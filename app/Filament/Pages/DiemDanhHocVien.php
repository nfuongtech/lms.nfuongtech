<?php

namespace App\Filament\Pages;

use App\Models\KhoaHoc;
use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\LichHoc;
use App\Models\KetQuaKhoaHoc;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
// Email & cấu hình
use App\Models\EmailTemplate;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class DiemDanhHocVien extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Điểm danh học viên';
    protected static ?string $title = 'Điểm danh học viên';
    protected static string $view = 'filament.pages.diem-danh-hoc-vien';

    // --- Chọn Khóa học, Buổi học ---
    public $namHienTai;
    public $availableNams = [];
    public $selectedNam = null;
    public $availableWeeks = [];
    public $selectedTuan = null;
    public $availableKhoaHocs = [];
    public $availableLichHocs = [];
    public $searchMaKhoaHoc = '';
    public $selectedKhoaHoc = null;
    public $selectedLichHoc = null;

    // --- Danh sách học viên & dữ liệu điểm danh ---
    public $hocViensDaDangKy = [];
    public $diemDanhData = [];

    // --- Biến cho gửi email (khôi phục để khớp Blade) ---
    public $showGuiEmailModal = false;
    public $selectedEmailTemplateId = null;
    public $selectedEmailAccountId = null;
    public $loaiEmail = 'hoc_vien'; // 'hoc_vien' | 'giang_vien'

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
        $this->availableKhoaHocs = collect();
        $this->availableLichHocs = collect();

        $this->refreshAvailableKhoaHocs();
        $this->refreshHocViens();
    }

    public function updatedSelectedNam(): void
    {
        $this->selectedTuan = null;
        $this->selectedKhoaHoc = null;
        $this->selectedLichHoc = null;
        $this->availableWeeks = $this->getAvailableWeeksProperty()->toArray();
        $this->availableKhoaHocs = collect();
        $this->availableLichHocs = collect();

        $this->refreshAvailableKhoaHocs();
        $this->refreshHocViens();
    }

    public function updatedSelectedTuan(): void
    {
        $this->selectedKhoaHoc = null;
        $this->selectedLichHoc = null;

        $this->refreshAvailableKhoaHocs();
        $this->availableLichHocs = collect();
        $this->refreshHocViens();
    }

    public function updatedSearchMaKhoaHoc(): void
    {
        $this->refreshAvailableKhoaHocs();
    }

    public function updatedSelectedKhoaHoc(): void
    {
        $this->selectedLichHoc = null;
        $this->refreshAvailableLichHocs();
        $this->refreshHocViens();
    }

    public function updatedSelectedLichHoc(): void
    {
        $this->refreshHocViens();
    }

    public function getAvailableWeeksProperty()
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

        $lichHocIds = LichHoc::query()
            ->where('nam', $this->selectedNam)
            ->where('tuan', $this->selectedTuan)
            ->pluck('khoa_hoc_id')
            ->unique();

        $query = KhoaHoc::with('chuongTrinh')
            ->whereIn('id', $lichHocIds);

        if ($this->searchMaKhoaHoc) {
            $query->where('ma_khoa_hoc', 'like', '%' . $this->searchMaKhoaHoc . '%');
        }

        $this->availableKhoaHocs = $query
            ->orderBy('ma_khoa_hoc')
            ->get();

        if ($this->selectedKhoaHoc && !$this->availableKhoaHocs->contains('id', $this->selectedKhoaHoc)) {
            $this->selectedKhoaHoc = null;
            $this->selectedLichHoc = null;
            $this->availableLichHocs = collect();
        } elseif ($this->selectedKhoaHoc) {
            $this->refreshAvailableLichHocs();
        }
    }

    private function refreshAvailableLichHocs(): void
    {
        if (!$this->selectedKhoaHoc || !$this->selectedNam || !$this->selectedTuan) {
            $this->availableLichHocs = collect();
            return;
        }

        $this->availableLichHocs = LichHoc::query()
            ->where('khoa_hoc_id', $this->selectedKhoaHoc)
            ->where('nam', $this->selectedNam)
            ->where('tuan', $this->selectedTuan)
            ->orderBy('ngay_hoc')
            ->get();

        if ($this->selectedLichHoc && !$this->availableLichHocs->contains('id', $this->selectedLichHoc)) {
            $this->selectedLichHoc = null;
        }
    }

    private function refreshHocViens(): void
    {
        $this->hocViensDaDangKy = [];
        $this->diemDanhData = [];

        if (
            !$this->selectedNam ||
            !$this->selectedTuan ||
            !$this->selectedKhoaHoc ||
            !$this->selectedLichHoc
        ) {
            return;
        }

        $availableKhoaHocCollection = $this->availableKhoaHocs instanceof Collection
            ? $this->availableKhoaHocs
            : collect($this->availableKhoaHocs);

        $availableKhoaHocIds = $availableKhoaHocCollection->pluck('id');

        if (!$availableKhoaHocIds->contains($this->selectedKhoaHoc)) {
            return;
        }

        $dangKies = DangKy::with(['hocVien'])
            ->whereIn('khoa_hoc_id', $availableKhoaHocIds)
            ->where('khoa_hoc_id', $this->selectedKhoaHoc)
            ->get();

        foreach ($dangKies as $dk) {
            $diemDanh = DiemDanh::where('dang_ky_id', $dk->id)
                ->where('lich_hoc_id', $this->selectedLichHoc)
                ->first();

            $this->hocViensDaDangKy[] = $dk->hocVien;

            $this->diemDanhData[$dk->id] = [
                'trang_thai'       => $diemDanh->trang_thai ?? 'co_mat', // 'co_mat' | 'vang_phep' | 'vang_khong_phep'
                'ly_do_vang'       => $diemDanh->ly_do_vang ?? '',
                'diem_buoi_hoc'    => $diemDanh->diem_buoi_hoc ?? null,
                'danh_gia_ky_luat' => $diemDanh->danh_gia_ky_luat ?? '',
                // Nếu bảng diem_danhs có các cột khác thì thêm vào đây cho khớp.
            ];
        }
    }

    public function luuDiemDanh(): void
    {
        if (!$this->selectedKhoaHoc || !$this->selectedLichHoc) {
            Notification::make()->title('Vui lòng chọn Khóa học và Buổi học trước khi điểm danh')->danger()->send();
            return;
        }

        $ok = 0; $fail = 0;

        foreach ($this->diemDanhData as $dangKyId => $data) {
            try {
                DiemDanh::updateOrCreate(
                    ['dang_ky_id' => $dangKyId, 'lich_hoc_id' => $this->selectedLichHoc],
                    $data
                );
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                \Log::error("Lỗi lưu điểm danh cho dang_ky_id $dangKyId: " . $e->getMessage());
            }
        }

        // Tính & ghi kết quả sang ket_qua_khoa_hocs (Observer sẽ lo đồng bộ 2 bảng hoàn thành/không hoàn thành)
        $this->tinhToanKetQuaKhoaHoc();

        Notification::make()
            ->title('Lưu điểm danh thành công!')
            ->body("Thành công: $ok. Thất bại: $fail.")
            ->success()
            ->send();

        // Reset trạng thái modal email (nếu Blade có)
        $this->showGuiEmailModal = false;
        $this->selectedEmailTemplateId = null;
        $this->selectedEmailAccountId = null;
        $this->loaiEmail = 'hoc_vien';

        // Reload lại trang cho chắc chắn
        redirect()->route('filament.admin.pages.diem-danh-hoc-vien');
    }

    private function tinhToanKetQuaKhoaHoc(): void
    {
        $dangKies = DangKy::where('khoa_hoc_id', $this->selectedKhoaHoc)->get();

        foreach ($dangKies as $dk) {
            $diemDanhs = DiemDanh::where('dang_ky_id', $dk->id)->get();

            $tongDiem = 0;
            $soBuoiCoDiem = 0;
            $soBuoiVang = 0;
            $tongSoBuoi = $diemDanhs->count();

            foreach ($diemDanhs as $dd) {
                if ($dd->diem_buoi_hoc !== null) {
                    $tongDiem += (float) $dd->diem_buoi_hoc;
                    $soBuoiCoDiem++;
                }
                if (in_array($dd->trang_thai, ['vang_phep', 'vang_khong_phep'])) {
                    $soBuoiVang++;
                }
            }

            $diemTongKhoa = $soBuoiCoDiem > 0 ? round($tongDiem / $soBuoiCoDiem, 2) : null;
            $tyLeVang = $tongSoBuoi > 0 ? ($soBuoiVang / $tongSoBuoi) * 100 : 0;

            // Quy tắc đạt/hoàn thành:
            // - Vắng <= 20%
            // - Không yêu cầu điểm nếu không nhập (null) hoặc điểm TB >= 5
            $ketQua = ($tyLeVang <= 20 && ($diemTongKhoa === null || $diemTongKhoa >= 5))
                ? 'hoan_thanh'
                : 'khong_hoan_thanh';

            KetQuaKhoaHoc::updateOrCreate(
                ['dang_ky_id' => $dk->id],
                [
                    'diem_tong_khoa' => $diemTongKhoa,
                    'ket_qua'        => $ketQua,                     // 'hoan_thanh' ↔ Đạt/Hoàn thành; 'khong_hoan_thanh' ↔ Không đạt/Không hoàn thành
                    'can_hoc_lai'    => $ketQua === 'khong_hoan_thanh' ? 1 : 0,
                ]
            );
            // Ghi nhớ: Đồng bộ sang 2 bảng hoc_vien_hoan_thanh / hoc_vien_khong_hoan_thanh do Observer xử lý.
        }
    }

    // ================== KHỐI EMAIL (khôi phục để khớp Blade) ==================
    public function moModalGuiEmail(): void
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()->title('Vui lòng chọn Khóa học trước khi gửi email')->danger()->send();
            return;
        }
        if (empty($this->hocViensDaDangKy)) {
            Notification::make()->title('Không có học viên nào để gửi email')->warning()->send();
            return;
        }
        $this->showGuiEmailModal = true;
    }

    public function guiEmailHangLoat(): void
    {
        // Nếu Blade có form gửi email thì giữ nguyên API như cũ để không lỗi:
        $this->validate([
            'selectedEmailTemplateId' => 'required|exists:email_templates,id',
            'selectedEmailAccountId'  => 'required|exists:email_accounts,id',
            'loaiEmail'               => 'required|in:hoc_vien,giang_vien',
        ], [
            'selectedEmailTemplateId.required' => 'Vui lòng chọn mẫu email.',
            'selectedEmailTemplateId.exists'   => 'Mẫu email không tồn tại.',
            'selectedEmailAccountId.required'  => 'Vui lòng chọn tài khoản gửi email.',
            'selectedEmailAccountId.exists'    => 'Tài khoản gửi email không tồn tại.',
            'loaiEmail.required'               => 'Vui lòng chọn loại email.',
            'loaiEmail.in'                     => 'Loại email không hợp lệ.',
        ]);

        $template  = EmailTemplate::find($this->selectedEmailTemplateId);
        $emailAcct = EmailAccount::find($this->selectedEmailAccountId);
        $khoaHoc   = KhoaHoc::with('chuongTrinh')->find($this->selectedKhoaHoc);

        if (!$template || !$emailAcct || !$khoaHoc) {
            Notification::make()->title('Thiếu dữ liệu gửi email')->danger()->send();
            $this->showGuiEmailModal = false;
            return;
        }

        // Cấu hình mailer động
        Config::set('mail.mailers.dynamic', [
            'transport'  => 'smtp',
            'host'       => $emailAcct->host,
            'port'       => $emailAcct->port,
            'encryption' => $emailAcct->encryption_tls ? 'tls' : null,
            'username'   => $emailAcct->username,
            'password'   => $emailAcct->password,
        ]);
        Config::set('mail.from', [
            'address' => $emailAcct->email,
            'name'    => $emailAcct->name,
        ]);

        $ok = 0; $fail = 0;

        if ($this->loaiEmail === 'hoc_vien') {
            foreach ($this->hocViensDaDangKy as $hocVien) {
                $recipientEmail = $hocVien->email ?? null;
                if (!$recipientEmail) {
                    $fail++;
                    EmailLog::create([
                        'email_account_id' => $emailAcct->id,
                        'recipient_email'  => 'N/A',
                        'subject'          => 'Không gửi (thiếu email học viên)',
                        'content'          => '',
                        'status'           => 'failed',
                        'error_message'    => 'Học viên không có email.',
                    ]);
                    continue;
                }

                $placeholders = [
                    '{ten_hoc_vien}'     => $hocVien->ho_ten ?? 'N/A',
                    '{msnv}'             => $hocVien->msnv ?? 'N/A',
                    '{ma_khoa_hoc}'      => $khoaHoc->ma_khoa_hoc ?? 'N/A',
                    '{ten_chuong_trinh}' => optional($khoaHoc->chuongTrinh)->ten_chuong_trinh ?? 'N/A',
                ];

                $tieuDe  = strtr($template->tieu_de,  $placeholders);
                $noiDung = strtr($template->noi_dung, $placeholders);

                try {
                    // Dùng mailable tối giản: gửi raw html
                    Mail::mailer('dynamic')->send(new \App\Mail\PlanNotificationMail($tieuDe, $noiDung));
                    $ok++;
                    $status = 'success'; $err = null;
                } catch (\Throwable $e) {
                    $fail++; $status = 'failed'; $err = $e->getMessage();
                    \Log::error("Lỗi gửi email tới {$recipientEmail}: " . $e->getMessage());
                }

                EmailLog::create([
                    'email_account_id' => $emailAcct->id,
                    'recipient_email'  => $recipientEmail,
                    'subject'          => $tieuDe,
                    'content'          => $noiDung,
                    'status'           => $status,
                    'error_message'    => $err,
                ]);
            }
        } else {
            // Gửi cho giảng viên theo lịch học của khóa
            $giangViens = $this->getDanhSachGiangVien();
            foreach ($giangViens as $gv) {
                $recipientEmail = $gv->email ?? null;
                if (!$recipientEmail) {
                    $fail++;
                    EmailLog::create([
                        'email_account_id' => $emailAcct->id,
                        'recipient_email'  => 'N/A',
                        'subject'          => 'Không gửi (thiếu email giảng viên)',
                        'content'          => '',
                        'status'           => 'failed',
                        'error_message'    => 'Giảng viên không có email.',
                    ]);
                    continue;
                }

                $placeholders = [
                    '{ten_giang_vien}'   => $gv->ho_ten ?? 'N/A',
                    '{ma_khoa_hoc}'      => $khoaHoc->ma_khoa_hoc ?? 'N/A',
                    '{ten_chuong_trinh}' => optional($khoaHoc->chuongTrinh)->ten_chuong_trinh ?? 'N/A',
                ];

                $tieuDe  = strtr($template->tieu_de,  $placeholders);
                $noiDung = strtr($template->noi_dung, $placeholders);

                try {
                    Mail::mailer('dynamic')->send(new \App\Mail\PlanNotificationMail($tieuDe, $noiDung));
                    $ok++;
                    $status = 'success'; $err = null;
                } catch (\Throwable $e) {
                    $fail++; $status = 'failed'; $err = $e->getMessage();
                    \Log::error("Lỗi gửi email tới GV {$recipientEmail}: " . $e->getMessage());
                }

                EmailLog::create([
                    'email_account_id' => $emailAcct->id,
                    'recipient_email'  => $recipientEmail,
                    'subject'          => $tieuDe,
                    'content'          => $noiDung,
                    'status'           => $status,
                    'error_message'    => $err,
                ]);
            }
        }

        $this->showGuiEmailModal = false;
        $this->selectedEmailTemplateId = null;
        $this->selectedEmailAccountId = null;
        $this->loaiEmail = 'hoc_vien';

        Notification::make()
            ->title("Gửi email hoàn tất!")
            ->body("Thành công: $ok. Thất bại: $fail.")
            ->success()
            ->send();
    }
    // ================== HẾT KHỐI EMAIL ==================

    private function getDanhSachGiangVien()
    {
        if (!$this->selectedKhoaHoc) return collect();

        // Lấy qua relation từ KhoaHoc -> lichHocs -> giangVien (tùy model Sư phụ định nghĩa)
        $khoaHoc = KhoaHoc::with('lichHocs.giangVien')->find($this->selectedKhoaHoc);
        if (!$khoaHoc) return collect();

        return $khoaHoc->lichHocs->pluck('giangVien')->filter();
    }

    public static function getSlug(): string
    {
        return 'diem-danh-hoc-vien';
    }
}
