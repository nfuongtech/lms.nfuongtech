<?php

namespace App\Filament\Pages;

use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KetQuaKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DiemDanhHocVien extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Đánh giá học viên';
    protected static ?string $title = 'Đánh giá học viên';
    protected static string $view = 'filament.pages.diem-danh-hoc-vien';

    public $namHienTai;
    public $availableNams = [];
    public $selectedNam = null;
    public $availableWeeks = [];
    public $selectedTuan = null;
    public $availableKhoaHocs = [];
    public $selectedKhoaHoc = null;

    public $khoaHocYearRows = [];
    public $khoaHocLichHocs = [];
    public $khoaHocRequirements = [
        'yeu_cau_gio' => null,
        'yeu_cau_diem' => null,
        'tong_gio_ke_hoach' => 0,
    ];

    public $hocViensDaDangKy = [];
    public $hocVienRows = [];
    public $diemDanhData = [];
    public $tongKetData = [];
    public $isEditing = [];

    public $daChuyenKetQua = false;
    public $coTheChinhSua = false;

    public $showGuiEmailModal = false;
    public $selectedEmailTemplateId = null;
    public $selectedEmailAccountId = null;
    public $loaiEmail = 'hoc_vien';

    public $showConfirmModal = false;

    public function mount(): void
    {
        $this->namHienTai = now()->year;
        $this->selectedNam = $this->namHienTai;

        $this->availableNams = LichHoc::query()
            ->select('nam')
            ->distinct()
            ->orderBy('nam', 'desc')
            ->pluck('nam')
            ->toArray();

        if (!in_array($this->selectedNam, $this->availableNams, true)) {
            $this->availableNams[] = $this->selectedNam;
            rsort($this->availableNams);
        }

        $this->availableWeeks = $this->getAvailableWeeksProperty()->toArray();
        $this->refreshAvailableKhoaHocs();
        $this->refreshKhoaHocYearRows();
    }

    public function updatedSelectedNam(): void
    {
        $this->selectedTuan = null;
        $this->selectedKhoaHoc = null;
        $this->availableWeeks = $this->getAvailableWeeksProperty()->toArray();
        $this->refreshAvailableKhoaHocs();
        $this->resetCourseContext();
        $this->refreshKhoaHocYearRows();
    }

    public function updatedSelectedTuan(): void
    {
        $this->selectedKhoaHoc = null;
        $this->refreshAvailableKhoaHocs();
        $this->resetCourseContext();
    }

    public function updatedSelectedKhoaHoc(): void
    {
        $this->refreshCourseContext();
    }

    public function updatedDiemDanhData($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (count($parts) !== 3) {
            return;
        }

        [$dangKyId, $lichHocId, $field] = $parts;
        $dangKyId = (int) $dangKyId;
        $lichHocId = (int) $lichHocId;

        if (!$dangKyId || !$lichHocId) {
            return;
        }

        $this->ensureCellDefaults($dangKyId, $lichHocId);

        if ($field === 'trang_thai') {
            $status = Arr::get($this->diemDanhData, "$dangKyId.$lichHocId.trang_thai", 'co_mat');
            if ($status === 'co_mat') {
                Arr::set($this->diemDanhData, "$dangKyId.$lichHocId.ly_do_vang", '');
                if (isset($this->khoaHocLichHocs[$lichHocId])) {
                    Arr::set($this->diemDanhData, "$dangKyId.$lichHocId.so_gio_hoc", $this->khoaHocLichHocs[$lichHocId]['so_gio']);
                }
            } else {
                Arr::set($this->diemDanhData, "$dangKyId.$lichHocId.so_gio_hoc", 0);
                Arr::set($this->diemDanhData, "$dangKyId.$lichHocId.diem", null);
            }
        }

        if ($field === 'so_gio_hoc') {
            $hours = Arr::get($this->diemDanhData, "$dangKyId.$lichHocId.so_gio_hoc");
            if (($hours === '' || $hours === null) && isset($this->khoaHocLichHocs[$lichHocId])) {
                Arr::set($this->diemDanhData, "$dangKyId.$lichHocId.so_gio_hoc", $this->khoaHocLichHocs[$lichHocId]['so_gio']);
            }
        }

        $this->recalculateTongKet($dangKyId);
    }

    public function updatedTongKetData($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (count($parts) !== 2) {
            return;
        }

        [$dangKyId, $field] = $parts;
        $dangKyId = (int) $dangKyId;

        if ($field === 'ket_qua') {
            $normalized = $this->normalizeKetQua(Arr::get($this->tongKetData, "$dangKyId.ket_qua"));
            Arr::set($this->tongKetData, "$dangKyId.ket_qua", $normalized);
            $suggestion = $this->normalizeKetQua(Arr::get($this->tongKetData, "$dangKyId.ket_qua_goi_y", $normalized));
            Arr::set(
                $this->tongKetData,
                "$dangKyId.ket_qua_is_manual",
                $normalized !== $suggestion
            );
            return;
        }

        if ($field === 'has_danh_gia') {
            $raw = Arr::get($this->tongKetData, "$dangKyId.has_danh_gia");
            $enabled = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($enabled === null) {
                $enabled = in_array($raw, ['1', 1, 'on', true], true);
            }

            Arr::set($this->tongKetData, "$dangKyId.has_danh_gia", (bool) $enabled);

            if (!$enabled) {
                Arr::set($this->tongKetData, "$dangKyId.danh_gia_ren_luyen", null);
            }
        }
    }

    public function dongDiemDanh(int $dangKyId): void
    {
        if (!isset($this->isEditing[$dangKyId])) {
            return;
        }

        $this->isEditing[$dangKyId] = false;
    }

    public function moSuaDiemDanh(int $dangKyId): void
    {
        if ($this->daChuyenKetQua || !$this->coTheChinhSua) {
            return;
        }

        if (!isset($this->isEditing[$dangKyId])) {
            return;
        }

        $this->isEditing[$dangKyId] = true;
    }

    public function chuanBiChuyenKetQua(): void
    {
        if ($this->daChuyenKetQua) {
            Notification::make()
                ->title('Khóa học đã được chuyển kết quả')
                ->warning()
                ->send();
            return;
        }

        if (!$this->coTheChinhSua) {
            Notification::make()
                ->title('Bạn không có quyền chuyển kết quả')
                ->danger()
                ->send();
            return;
        }

        if (empty($this->hocVienRows)) {
            Notification::make()->title('Không có học viên để chuyển kết quả')->warning()->send();
            return;
        }

        $this->showConfirmModal = true;
    }

    public function luuTam(): void
    {
        if (!$this->coTheChinhSua) {
            Notification::make()
                ->title('Bạn không có quyền lưu tạm')
                ->danger()
                ->send();

            return;
        }

        if (!$this->selectedKhoaHoc) {
            Notification::make()
                ->title('Vui lòng chọn Khóa học trước khi lưu tạm')
                ->warning()
                ->send();

            return;
        }

        if (empty($this->hocVienRows)) {
            Notification::make()
                ->title('Không có học viên để lưu tạm')
                ->warning()
                ->send();

            return;
        }

        try {
            $this->persistResults(false);
        } catch (\Throwable $exception) {
            Log::error('Không thể lưu tạm kết quả: ' . $exception->getMessage(), ['trace' => $exception->getTraceAsString()]);

            Notification::make()
                ->title('Không thể lưu tạm')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Đã lưu tạm kết quả')
            ->success()
            ->send();
    }

    public function xacNhanChuyenKetQua(): void
    {
        if ($this->daChuyenKetQua || !$this->coTheChinhSua) {
            $this->showConfirmModal = false;
            return;
        }

        try {
            $this->validateBeforeSubmit();
        } catch (ValidationException $exception) {
            $this->showConfirmModal = false;
            Notification::make()
                ->title('Không thể chuyển kết quả')
                ->body(collect($exception->errors())->flatten()->implode("
"))
                ->danger()
                ->send();
            return;
        }

        try {
            $this->persistResults(true);
        } catch (\Throwable $exception) {
            Log::error('Không thể chuyển kết quả: ' . $exception->getMessage(), ['trace' => $exception->getTraceAsString()]);

            Notification::make()
                ->title('Không thể chuyển kết quả')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $this->showConfirmModal = false;
            return;
        }

        $this->showConfirmModal = false;
        $this->daChuyenKetQua = true;
        $this->coTheChinhSua = false;
        foreach (array_keys($this->isEditing) as $dangKyId) {
            $this->isEditing[$dangKyId] = false;
        }

        Notification::make()
            ->title('Đã chuyển kết quả')
            ->success()
            ->send();
    }

    public function exportCourseOverview()
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()->title('Vui lòng chọn Khóa học trước khi xuất')->warning()->send();
            return null;
        }

        try {
            $khoaHoc = KhoaHoc::with(['chuongTrinh', 'lichHocs.giangVien'])->find($this->selectedKhoaHoc);
            if (!$khoaHoc) {
                Notification::make()->title('Không tìm thấy khóa học để xuất')->danger()->send();
                return null;
            }

            $fileName = 'thong-tin-khoa-hoc-' . ($khoaHoc->ma_khoa_hoc ?: 'khoa-hoc') . '.xlsx';

            return Excel::download(
                new DanhGiaKhoaHocExport(
                    $khoaHoc,
                    $this->mapLichHocForExport($khoaHoc),
                    $this->khoaHocRequirements,
                    count($this->hocVienRows)
                ),
                $fileName
            );
        } catch (\Throwable $exception) {
            Log::error('Xuất thông tin khóa học thất bại: ' . $exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            Notification::make()->title('Không thể xuất dữ liệu khóa học')->danger()->body($exception->getMessage())->send();
            return null;
        }
    }

    public function exportStudentList()
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()->title('Vui lòng chọn Khóa học trước khi xuất')->warning()->send();
            return null;
        }

        if (empty($this->hocVienRows)) {
            Notification::make()->title('Không có dữ liệu học viên để xuất')->warning()->send();
            return null;
        }

        try {
            $khoaHoc = KhoaHoc::select('id', 'ma_khoa_hoc')->find($this->selectedKhoaHoc);
            $fileCode = $khoaHoc?->ma_khoa_hoc ?? $this->selectedKhoaHoc;
            $fileName = 'danh-sach-hoc-vien-' . $fileCode . '.xlsx';

            return Excel::download(
                new DanhSachHocVienDanhGiaExport(
                    $this->hocVienRows,
                    $this->diemDanhData,
                    $this->tongKetData,
                    $this->getVisibleSessionColumns(),
                    !in_array('dtb', $this->hiddenColumns, true),
                    !in_array('ket_qua', $this->hiddenColumns, true),
                    !in_array('danh_gia', $this->hiddenColumns, true),
                    !in_array('hanh_dong', $this->hiddenColumns, true)
                ),
                $fileName
            );
        } catch (\Throwable $exception) {
            Log::error('Xuất danh sách học viên thất bại: ' . $exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            Notification::make()->title('Không thể xuất danh sách học viên')->danger()->body($exception->getMessage())->send();
            return null;
        }
    }

    public function moModalGuiEmail(): void
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()->title('Vui lòng chọn Khóa học trước khi gửi email')->danger()->send();
            return;
        }

        if (empty($this->hocVienRows)) {
            Notification::make()->title('Không có học viên nào để gửi email')->warning()->send();
            return;
        }

        $this->showGuiEmailModal = true;
    }

    public function guiEmailHangLoat(): void
    {
        $this->validate([
            'selectedEmailTemplateId' => 'required|exists:email_templates,id',
            'selectedEmailAccountId' => 'required|exists:email_accounts,id',
            'loaiEmail' => 'required|in:hoc_vien,giang_vien',
        ], [
            'selectedEmailTemplateId.required' => 'Vui lòng chọn mẫu email.',
            'selectedEmailTemplateId.exists' => 'Mẫu email không tồn tại.',
            'selectedEmailAccountId.required' => 'Vui lòng chọn tài khoản gửi email.',
            'selectedEmailAccountId.exists' => 'Tài khoản gửi email không tồn tại.',
            'loaiEmail.required' => 'Vui lòng chọn loại email.',
            'loaiEmail.in' => 'Loại email không hợp lệ.',
        ]);

        $template = EmailTemplate::find($this->selectedEmailTemplateId);
        $emailAcct = EmailAccount::find($this->selectedEmailAccountId);
        $khoaHoc = KhoaHoc::with('chuongTrinh')->find($this->selectedKhoaHoc);

        if (!$template || !$emailAcct || !$khoaHoc) {
            Notification::make()->title('Thiếu dữ liệu gửi email')->danger()->send();
            $this->showGuiEmailModal = false;
            return;
        }

        Config::set('mail.mailers.dynamic', [
            'transport' => 'smtp',
            'host' => $emailAcct->host,
            'port' => $emailAcct->port,
            'encryption' => $emailAcct->encryption_tls ? 'tls' : null,
            'username' => $emailAcct->username,
            'password' => $emailAcct->password,
        ]);
        Config::set('mail.from', [
            'address' => $emailAcct->email,
            'name' => $emailAcct->name,
        ]);

        $ok = 0;
        $fail = 0;

        if ($this->loaiEmail === 'hoc_vien') {
            foreach ($this->hocVienRows as $row) {
                $hocVien = $row['hoc_vien'];
                $recipientEmail = $hocVien->email ?? null;
                if (!$recipientEmail) {
                    $fail++;
                    EmailLog::create([
                        'email_account_id' => $emailAcct->id,
                        'recipient_email' => 'N/A',
                        'subject' => 'Không gửi (thiếu email học viên)',
                        'content' => '',
                        'status' => 'failed',
                        'error_message' => 'Học viên không có email.',
                    ]);
                    continue;
                }

                $dangKyId = $row['dang_ky_id'];
                $placeholders = [
                    '{ten_hoc_vien}' => $hocVien->ho_ten ?? 'N/A',
                    '{msnv}' => $hocVien->msnv ?? 'N/A',
                    '{ma_khoa_hoc}' => $khoaHoc->ma_khoa_hoc ?? 'N/A',
                    '{ten_chuong_trinh}' => optional($khoaHoc->chuongTrinh)->ten_chuong_trinh ?? 'N/A',
                    '{chuc_vu}' => $hocVien->chuc_vu ?? '',
                    '{don_vi}' => optional($hocVien->donVi)->ten_hien_thi ?? '',
                    '{diem_tb}' => Arr::get($this->tongKetData, "$dangKyId.diem_trung_binh", ''),
                    '{ket_qua}' => $this->mapKetQuaLabel(Arr::get($this->tongKetData, "$dangKyId.ket_qua", 'hoan_thanh')),
                ];

                $subject = strtr($template->tieu_de, $placeholders);
                $body = strtr($template->noi_dung, $placeholders);

                try {
                    Mail::mailer('dynamic')
                        ->to($recipientEmail)
                        ->send(new \App\Mail\PlanNotificationMail($subject, $body));
                    $ok++;
                    $status = 'success';
                    $err = null;
                } catch (\Throwable $e) {
                    $fail++;
                    $status = 'failed';
                    $err = $e->getMessage();
                    Log::error("Lỗi gửi email tới {$recipientEmail}: " . $e->getMessage());
                }

                EmailLog::create([
                    'email_account_id' => $emailAcct->id,
                    'recipient_email' => $recipientEmail,
                    'subject' => $subject,
                    'content' => $body,
                    'status' => $status,
                    'error_message' => $err,
                ]);
            }
        } else {
            $giangViens = $this->getDanhSachGiangVien();
            foreach ($giangViens as $gv) {
                $recipientEmail = $gv->email ?? null;
                if (!$recipientEmail) {
                    $fail++;
                    EmailLog::create([
                        'email_account_id' => $emailAcct->id,
                        'recipient_email' => 'N/A',
                        'subject' => 'Không gửi (thiếu email giảng viên)',
                        'content' => '',
                        'status' => 'failed',
                        'error_message' => 'Giảng viên không có email.',
                    ]);
                    continue;
                }

                $placeholders = [
                    '{ten_giang_vien}' => $gv->ho_ten ?? 'N/A',
                    '{ma_khoa_hoc}' => $khoaHoc->ma_khoa_hoc ?? 'N/A',
                    '{ten_chuong_trinh}' => optional($khoaHoc->chuongTrinh)->ten_chuong_trinh ?? 'N/A',
                ];

                $subject = strtr($template->tieu_de, $placeholders);
                $body = strtr($template->noi_dung, $placeholders);

                try {
                    Mail::mailer('dynamic')
                        ->to($recipientEmail)
                        ->send(new \App\Mail\PlanNotificationMail($subject, $body));
                    $ok++;
                    $status = 'success';
                    $err = null;
                } catch (\Throwable $e) {
                    $fail++;
                    $status = 'failed';
                    $err = $e->getMessage();
                    Log::error("Lỗi gửi email tới GV {$recipientEmail}: " . $e->getMessage());
                }

                EmailLog::create([
                    'email_account_id' => $emailAcct->id,
                    'recipient_email' => $recipientEmail,
                    'subject' => $subject,
                    'content' => $body,
                    'status' => $status,
                    'error_message' => $err,
                ]);
            }
        }

        $this->showGuiEmailModal = false;
        $this->selectedEmailTemplateId = null;
        $this->selectedEmailAccountId = null;
        $this->loaiEmail = 'hoc_vien';

        Notification::make()
            ->title('Gửi email hoàn tất!')
            ->body("Thành công: {$ok}. Thất bại: {$fail}.")
            ->success()
            ->send();
    }

    public static function getSlug(): string
    {
        return 'diem-danh-hoc-vien';
    }

    private function refreshCourseContext(): void
    {
        $this->hocViensDaDangKy = [];
        $this->hocVienRows = [];
        $this->diemDanhData = [];
        $this->tongKetData = [];
        $this->isEditing = [];
        $this->khoaHocLichHocs = [];
        $this->columnToggleOptions = [];
        $this->coTheChinhSua = false;
        $this->daChuyenKetQua = false;

        if (!$this->selectedKhoaHoc) {
            $this->hiddenColumns = [];
            return;
        }

        $khoaHoc = KhoaHoc::with(['lichHocs' => function ($query) {
            $query->orderBy('ngay_hoc')->orderBy('gio_bat_dau');
        }])->find($this->selectedKhoaHoc);

        if (!$khoaHoc) {
            return;
        }

        $lichHocs = $khoaHoc->lichHocs;
        if ($this->selectedNam) {
            $lichHocs = $lichHocs->where('nam', $this->selectedNam);
        }
        if ($this->selectedTuan) {
            $lichHocs = $lichHocs->where('tuan', $this->selectedTuan);
        }

        $lichHocArray = [];
        $tongGioKeHoach = 0;
        foreach ($lichHocs as $lichHoc) {
            $tongGioKeHoach += (float) ($lichHoc->so_gio_giang ?? 0);
            $ngay = $lichHoc->ngay_hoc;
            if ($ngay instanceof \DateTimeInterface) {
                $nhan = $ngay->format('d/m');
                $ngayMoTa = $ngay->format('d/m/Y');
            } elseif ($ngay) {
                $timestamp = strtotime((string) $ngay);
                $nhan = $timestamp ? date('d/m', $timestamp) : 'Buổi';
                $ngayMoTa = $timestamp ? date('d/m/Y', $timestamp) : '—';
            } else {
                $nhan = 'Buổi';
                $ngayMoTa = '—';
            }

            $gioBatDau = $lichHoc->gio_bat_dau ? substr($lichHoc->gio_bat_dau, 0, 5) : '';
            $gioKetThuc = $lichHoc->gio_ket_thuc ? substr($lichHoc->gio_ket_thuc, 0, 5) : '';
            $moTaGio = $gioBatDau && $gioKetThuc ? " · {$gioBatDau}-{$gioKetThuc}" : '';
            $moTaDiaDiem = $lichHoc->dia_diem ? ' · ' . $lichHoc->dia_diem : '';

            $lichHocArray[$lichHoc->id] = [
                'nhan' => $nhan,
                'mo_ta' => $ngayMoTa . $moTaGio . $moTaDiaDiem,
                'so_gio' => (float) ($lichHoc->so_gio_giang ?? 0),
                'giang_vien_id' => $lichHoc->giang_vien_id,
            ];
        }

        $this->khoaHocLichHocs = $lichHocArray;
        $this->columnToggleOptions = $this->generateColumnToggleOptions();
        $validKeys = array_keys($this->columnToggleOptions);
        $this->hiddenColumns = array_values(array_intersect($this->hiddenColumns, $validKeys));

        $this->khoaHocRequirements = [
            'yeu_cau_gio' => $khoaHoc->yeu_cau_phan_tram_gio,
            'yeu_cau_diem' => $khoaHoc->yeu_cau_diem_tb,
            'tong_gio_ke_hoach' => round($tongGioKeHoach, 2),
        ];
        $this->daChuyenKetQua = (bool) $khoaHoc->da_chuyen_ket_qua;

        $this->evaluateEditPermission($khoaHoc);
        $this->refreshHocVienData($khoaHoc);
    }

    private function refreshHocVienData(KhoaHoc $khoaHoc): void
    {
        $lichHocIds = array_keys($this->khoaHocLichHocs);
        if (empty($lichHocIds)) {
            return;
        }

        $dangKies = DangKy::with([
            'hocVien.donVi',
            'diemDanhs' => fn ($query) => $query->whereIn('lich_hoc_id', $lichHocIds),
            'diemDanhs.lichHoc',
        ])->where('khoa_hoc_id', $khoaHoc->id)->get();

        foreach ($dangKies as $dangKy) {
            $hocVien = $dangKy->hocVien;
            if (!$hocVien) {
                continue;
            }

            $this->hocViensDaDangKy[] = $hocVien;
            $this->hocVienRows[] = [
                'hoc_vien' => $hocVien,
                'dang_ky_id' => $dangKy->id,
            ];

            foreach ($lichHocIds as $lichHocId) {
                $record = $dangKy->diemDanhs->firstWhere('lich_hoc_id', $lichHocId);
                $this->diemDanhData[$dangKy->id][$lichHocId] = [
                    'trang_thai' => $record->trang_thai ?? 'co_mat',
                    'ly_do_vang' => $record->ly_do_vang ?? '',
                    'so_gio_hoc' => $record->so_gio_hoc ?? $this->khoaHocLichHocs[$lichHocId]['so_gio'],
                    'diem' => $record?->diem_buoi_hoc,
                ];
            }

            $ketQua = KetQuaKhoaHoc::firstOrNew(['dang_ky_id' => $dangKy->id]);
            $ketQuaGoiY = $ketQua->ket_qua_goi_y ? $this->normalizeKetQua($ketQua->ket_qua_goi_y) : null;
            $ketQuaThucTe = $ketQua->ket_qua ? $this->normalizeKetQua($ketQua->ket_qua) : null;
            $hasDanhGia = trim((string) ($ketQua->danh_gia_ren_luyen ?? '')) !== '';
            $isManual = $ketQuaThucTe !== null && $ketQuaGoiY !== null && $ketQuaThucTe !== $ketQuaGoiY;

            $this->tongKetData[$dangKy->id] = [
                'diem_trung_binh' => $ketQua->diem_trung_binh,
                'ket_qua_goi_y' => $ketQuaGoiY,
                'ket_qua' => $ketQuaThucTe ?? $ketQuaGoiY ?? 'hoan_thanh',
                'danh_gia_ren_luyen' => $ketQua->danh_gia_ren_luyen,
                'tong_so_gio_thuc_te' => $ketQua->tong_so_gio_thuc_te,
                'has_danh_gia' => $hasDanhGia,
                'ket_qua_is_manual' => $isManual,
            ];

            if (! $hasDanhGia) {
                $this->tongKetData[$dangKy->id]['danh_gia_ren_luyen'] = null;
            }

            $this->isEditing[$dangKy->id] = false;
            $this->recalculateTongKet($dangKy->id, !$isManual);
        }
    }

    private function persistResults(bool $finalize): void
    {
        DB::beginTransaction();

        try {
            $khoaHoc = KhoaHoc::with('lichHocs')->find($this->selectedKhoaHoc);
            if (!$khoaHoc) {
                throw new \RuntimeException('Không tìm thấy khóa học.');
            }

            $lichHocIds = array_keys($this->khoaHocLichHocs);

            foreach ($this->hocVienRows as $row) {
                $dangKyId = $row['dang_ky_id'];
                if (!$dangKyId) {
                    continue;
                }

                foreach ($lichHocIds as $lichHocId) {
                    $cell = $this->diemDanhData[$dangKyId][$lichHocId] ?? [];
                    $status = $cell['trang_thai'] ?? 'co_mat';
                    $lyDo = $status === 'co_mat' ? null : ($cell['ly_do_vang'] ?? null);
                    $soGio = $cell['so_gio_hoc'] ?? ($this->khoaHocLichHocs[$lichHocId]['so_gio'] ?? null);
                    $diem = $cell['diem'] ?? null;

                    if ($status !== 'co_mat') {
                        $soGio = 0;
                        $diem = null;
                    }

                    DiemDanh::updateOrCreate(
                        [
                            'dang_ky_id' => $dangKyId,
                            'lich_hoc_id' => $lichHocId,
                        ],
                        [
                            'trang_thai' => $status,
                            'ly_do_vang' => $lyDo,
                            'so_gio_hoc' => $soGio === '' ? null : $soGio,
                            'diem_buoi_hoc' => $diem === '' ? null : $diem,
                            'danh_gia_ky_luat' => null,
                        ]
                    );
                }

                $tongKet = $this->tongKetData[$dangKyId] ?? [];
                $ketQua = $this->normalizeKetQua($tongKet['ket_qua'] ?? null);
                $goiY = $this->normalizeKetQua($tongKet['ket_qua_goi_y'] ?? null);
                $hocVienId = $row['hoc_vien']->id;

                $hasDanhGia = filter_var($tongKet['has_danh_gia'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($hasDanhGia === null) {
                    $hasDanhGia = in_array($tongKet['has_danh_gia'] ?? false, ['1', 1, 'on', true], true);
                }

                $danhGia = $hasDanhGia ? trim((string) ($tongKet['danh_gia_ren_luyen'] ?? '')) : '';
                if ($danhGia === '') {
                    $danhGia = null;
                }

                Arr::set($this->tongKetData, "$dangKyId.has_danh_gia", $hasDanhGia);
                Arr::set($this->tongKetData, "$dangKyId.ket_qua_is_manual", $ketQua !== $goiY);

                $payload = [
                    'ket_qua_goi_y' => $goiY,
                    'ket_qua' => $ketQua,
                    'danh_gia_ren_luyen' => $danhGia,
                    'can_hoc_lai' => $ketQua === 'khong_hoan_thanh',
                ];

                if (array_key_exists('tong_so_gio_thuc_te', $tongKet)) {
                    $payload['tong_so_gio_thuc_te'] = $tongKet['tong_so_gio_thuc_te'];
                }

                if ($this->khoaHocRequirements['tong_gio_ke_hoach'] !== null) {
                    $payload['tong_so_gio_ke_hoach'] = $this->khoaHocRequirements['tong_gio_ke_hoach'];
                }

                if (array_key_exists('diem_trung_binh', $tongKet)) {
                    $payload['diem_trung_binh'] = $tongKet['diem_trung_binh'];
                }

                $payload = $this->filterKetQuaColumns($payload, $ketQua);

                $ketQuaModel = KetQuaKhoaHoc::updateOrCreate(
                    ['dang_ky_id' => $dangKyId],
                    $payload
                );

                if ($finalize) {
                    $this->runIgnoringMissingTable(fn () => HocVienHoanThanh::where('ket_qua_khoa_hoc_id', $ketQuaModel->id)->delete());
                    $this->runIgnoringMissingTable(fn () => HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $ketQuaModel->id)->delete());

                    if ($ketQua === 'hoan_thanh') {
                        $this->runIgnoringMissingTable(fn () => HocVienHoanThanh::updateOrCreate(
                            [
                                'hoc_vien_id' => $hocVienId,
                                'khoa_hoc_id' => $khoaHoc->id,
                                'ket_qua_khoa_hoc_id' => $ketQuaModel->id,
                            ],
                            []
                        ));
                    } elseif ($ketQua === 'khong_hoan_thanh') {
                        $this->runIgnoringMissingTable(fn () => HocVienKhongHoanThanh::updateOrCreate(
                            [
                                'hoc_vien_id' => $hocVienId,
                                'khoa_hoc_id' => $khoaHoc->id,
                                'ket_qua_khoa_hoc_id' => $ketQuaModel->id,
                            ],
                            []
                        ));
                    }
                }
            }

            if ($finalize) {
                $updates = ['da_chuyen_ket_qua' => true];

                if (Schema::hasColumn('khoa_hocs', 'thoi_gian_chuyen_ket_qua')) {
                    $updates['thoi_gian_chuyen_ket_qua'] = now();
                }

                if (Schema::hasColumn('khoa_hocs', 'nguoi_chuyen_ket_qua')) {
                    $updates['nguoi_chuyen_ket_qua'] = Auth::user()?->name;
                }

                $khoaHoc->update($updates);
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $this->refreshCourseContext();
    }

    private function recalculateTongKet(int $dangKyId, bool $resetManual = false): void
    {
        if (!isset($this->tongKetData[$dangKyId])) {
            return;
        }

        $lichHocIds = array_keys($this->khoaHocLichHocs);
        $tongKeHoach = $this->khoaHocRequirements['tong_gio_ke_hoach'] ?? 0;
        $tongThucTe = 0;
        $tongDiem = 0;
        $countDiem = 0;

        foreach ($lichHocIds as $lichHocId) {
            $cell = $this->diemDanhData[$dangKyId][$lichHocId] ?? [];
            $status = $cell['trang_thai'] ?? 'co_mat';
            $hours = $cell['so_gio_hoc'] ?? $this->khoaHocLichHocs[$lichHocId]['so_gio'];

            if ($status !== 'co_mat') {
                $hours = 0;
            }

            $tongThucTe += max(0, (float) $hours);

            if ($cell['diem'] !== null && $cell['diem'] !== '') {
                $tongDiem += (float) $cell['diem'];
                $countDiem++;
            }
        }

        $diemTrungBinh = $countDiem > 0 ? round($tongDiem / $countDiem, 2) : null;
        $this->tongKetData[$dangKyId]['diem_trung_binh'] = $diemTrungBinh;
        $this->tongKetData[$dangKyId]['tong_so_gio_thuc_te'] = round($tongThucTe, 2);

        $phanTram = $tongKeHoach > 0 ? ($tongThucTe / $tongKeHoach) * 100 : 0;
        $datGio = $this->khoaHocRequirements['yeu_cau_gio'] === null
            || $phanTram >= $this->khoaHocRequirements['yeu_cau_gio'];
        $datDiem = $this->khoaHocRequirements['yeu_cau_diem'] === null
            || ($diemTrungBinh !== null && $diemTrungBinh >= $this->khoaHocRequirements['yeu_cau_diem']);

        $goiY = ($datGio && $datDiem) ? 'hoan_thanh' : 'khong_hoan_thanh';
        $this->tongKetData[$dangKyId]['ket_qua_goi_y'] = $goiY;

        $isManual = (bool) ($this->tongKetData[$dangKyId]['ket_qua_is_manual'] ?? false);
        if ($resetManual) {
            $isManual = false;
        }

        if (! $isManual) {
            $this->tongKetData[$dangKyId]['ket_qua'] = $goiY;
        }

        $this->tongKetData[$dangKyId]['ket_qua_is_manual'] = $isManual;
    }

    private function resetCourseContext(): void
    {
        $this->selectedKhoaHoc = null;
        $this->khoaHocLichHocs = [];
        $this->khoaHocRequirements = [
            'yeu_cau_gio' => null,
            'yeu_cau_diem' => null,
            'tong_gio_ke_hoach' => 0,
        ];
        $this->hocViensDaDangKy = [];
        $this->hocVienRows = [];
        $this->diemDanhData = [];
        $this->tongKetData = [];
        $this->isEditing = [];
        $this->daChuyenKetQua = false;
        $this->coTheChinhSua = false;
    }

    private function getAvailableWeeksProperty(): Collection
    {
        if (!$this->selectedNam) {
            return collect();
        }

        return LichHoc::query()
            ->select('tuan')
            ->where('nam', $this->selectedNam)
            ->distinct()
            ->orderBy('tuan')
            ->pluck('tuan');
    }

    private function refreshAvailableKhoaHocs(): void
    {
        if (!$this->selectedNam || !$this->selectedTuan) {
            $this->availableKhoaHocs = collect();
            return;
        }

        $khoaHocIds = LichHoc::query()
            ->where('nam', $this->selectedNam)
            ->where('tuan', $this->selectedTuan)
            ->pluck('khoa_hoc_id')
            ->unique();

        if ($khoaHocIds->isEmpty()) {
            $this->availableKhoaHocs = collect();
            return;
        }

        $khoaHocIds = DangKy::query()
            ->whereIn('khoa_hoc_id', $khoaHocIds)
            ->select('khoa_hoc_id')
            ->groupBy('khoa_hoc_id')
            ->pluck('khoa_hoc_id');

        if ($khoaHocIds->isEmpty()) {
            $this->availableKhoaHocs = collect();
            return;
        }

        $this->availableKhoaHocs = KhoaHoc::with('chuongTrinh')
            ->whereIn('id', $khoaHocIds)
            ->orderBy('ma_khoa_hoc')
            ->get();
    }

    private function refreshKhoaHocYearRows(): void
    {
        $this->khoaHocYearRows = [];
        if (!$this->selectedNam) {
            return;
        }

        $khoaHocIds = LichHoc::query()
            ->where('nam', $this->selectedNam)
            ->pluck('khoa_hoc_id')
            ->unique();

        if ($khoaHocIds->isEmpty()) {
            return;
        }

        $khoaHocs = KhoaHoc::with([
            'chuongTrinh',
            'lichHocs' => fn ($q) => $q->orderBy('ngay_hoc'),
            'lichHocs.chuyenDe',
            'lichHocs.giangVien',
        ])->whereIn('id', $khoaHocIds)->orderBy('ma_khoa_hoc')->get();

        $rows = [];
        foreach ($khoaHocs as $khoaHoc) {
            $lichTrongNam = $khoaHoc->lichHocs->where('nam', $this->selectedNam);
            $soBuoi = $lichTrongNam->count();
            $tuanSet = $lichTrongNam->pluck('tuan')->unique()->values();
            $tuanCsv = $tuanSet->implode(', ');
            $ngayMin = $lichTrongNam->min('ngay_hoc');
            $ngayMax = $lichTrongNam->max('ngay_hoc');

            $ngayDaoTao = $this->formatKhoangNgay($ngayMin, $ngayMax);

            $giangVienNames = [];
            foreach ($lichTrongNam as $lich) {
                $gv = $lich->giangVien;
                if ($gv && $gv->ho_ten) {
                    $giangVienNames[$gv->ho_ten] = true;
                }
            }

            $soLuongHv = DangKy::where('khoa_hoc_id', $khoaHoc->id)->count();
            if ($soLuongHv === 0) {
                continue;
            }

            $rows[] = [
                'khoa_hoc_id' => $khoaHoc->id,
                'ma_khoa_hoc' => $khoaHoc->ma_khoa_hoc ?? '',
                'ten_khoa_hoc' => optional($khoaHoc->chuongTrinh)->ten_chuong_trinh ?? ($khoaHoc->ten_khoa_hoc ?? ''),
                'trang_thai' => $khoaHoc->trang_thai_hien_thi,
                'so_buoi' => $soBuoi,
                'tuan' => $tuanCsv,
                'ngay_dao_tao' => $ngayDaoTao,
                'giang_vien' => implode(', ', array_keys($giangVienNames)),
                'so_luong_hv' => $soLuongHv,
            ];
        }

        $this->khoaHocYearRows = $rows;
    }

    private function formatKhoangNgay($ngayMin, $ngayMax): string
    {
        $format = function ($value) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('d/m/Y');
            }

            if ($value) {
                $timestamp = strtotime((string) $value);
                return $timestamp ? date('d/m/Y', $timestamp) : '';
            }

            return '';
        };

        $minFormatted = $format($ngayMin);
        $maxFormatted = $format($ngayMax);

        if ($minFormatted && $maxFormatted) {
            if ($minFormatted === $maxFormatted) {
                return $minFormatted;
            }

            return $minFormatted . ' - ' . $maxFormatted;
        }

        if ($minFormatted) {
            return $minFormatted;
        }

        return '';
    }

    private function generateColumnToggleOptions(): array
    {
        $options = [];

        foreach ($this->khoaHocLichHocs as $lichHocId => $lichHoc) {
            $options['session_' . $lichHocId] = 'Ngày ' . ($lichHoc['nhan'] ?? $lichHocId);
        }

        $options['dtb'] = 'ĐTB';
        $options['ket_qua'] = 'Kết quả';
        $options['danh_gia'] = 'Đánh giá rèn luyện';
        $options['hanh_dong'] = 'Hành động';

        return $options;
    }

    private function getVisibleSessionColumns(): array
    {
        $columns = [];

        foreach ($this->khoaHocLichHocs as $lichHocId => $lichHoc) {
            if (in_array('session_' . $lichHocId, $this->hiddenColumns, true)) {
                continue;
            }

            $columns[] = [
                'id' => $lichHocId,
                'label' => $lichHoc['nhan'] ?? ('Buổi ' . $lichHocId),
                'mo_ta' => $lichHoc['mo_ta'] ?? '',
            ];
        }

        return $columns;
    }

    private function mapLichHocForExport(KhoaHoc $khoaHoc): array
    {
        $giangVienTheoBuoi = [];
        foreach ($khoaHoc->lichHocs as $lichHoc) {
            $giangVienTheoBuoi[$lichHoc->id] = optional($lichHoc->giangVien)->ho_ten;
        }

        $rows = [];
        foreach ($this->khoaHocLichHocs as $lichHocId => $lichHoc) {
            $rows[] = $lichHoc + ['giang_vien' => $giangVienTheoBuoi[$lichHocId] ?? null];
        }

        return $rows;
    }

    private function ensureCellDefaults(int $dangKyId, int $lichHocId): void
    {
        if (!isset($this->diemDanhData[$dangKyId][$lichHocId])) {
            $this->diemDanhData[$dangKyId][$lichHocId] = [
                'trang_thai' => 'co_mat',
                'ly_do_vang' => '',
                'so_gio_hoc' => $this->khoaHocLichHocs[$lichHocId]['so_gio'] ?? null,
                'diem' => null,
            ];
        }
    }

    private function validateBeforeSubmit(): void
    {
        // Hiện tại không có yêu cầu bắt buộc nào trước khi lưu/chuyển kết quả.
    }

    private function normalizeKetQua(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        return match ($normalized) {
            'khong hoan thanh', 'không hoàn thành', 'khong_hoan_thanh' => 'khong_hoan_thanh',
            default => 'hoan_thanh',
        };
    }

    /**
     * @template TReturn
     * @param  callable():TReturn  $callback
     * @return TReturn|null
     */
    private function runIgnoringMissingTable(callable $callback)
    {
        try {
            return $callback();
        } catch (QueryException $exception) {
            if ($this->isMissingTableError($exception)) {
                Log::warning('Bỏ qua đồng bộ vì thiếu bảng kết quả', [
                    'message' => $exception->getMessage(),
                ]);

                return null;
            }

            throw $exception;
        }
    }

    private function isMissingTableError(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'base table or view not found')
            || str_contains($message, 'no such table')
            || str_contains($message, 'does not exist')
            || str_contains($message, "doesn't exist");
    }

    private function evaluateEditPermission(?KhoaHoc $khoaHoc): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->coTheChinhSua = false;
            return;
        }

        $privilegedRoles = [
            'Super Admin',
            'super_admin',
            'Admin',
            'admin',
            'Quản lý đào tạo',
            'quan-ly-dao-tao',
            'quan_ly_dao_tao',
        ];

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($privilegedRoles)) {
            $this->coTheChinhSua = true;
            return;
        }

        if (method_exists($user, 'hasRole')) {
            foreach ($privilegedRoles as $role) {
                if ($user->hasRole($role)) {
                    $this->coTheChinhSua = true;
                    return;
                }
            }
        }

        if (method_exists($user, 'can') && ($user->can('manage-training-results') || $user->can('manage-specialized-modules'))) {
            $this->coTheChinhSua = true;
            return;
        }

        $giangVienId = optional($user->giangVien)->id;
        if (!$giangVienId || !$khoaHoc) {
            $this->coTheChinhSua = false;
            return;
        }

        foreach ($this->khoaHocLichHocs as $lichHoc) {
            if ((int) ($lichHoc['giang_vien_id'] ?? 0) === (int) $giangVienId) {
                $this->coTheChinhSua = true;
                return;
            }
        }

        $this->coTheChinhSua = false;
    }

    private function getDanhSachGiangVien(): Collection
    {
        if (!$this->selectedKhoaHoc) {
            return collect();
        }

        $khoaHoc = KhoaHoc::with('lichHocs.giangVien')->find($this->selectedKhoaHoc);
        if (!$khoaHoc) {
            return collect();
        }

        return $khoaHoc->lichHocs->pluck('giangVien')->filter();
    }

    private function mapKetQuaLabel(?string $value): string
    {
        $value = $this->normalizeKetQua($value);
        return $value === 'hoan_thanh' ? 'Hoàn thành' : 'Không hoàn thành';
    }

    private function filterKetQuaColumns(array $payload, string $ketQua): array
    {
        static $columns;

        if ($columns === null) {
            $columns = Schema::hasTable('ket_qua_khoa_hocs') ? Schema::getColumnListing('ket_qua_khoa_hocs') : [];
        }

        $allowed = array_flip($columns);
        $filtered = [];

        foreach ($payload as $column => $value) {
            if (isset($allowed[$column])) {
                $filtered[$column] = $value;
            }
        }

        if (!isset($filtered['ket_qua']) && isset($allowed['ket_qua'])) {
            $filtered['ket_qua'] = $ketQua;
        }

        if (!isset($filtered['can_hoc_lai']) && isset($allowed['can_hoc_lai'])) {
            $filtered['can_hoc_lai'] = $ketQua === 'khong_hoan_thanh';
        }

        if (!isset($allowed['diem_trung_binh']) && isset($allowed['diem']) && array_key_exists('diem_trung_binh', $payload)) {
            $filtered['diem'] = $payload['diem_trung_binh'];
        }

        if (isset($allowed['nguoi_nhap'])) {
            $filtered['nguoi_nhap'] = Auth::user()?->name;
        }

        if (isset($allowed['ngay_nhap'])) {
            $filtered['ngay_nhap'] = now();
        }

        if (isset($allowed['needs_review']) && !array_key_exists('needs_review', $filtered)) {
            $filtered['needs_review'] = false;
        }

        return $filtered;
    }
}
