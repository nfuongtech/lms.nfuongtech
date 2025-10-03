<?php

namespace App\Filament\Pages;

use App\Models\KhoaHoc;
use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\LichHoc;
use App\Models\KetQuaKhoaHoc;
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
    public $selectedKhoaHoc = null;
    public $selectedLichHoc = null;

    // --- Danh sách học viên & dữ liệu điểm danh ---
    public $hocViensDaDangKy = [];
    public $diemDanhData = [];
    public $isEditing = []; // đóng/mở theo từng học viên

    // --- Bảng liệt kê Khóa học theo Năm (kèm khả năng bấm mở buổi học) ---
    // mỗi phần tử: [
    //   'khoa_hoc_id','ma_khoa_hoc','ten_khoa_hoc','trang_thai','so_buoi','tuan','max_tuan',
    //   'ngay_dao_tao','giang_vien','so_luong_hv','lichs' => [ {id,tuan,ngay_hoc,gio_bat_dau,gio_ket_thuc,ten_chuyen_de,dia_diem}, ... ]
    // ]
    public $khoaHocYearRows = [];

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
        $this->refreshAvailableLichHocs();
        $this->refreshHocViens();
        $this->refreshKhoaHocYearRows();
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
        $this->refreshAvailableLichHocs();
        $this->refreshHocViens();
        $this->refreshKhoaHocYearRows();
    }

    public function updatedSelectedTuan(): void
    {
        $this->selectedKhoaHoc = null;
        $this->selectedLichHoc = null;

        $this->refreshAvailableKhoaHocs();
        $this->availableLichHocs = collect();
        $this->refreshHocViens();
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

        $this->availableKhoaHocs = KhoaHoc::with('chuongTrinh')
            ->whereIn('id', $lichHocIds)
            ->orderBy('ma_khoa_hoc')
            ->get();

        if ($this->selectedKhoaHoc && !$this->availableKhoaHocs->contains('id', $this->selectedKhoaHoc)) {
            $this->selectedKhoaHoc = null;
            $this->selectedLichHoc = null;
            $this->availableLichHocs = collect();
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
        $this->isEditing = [];

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

        $dangKies = DangKy::with(['hocVien', 'hocVien.dangKies'])
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
            ];

            // MẶC ĐỊNH "ĐÓNG" nếu đã có bản ghi điểm danh cho buổi này (đảm bảo load lại trang vẫn đóng)
            $this->isEditing[$dk->id] = $diemDanh ? false : true;
        }
    }

    private function refreshKhoaHocYearRows(): void
    {
        $this->khoaHocYearRows = [];
        if (!$this->selectedNam) return;

        // Lấy tất cả Khoa học có lịch trong năm
        $khoaHocIds = LichHoc::query()
            ->where('nam', $this->selectedNam)
            ->pluck('khoa_hoc_id')
            ->unique();

        if ($khoaHocIds->isEmpty()) return;

        $khoaHocs = KhoaHoc::with([
            'chuongTrinh',
            'lichHocs' => function ($q) {
                $q->orderBy('ngay_hoc');
            },
            'lichHocs.chuyenDe',
            'lichHocs.giangVien',
        ])
        ->whereIn('id', $khoaHocIds)
        ->orderBy('ma_khoa_hoc')
        ->get();

        $rows = [];

        foreach ($khoaHocs as $kh) {
            $lichTrongNam = $kh->lichHocs->where('nam', $this->selectedNam);

            $soBuoi = $lichTrongNam->count();
            $tuanSet = $lichTrongNam->pluck('tuan')->unique()->values();
            $tuanCsv = $tuanSet->implode(', ');
            $maxTuan = $tuanSet->count() ? (int) $tuanSet->max() : 0;

            $ngayMin = $lichTrongNam->min('ngay_hoc');
            $ngayMax = $lichTrongNam->max('ngay_hoc');
            if ($ngayMin && $ngayMax) {
                $ngayDaoTao = date('d/m/Y', strtotime($ngayMin)) . ' - ' . date('d/m/Y', strtotime($ngayMax));
            } elseif ($ngayMin) {
                $ngayDaoTao = date('d/m/Y', strtotime($ngayMin));
            } else {
                $ngayDaoTao = '';
            }

            // Gộp giảng viên
            $giangVienNames = [];
            foreach ($lichTrongNam as $lh) {
                $gv = $lh->giangVien ?? null;
                if ($gv && !empty($gv->ho_ten)) {
                    $giangVienNames[$gv->ho_ten] = true;
                }
            }
            $giangVienCsv = implode(', ', array_keys($giangVienNames));

            // Số lượng HV đã đăng ký
            $soLuongHv = DangKy::where('khoa_hoc_id', $kh->id)->count();

            // Trạng thái hiển thị
            $trangThai = $kh->trang_thai ?? ($soBuoi > 0 ? 'Hoạt động' : '');

            // Lắp danh sách buổi học chi tiết (để click chọn)
            $lichs = [];
            foreach ($lichTrongNam as $lh) {
                $lichs[] = [
                    'id'            => $lh->id,
                    'tuan'          => $lh->tuan,
                    'ngay_hoc'      => $lh->ngay_hoc,
                    'gio_bat_dau'   => $lh->gio_bat_dau,
                    'gio_ket_thuc'  => $lh->gio_ket_thuc,
                    'ten_chuyen_de' => optional($lh->chuyenDe)->ten_chuyen_de,
                    'dia_diem'      => $lh->dia_diem,
                ];
            }

            $rows[] = [
                'khoa_hoc_id'  => $kh->id,
                'ma_khoa_hoc'  => $kh->ma_khoa_hoc ?? '',
                'ten_khoa_hoc' => optional($kh->chuongTrinh)->ten_chuong_trinh ?? ($kh->ten_khoa_hoc ?? ''),
                'trang_thai'   => $trangThai,
                'so_buoi'      => $soBuoi,
                'tuan'         => $tuanCsv,
                'max_tuan'     => $maxTuan,
                'ngay_dao_tao' => $ngayDaoTao,
                'giang_vien'   => $giangVienCsv,
                'so_luong_hv'  => $soLuongHv,
                'lichs'        => $lichs,
            ];
        }

        // SẮP XẾP THEO TUẦN MỚI NHẤT (max_tuan DESC), phụ theo mã khóa
        usort($rows, function ($a, $b) {
            return ($b['max_tuan'] <=> $a['max_tuan'])
                ?: strcmp($a['ma_khoa_hoc'], $b['ma_khoa_hoc']);
        });

        $this->khoaHocYearRows = $rows;
    }

    /**
     * Chọn buổi học từ bảng "Danh sách Khóa học trong năm"
     * LƯU Ý: không dùng type-hint tham số để Livewire bind ổn định.
     */
    public function chonBuoiTuBangNam($khoaHocId, $tuan, $lichHocId): void
    {
        // Chuẩn hóa kiểu
        $khoaHocId = (int) $khoaHocId;
        $tuan      = (int) $tuan;
        $lichHocId = (int) $lichHocId;

        // B1: đặt tuần trước (để load availableKhoaHocs theo tuần đó)
        $this->selectedTuan = $tuan;
        $this->refreshAvailableKhoaHocs();

        // B2: đặt khóa học -> nạp buổi
        $this->selectedKhoaHoc = $khoaHocId;
        $this->refreshAvailableLichHocs();

        // B3: đặt buổi học nếu tồn tại trong danh sách được nạp
        if ($this->availableLichHocs->contains('id', $lichHocId)) {
            $this->selectedLichHoc = $lichHocId;
        } else {
            $this->selectedLichHoc = $this->availableLichHocs->first()->id ?? null;
        }

        // B4: nạp học viên + dữ liệu điểm danh
        $this->refreshHocViens();
    }

    public function dongDiemDanh(int $dangKyId): void
    {
        $this->isEditing[$dangKyId] = false;
    }

    public function moSuaDiemDanh(int $dangKyId): void
    {
        $this->isEditing[$dangKyId] = true;
    }

    public function luuDiemDanh(): void
    {
        if (!$this->selectedKhoaHoc || !$this->selectedLichHoc) {
            Notification::make()->title('Vui lòng chọn Khóa học và Buổi học trước khi điểm danh')->danger()->send();
            return;
        }

        $ok = 0; $fail = 0;

        foreach ($this->diemDanhData as $dangKyId => $data) {
            // Ràng buộc nghiệp vụ
            $trangThai = $data['trang_thai'] ?? 'co_mat';
            $lyDo = $data['ly_do_vang'] ?? null;

            if ($trangThai === 'co_mat') {
                $data['ly_do_vang'] = null;
            } elseif ($trangThai === 'vang_phep') {
                if (!$lyDo || trim($lyDo) === '') {
                    $fail++;
                    \Log::warning("Điểm danh bỏ qua (thiếu lý do vắng phép) cho dang_ky_id $dangKyId");
                    continue;
                }
            }

            // Chuẩn hóa điểm
            if (array_key_exists('diem_buoi_hoc', $data) && $data['diem_buoi_hoc'] !== null && $data['diem_buoi_hoc'] !== '') {
                $diem = (float) $data['diem_buoi_hoc'];
                if ($diem < 0) $diem = 0;
                if ($diem > 10) $diem = 10;
                $data['diem_buoi_hoc'] = $diem;
            } else {
                $data['diem_buoi_hoc'] = null;
            }

            try {
                DiemDanh::updateOrCreate(
                    ['dang_ky_id' => $dangKyId, 'lich_hoc_id' => $this->selectedLichHoc],
                    $data
                );
                $ok++;
                $this->isEditing[$dangKyId] = false; // đóng tuyệt đối
            } catch (\Throwable $e) {
                $fail++;
                \Log::error("Lỗi lưu điểm danh cho dang_ky_id $dangKyId: " . $e->getMessage());
            }
        }

        $this->tinhToanKetQuaKhoaHoc();

        Notification::make()
            ->title('Lưu điểm danh thành công!')
            ->body("Thành công: $ok. Thất bại / bỏ qua: $fail.")
            ->success()
            ->send();
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

            // Chỉ 2 trạng thái: Hoàn thành / Không hoàn thành
            $ketQua = ($tyLeVang <= 20 && ($diemTongKhoa === null || $diemTongKhoa >= 5))
                ? 'hoan_thanh'
                : 'khong_hoan_thanh';

            KetQuaKhoaHoc::updateOrCreate(
                ['dang_ky_id' => $dk->id],
                [
                    'diem_tong_khoa' => $diemTongKhoa,
                    'ket_qua'        => $ketQua,
                    'can_hoc_lai'    => $ketQua === 'khong_hoan_thanh' ? 1 : 0,
                ]
            );
        }
    }

    // ================== KHỐI EMAIL ==================
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

                // Thay placeholder mở rộng
                $dangKy = $hocVien->dangKies()->where('khoa_hoc_id', $this->selectedKhoaHoc)->first();
                $dd = null;
                if ($dangKy) {
                    $dd = DiemDanh::where('dang_ky_id', $dangKy->id)
                        ->where('lich_hoc_id', $this->selectedLichHoc)
                        ->first();
                }
                $ttMap = [
                    'co_mat' => 'Có mặt',
                    'vang_phep' => 'Vắng phép',
                    'vang_khong_phep' => 'Vắng không phép',
                ];
                $placeholders = [
                    '{ten_hoc_vien}'     => $hocVien->ho_ten ?? 'N/A',
                    '{msnv}'             => $hocVien->msnv ?? 'N/A',
                    '{ma_khoa_hoc}'      => $khoaHoc->ma_khoa_hoc ?? 'N/A',
                    '{ten_chuong_trinh}' => optional($khoaHoc->chuongTrinh)->ten_chuong_trinh ?? 'N/A',
                    '{chuc_vu}'          => $hocVien->chuc_vu ?? '',
                    '{don_vi}'           => optional($hocVien->donVi)->ten_hien_thi ?? '',
                    '{tinh_trang}'       => $ttMap[$dd->trang_thai ?? 'co_mat'] ?? 'Có mặt',
                    '{ly_do_vang}'       => $dd->ly_do_vang ?? '',
                    '{diem_buoi_hoc}'    => ($dd && $dd->diem_buoi_hoc !== null) ? (string)$dd->diem_buoi_hoc : '',
                    '{danh_gia_ky_luat}' => $dd->danh_gia_ky_luat ?? '',
                ];

                $tieuDe  = strtr($template->tieu_de,  $placeholders);
                $noiDung = strtr($template->noi_dung, $placeholders);

                try {
                    Mail::mailer('dynamic')
                        ->to($recipientEmail)
                        ->send(new \App\Mail\PlanNotificationMail($tieuDe, $noiDung));
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
                    Mail::mailer('dynamic')
                        ->to($recipientEmail)
                        ->send(new \App\Mail\PlanNotificationMail($tieuDe, $noiDung));
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

        // Lấy qua relation từ KhoaHoc -> lichHocs -> giangVien (tùy model định nghĩa)
        $khoaHoc = KhoaHoc::with('lichHocs.giangVien')->find($this->selectedKhoaHoc);
        if (!$khoaHoc) return collect();

        return $khoaHoc->lichHocs->pluck('giangVien')->filter();
    }

    public static function getSlug(): string
    {
        return 'diem-danh-hoc-vien';
    }
}
