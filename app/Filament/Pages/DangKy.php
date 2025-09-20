<?php

namespace App\Filament\Pages;

use App\Models\HocVien;
use App\Models\KhoaHoc;
use App\Models\DangKy as DangKyModel;
use App\Models\EmailTemplate;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use App\Models\GiangVien;
use App\Models\LichHoc; // Thêm model LichHoc để lấy tuần
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Mail\PlanNotificationMail;
// --- Thêm cho xuất Excel ---
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ThongTinKhoaHocExport;
use App\Exports\DanhSachHocVienExport;
// --- Hết thêm cho xuất Excel ---
use App\Models\ChuongTrinh;
use Illuminate\Support\Facades\Schema;

class DangKy extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Đăng ký học viên';
    protected static string $view = 'filament.pages.dang-ky';

    // --- Các biến cho lọc và chọn khóa học ---
    public $selectedTuan = null;
    public $selectedTrangThaiKeHoach = null;
    public $selectedKhoaHoc = null; // ID của khóa học được chọn
    // --- Hết các biến cho lọc và chọn ---

    public $msnvInput = '';
    public $parsedHocViens = [];
    public $parsedMsnvNotFound = [];
    public $hocViensDaDangKy = [];

    public $showAddHocVienModal = false;
    public $newHocVien = [
        'msnv' => '',
        'ho_ten' => '',
        'email' => '',
        'chuc_vu' => '',
        'don_vi_id' => null,
        'nam_sinh' => null,
    ];

    // --- Thêm biến cho gửi email ---
    public $showGuiEmailModal = false;
    public $selectedEmailTemplateId = null;
    public $selectedEmailAccountId = null;
    public $loaiEmail = 'hoc_vien'; // 'hoc_vien' hoặc 'giang_vien'
    // --- Hết thêm biến cho gửi email ---

    // Biến giữ thời lượng (có thể dùng trong view nếu cần)
    public $thoiLuong = 0;

    public function mount(): void
    {
        $this->refreshHocViens();
    }

    // --- Cập nhật danh sách khóa học khi thay đổi tuần hoặc trạng thái ---
    public function updatedSelectedTuan(): void
    {
        $this->selectedKhoaHoc = null;
        $this->hocViensDaDangKy = collect();
    }

    public function updatedSelectedTrangThaiKeHoach(): void
    {
        $this->selectedKhoaHoc = null;
        $this->hocViensDaDangKy = collect();
    }

    // --- Khi người dùng chọn một khóa học từ danh sách (dựa trên ID) ---
    public function updatedSelectedKhoaHoc(): void
    {
        $this->refreshHocViens();

        // --- Bổ sung: tính thời lượng từ chuong_trinhs "Đang áp dụng" dựa trên chuongTrinh của khóa học ---
        $this->computeThoiLuongForSelectedKhoaHoc();
    }

    /**
     * Tính tổng thời lượng (giờ) cho khóa học được chọn lấy từ bảng chuong_trinhs
     * (dùng chuongTrinh liên kết từ KhoaHoc thay vì where('khoa_hoc_id', ...))
     */
    private function computeThoiLuongForSelectedKhoaHoc(): void
    {
        $this->thoiLuong = 0;

        if (!$this->selectedKhoaHoc) {
            return;
        }

        $khoaHoc = KhoaHoc::with('chuongTrinh', 'lichHocs')->find($this->selectedKhoaHoc);
        if (!$khoaHoc) {
            return;
        }

        // Nếu khóa học liên kết tới 1 chuongTrinh cụ thể thì dùng id đó để lọc chuong_trinhs
        $chuongTrinh = $khoaHoc->chuongTrinh ?? null;

        // Xác định tên cột giờ trên bảng chuong_trinhs (nhiều tên cột phổ biến)
        $column = null;
        if (Schema::hasColumn('chuong_trinhs', 'so_gio')) {
            $column = 'so_gio';
        } elseif (Schema::hasColumn('chuong_trinhs', 'thoi_luong')) {
            $column = 'thoi_luong';
        } elseif (Schema::hasColumn('chuong_trinhs', 'gio')) {
            $column = 'gio';
        }

        if ($chuongTrinh && $column) {
            // Truy vấn an toàn: dùng id của chuongTrinh (KHÔNG dùng khoa_hoc_id)
            $this->thoiLuong = ChuongTrinh::where('id', $chuongTrinh->id)
                ->where('tinh_trang', 'Đang áp dụng')
                ->sum($column);
        } else {
            // Fallback an toàn: tổng theo lichHocs của khóa học (không gây lỗi SQL)
            try {
                $this->thoiLuong = $khoaHoc->lichHocs->sum(function($lich) {
                    // cố gắng dùng trực tiếp trường 'thoi_luong' của lichHoc nếu có
                    if (isset($lich->thoi_luong)) {
                        return $lich->thoi_luong;
                    }
                    // nếu không, cố gắng tính từ gio_bat_dau/gio_ket_thuc (minutes -> hours)
                    if ($lich->gio_bat_dau && $lich->gio_ket_thuc) {
                        try {
                            $start = \Carbon\Carbon::parse($lich->gio_bat_dau);
                            $end = \Carbon\Carbon::parse($lich->gio_ket_thuc);
                            return $end->diffInMinutes($start) / 60;
                        } catch (\Throwable $e) {
                            return 0;
                        }
                    }
                    return 0;
                }) ?? 0;
            } catch (\Throwable $e) {
                $this->thoiLuong = 0;
            }
        }
    }

    public function updatedMsnvInput($value): void
    {
        $this->parsedHocViens = [];
        $this->parsedMsnvNotFound = [];

        $msnvList = array_filter(array_map('trim', explode(',', $value)));

        foreach ($msnvList as $msnv) {
            $hv = HocVien::where('msnv', $msnv)
                ->where('tinh_trang', 'Đang làm việc')
                ->first();

            if ($hv) {
                $this->parsedHocViens[] = [
                    'id' => $hv->id,
                    'msnv' => $hv->msnv,
                    'ho_ten' => $hv->ho_ten,
                    'chuc_vu' => $hv->chuc_vu,
                    'don_vi' => $hv->donVi->ten_hien_thi ?? '',
                    'nam_sinh' => $hv->nam_sinh ? date('d/m/Y', strtotime($hv->nam_sinh)) : 'N/A',
                    'email' => $hv->email ?? 'N/A',
                    'display' => "{$hv->msnv} - {$hv->ho_ten}, {$hv->donVi->ten_hien_thi}",
                ];
            } else {
                $this->parsedMsnvNotFound[] = $msnv;
            }
        }
    }

    public function saveNewHocVien()
    {
        $this->validate([
            'newHocVien.msnv' => 'required|unique:hoc_viens,msnv',
            'newHocVien.ho_ten' => 'required',
            'newHocVien.email' => 'nullable|email|unique:hoc_viens,email',
            'newHocVien.nam_sinh' => 'nullable|date_format:d/m/Y',
        ], [
            'newHocVien.msnv.unique' => 'MSNV đã tồn tại',
            'newHocVien.msnv.required' => 'Vui lòng nhập MSNV',
            'newHocVien.ho_ten.required' => 'Vui lòng nhập họ tên',
            'newHocVien.email.email' => 'Email không hợp lệ',
            'newHocVien.email.unique' => 'Email đã tồn tại',
            'newHocVien.nam_sinh.date_format' => 'Năm sinh không đúng định dạng dd/mm/yyyy',
        ]);

        $namSinhDb = null;
        if ($this->newHocVien['nam_sinh']) {
            $namSinhDb = \Carbon\Carbon::createFromFormat('d/m/Y', $this->newHocVien['nam_sinh'])->format('Y-m-d');
        }

        $hocVien = HocVien::create([
            'msnv' => $this->newHocVien['msnv'],
            'ho_ten' => $this->newHocVien['ho_ten'],
            'email' => $this->newHocVien['email'],
            'chuc_vu' => $this->newHocVien['chuc_vu'],
            'don_vi_id' => $this->newHocVien['don_vi_id'],
            'nam_sinh' => $namSinhDb,
            'tinh_trang' => 'Đang làm việc',
        ]);

        $this->parsedHocViens[] = [
            'id' => $hocVien->id,
            'msnv' => $hocVien->msnv,
            'ho_ten' => $hocVien->ho_ten,
            'chuc_vu' => $hocVien->chuc_vu,
            'don_vi' => $hocVien->donVi->ten_hien_thi ?? '',
            'nam_sinh' => $hocVien->nam_sinh ? date('d/m/Y', strtotime($hocVien->nam_sinh)) : 'N/A',
            'email' => $hocVien->email ?? 'N/A',
            'display' => "{$hocVien->msnv} - {$hocVien->ho_ten}, {$hocVien->donVi->ten_hien_thi}",
        ];

        $this->parsedMsnvNotFound = array_diff($this->parsedMsnvNotFound, [$this->newHocVien['msnv']]);

        $this->showAddHocVienModal = false;

        $this->newHocVien = [
            'msnv' => '',
            'ho_ten' => '',
            'email' => '',
            'chuc_vu' => '',
            'don_vi_id' => null,
            'nam_sinh' => null,
        ];

        Notification::make()
            ->title('Thêm học viên thành công')
            ->success()
            ->send();
    }

    public function store(): void
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()
                ->title('Vui lòng chọn Khóa học trước khi ghi danh')
                ->danger()
                ->send();
            return;
        }

        $soLuongThemMoi = 0;
        foreach ($this->parsedHocViens as $hv) {
            $exists = DangKyModel::where('hoc_vien_id', $hv['id'])
                ->where('khoa_hoc_id', $this->selectedKhoaHoc)
                ->exists();

            if (!$exists) {
                DangKyModel::create([
                    'hoc_vien_id' => $hv['id'],
                    'khoa_hoc_id' => $this->selectedKhoaHoc,
                ]);
                $soLuongThemMoi++;
            }
        }

        $this->refreshHocViens();
        $this->msnvInput = '';
        $this->parsedHocViens = [];

        Notification::make()
            ->title("Ghi danh thành công! Đã thêm $soLuongThemMoi học viên.")
            ->success()
            ->send();
    }

    public function deleteDangKy($id): void
    {
        DangKyModel::where('id', $id)->delete();
        $this->refreshHocViens();
        Notification::make()
            ->title('Xóa đăng ký thành công')
            ->success()
            ->send();
    }

    // --- BẮT ĐẦU: Chức năng gửi email ---
    public function moModalGuiEmail()
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()
                ->title('Vui lòng chọn Khóa học trước khi gửi email')
                ->danger()
                ->send();
            return;
        }

        // MỞ modal bất kể có học viên/giảng viên hay không (để người dùng xem template/tài khoản) —
        // nếu không có recipient, hiển thị cảnh báo trong modal (blade xử lý nếu cần).
        $this->showGuiEmailModal = true;
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

    // Hỗ trợ lấy danh sách mẫu email an toàn cho view
    public function getEmailTemplates(): Collection
    {
        try {
            return EmailTemplate::all();
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi load EmailTemplate: ' . $e->getMessage());
            return collect();
        }
    }

    // Hỗ trợ lấy danh sách tài khoản email an toàn cho view
    public function getEmailAccounts(): Collection
    {
        try {
            return EmailAccount::where('active', 1)->get();
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi load EmailAccount: ' . $e->getMessage());
            return collect();
        }
    }

    public function guiEmailHangLoat()
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
            'loaiEmail.required' => 'Vui lòng chọn loại email gửi đi.',
            'loaiEmail.in' => 'Loại email không hợp lệ.',
        ]);

        $template = EmailTemplate::find($this->selectedEmailTemplateId);
        $emailAccount = EmailAccount::find($this->selectedEmailAccountId);
        $khoaHoc = KhoaHoc::with('chuongTrinh')->find($this->selectedKhoaHoc);

        if (!$template || !$emailAccount || !$khoaHoc) {
            Notification::make()
                ->title('Lỗi: Không tìm thấy thông tin cần thiết để gửi email.')
                ->danger()
                ->send();
            $this->showGuiEmailModal = false;
            $this->selectedEmailTemplateId = null;
            $this->selectedEmailAccountId = null;
            return;
        }

        Config::set('mail.mailers.dynamic', [
            'transport' => 'smtp',
            'host' => $emailAccount->host,
            'port' => $emailAccount->port,
            'encryption' => $emailAccount->encryption_tls ? 'tls' : null,
            'username' => $emailAccount->username,
            'password' => $emailAccount->password,
        ]);
        Config::set('mail.from', [
            'address' => $emailAccount->email,
            'name' => $emailAccount->name,
        ]);

        $soLuongGuiThanhCong = 0;
        $soLuongGuiThatBai = 0;

        if ($this->loaiEmail === 'hoc_vien') {
            foreach ($this->hocViensDaDangKy as $dk) {
                $hocVien = $dk->hocVien;
                $recipientEmail = $hocVien->email ?? 'N/A';

                if (!$hocVien || !$hocVien->email) {
                    $soLuongGuiThatBai++;
                    EmailLog::create([
                        'email_account_id' => $emailAccount->id,
                        'recipient_email' => $recipientEmail,
                        'subject' => 'Không gửi (thiếu email)',
                        'content' => 'Học viên không có địa chỉ email.',
                        'status' => 'failed',
                        'error_message' => 'Học viên không có địa chỉ email.',
                    ]);
                    continue;
                }

                $placeholders = [
                    '{ten_hoc_vien}' => $hocVien->ho_ten ?? 'N/A',
                    '{msnv}' => $hocVien->msnv ?? 'N/A',
                    '{ma_khoa_hoc}' => $khoaHoc->ma_khoa_hoc ?? 'N/A',
                    '{ten_chuong_trinh}' => $khoaHoc->chuongTrinh->ten_chuong_trinh ?? 'N/A',
                ];

                $tieuDe = $template->tieu_de ?? ($template->ten_mau ?? 'Không có tiêu đề');
                $noiDung = $template->noi_dung ?? '';

                foreach ($placeholders as $placeholder => $value) {
                    $tieuDe = str_replace($placeholder, $value, $tieuDe);
                    $noiDung = str_replace($placeholder, $value, $noiDung);
                }

                $success = true;
                $loiLog = null;
                try {
                    Mail::mailer('dynamic')->to($hocVien->email, $hocVien->ho_ten)->send(new PlanNotificationMail($tieuDe, $noiDung));
                } catch (\Throwable $e) {
                    $success = false;
                    $loiLog = $e->getMessage();
                    \Log::error("Lỗi gửi email tới {$hocVien->email}: " . $e->getMessage());
                }

                $trangThaiLog = $success ? 'success' : 'failed';
                if ($success) $soLuongGuiThanhCong++; else $soLuongGuiThatBai++;

                EmailLog::create([
                    'email_account_id' => $emailAccount->id,
                    'recipient_email' => $recipientEmail,
                    'subject' => $tieuDe ?? 'Không có tiêu đề',
                    'content' => $noiDung ?? 'Không có nội dung',
                    'status' => $trangThaiLog,
                    'error_message' => $loiLog,
                ]);
            }
        }
        elseif ($this->loaiEmail === 'giang_vien') {
            $giangViens = $this->getDanhSachGiangVien();
            foreach ($giangViens as $giangVien) {
                $recipientEmail = $giangVien->email ?? 'N/A';

                if (!$giangVien || !$giangVien->email) {
                    $soLuongGuiThatBai++;
                    EmailLog::create([
                        'email_account_id' => $emailAccount->id,
                        'recipient_email' => $recipientEmail,
                        'subject' => 'Không gửi (thiếu email giảng viên)',
                        'content' => 'Giảng viên không có địa chỉ email.',
                        'status' => 'failed',
                        'error_message' => 'Giảng viên không có địa chỉ email.',
                    ]);
                    continue;
                }

                $placeholders = [
                    '{ten_giang_vien}' => $giangVien->ho_ten ?? 'N/A',
                    '{ma_khoa_hoc}' => $khoaHoc->ma_khoa_hoc ?? 'N/A',
                    '{ten_chuong_trinh}' => $khoaHoc->chuongTrinh->ten_chuong_trinh ?? 'N/A',
                ];

                $tieuDe = $template->tieu_de ?? ($template->ten_mau ?? 'Không có tiêu đề');
                $noiDung = $template->noi_dung ?? '';

                foreach ($placeholders as $placeholder => $value) {
                    $tieuDe = str_replace($placeholder, $value, $tieuDe);
                    $noiDung = str_replace($placeholder, $value, $noiDung);
                }

                $success = true;
                $loiLog = null;
                try {
                    Mail::mailer('dynamic')->to($giangVien->email, $giangVien->ho_ten)->send(new PlanNotificationMail($tieuDe, $noiDung));
                } catch (\Throwable $e) {
                    $success = false;
                    $loiLog = $e->getMessage();
                    \Log::error("Lỗi gửi email tới giảng viên {$giangVien->email}: " . $e->getMessage());
                }

                $trangThaiLog = $success ? 'success' : 'failed';
                if ($success) $soLuongGuiThanhCong++; else $soLuongGuiThatBai++;

                EmailLog::create([
                    'email_account_id' => $emailAccount->id,
                    'recipient_email' => $recipientEmail,
                    'subject' => $tieuDe ?? 'Không có tiêu đề',
                    'content' => $noiDung ?? 'Không có nội dung',
                    'status' => $trangThaiLog,
                    'error_message' => $loiLog,
                ]);
            }
        }

        $this->showGuiEmailModal = false;
        $this->selectedEmailTemplateId = null;
        $this->selectedEmailAccountId = null;
        $this->loaiEmail = 'hoc_vien';

        Notification::make()
            ->title("Gửi email hoàn tất!")
            ->body("Thành công: $soLuongGuiThanhCong. Thất bại: $soLuongGuiThatBai.")
            ->success()
            ->send();
    }
    // --- KẾT THÚC: Chức năng gửi email ---

    // --- BẮT ĐẦU: Chức năng gửi lại email cho từng học viên ---
    public function guiLaiEmailChoHocVien($dangKyId)
    {
        $dangKy = DangKyModel::with('hocVien', 'khoaHoc.chuongTrinh')->find($dangKyId);
        if (!$dangKy || !$dangKy->hocVien || !$dangKy->hocVien->email) {
            Notification::make()
                ->title('Không thể gửi lại email: Học viên không có địa chỉ email.')
                ->danger()
                ->send();
            return;
        }

        // Tìm tài khoản email mặc định
        $emailAccount = EmailAccount::where('is_default', 1)->where('active', 1)->first();
        if (!$emailAccount) {
            Notification::make()
                ->title('Không tìm thấy tài khoản email mặc định để gửi lại.')
                ->danger()
                ->send();
            return;
        }

        // Tìm mẫu email mặc định cho loại 'them_hoc_vien' (hoặc bạn có thể chọn mẫu khác)
        $template = EmailTemplate::where('loai_thong_bao', 'them_hoc_vien')->first();
        if (!$template) {
            Notification::make()
                ->title('Không tìm thấy mẫu email để gửi lại.')
                ->danger()
                ->send();
            return;
        }

        // Cấu hình mailer
        Config::set('mail.mailers.dynamic', [
            'transport' => 'smtp',
            'host' => $emailAccount->host,
            'port' => $emailAccount->port,
            'encryption' => $emailAccount->encryption_tls ? 'tls' : null,
            'username' => $emailAccount->username,
            'password' => $emailAccount->password,
        ]);
        Config::set('mail.from', [
            'address' => $emailAccount->email,
            'name' => $emailAccount->name,
        ]);

        // Chuẩn bị nội dung
        $hocVien = $dangKy->hocVien;
        $khoaHoc = $dangKy->khoaHoc;
        $placeholders = [
            '{ten_hoc_vien}' => $hocVien->ho_ten ?? 'N/A',
            '{msnv}' => $hocVien->msnv ?? 'N/A',
            '{ma_khoa_hoc}' => $khoaHoc->ma_khoa_hoc ?? 'N/A',
            '{ten_chuong_trinh}' => $khoaHoc->chuongTrinh->ten_chuong_trinh ?? 'N/A',
        ];

        $tieuDe = $template->tieu_de ?? ($template->ten_mau ?? 'Không có tiêu đề');
        $noiDung = $template->noi_dung ?? '';
        foreach ($placeholders as $placeholder => $value) {
            $tieuDe = str_replace($placeholder, $value, $tieuDe);
            $noiDung = str_replace($placeholder, $value, $noiDung);
        }

        $success = true;
        $loiLog = null;
        try {
            Mail::mailer('dynamic')->to($hocVien->email, $hocVien->ho_ten)->send(new PlanNotificationMail($tieuDe, $noiDung));
        } catch (\Throwable $e) {
            $success = false;
            $loiLog = $e->getMessage();
            \Log::error("Lỗi gửi lại email tới {$hocVien->email}: " . $e->getMessage());
        }

        $trangThaiLog = $success ? 'success' : 'failed';
        $recipientEmail = $hocVien->email;

        // Lưu log
        EmailLog::create([
            'email_account_id' => $emailAccount->id,
            'recipient_email' => $recipientEmail,
            'subject' => $tieuDe ?? 'Không có tiêu đề',
            'content' => $noiDung ?? 'Không có nội dung',
            'status' => $trangThaiLog,
            'error_message' => $loiLog,
        ]);

        if ($success) {
            Notification::make()
                ->title("Gửi lại email cho {$hocVien->ho_ten} thành công!")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Gửi lại email cho {$hocVien->ho_ten} thất bại!")
                ->danger()
                ->send();
        }
    }
    // --- KẾT THÚC: Chức năng gửi lại email ---

    // --- BẮT ĐẦU: Chức năng xuất Excel ---
    public function xuatThongTinKhoaHoc()
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()
                ->title('Vui lòng chọn Khóa học trước khi xuất')
                ->danger()
                ->send();
            return;
        }
        return Excel::download(new ThongTinKhoaHocExport($this->selectedKhoaHoc), 'thong_tin_khoa_hoc.xlsx');
    }

    public function xuatDanhSachHocVien()
    {
        if (!$this->selectedKhoaHoc) {
            Notification::make()
                ->title('Vui lòng chọn Khóa học trước khi xuất')
                ->danger()
                ->send();
            return;
        }
        return Excel::download(new DanhSachHocVienExport($this->selectedKhoaHoc), 'danh_sach_hoc_vien.xlsx');
    }
    // --- KẾT THÚC: Chức năng xuất Excel ---

    private function refreshHocViens(): void
    {
        if ($this->selectedKhoaHoc) {
            $this->hocViensDaDangKy = DangKyModel::with(['hocVien.donVi', 'khoaHoc.chuongTrinh'])
                ->where('khoa_hoc_id', $this->selectedKhoaHoc)
                ->get();
        } else {
            $this->hocViensDaDangKy = collect();
        }
    }

    // --- Hàm lấy danh sách tuần duy nhất từ các khóa học đã lọc ---
    public function getDanhSachTuanProperty(): array
    {
        $query = KhoaHoc::query();

        if ($this->selectedTrangThaiKeHoach) {
            $query->where('trang_thai', $this->selectedTrangThaiKeHoach);
        }

        // Lấy các khóa học đã lọc
        $khoaHocs = $query->with('lichHocs')->get();

        // Lấy tất cả các tuần từ lịch học
        $tuanList = [];
        foreach ($khoaHocs as $kh) {
            foreach ($kh->lichHocs as $lich) {
                if ($lich->ngay_hoc) {
                    $tuan = date('W', strtotime($lich->ngay_hoc));
                    $nam = date('Y', strtotime($lich->ngay_hoc));
                    $key = "$nam-W$tuan";
                    $tuanList[$key] = "Tuần $tuan, Năm $nam";
                }
            }
        }

        // Sắp xếp tuần theo thứ tự giảm dần (tuần gần nhất trước)
        krsort($tuanList);

        return $tuanList;
    }

    // --- Hàm lấy danh sách khóa học đã lọc theo tuần và trạng thái ---
    public function getDanhSachKhoaHocLocProperty()
    {
        $query = KhoaHoc::query()->with('chuongTrinh', 'lichHocs');

        if ($this->selectedTrangThaiKeHoach) {
            $query->where('trang_thai', $this->selectedTrangThaiKeHoach);
        }

        // Nếu đã chọn tuần, lọc thêm theo tuần
        if ($this->selectedTuan) {
            // $this->selectedTuan có dạng "2023-W35"
            [$nam, $tuanStr] = explode('-W', $this->selectedTuan);
            $tuan = (int)$tuanStr;

            $query->whereHas('lichHocs', function ($q) use ($nam, $tuan) {
                $q->whereYear('ngay_hoc', $nam)
                  ->whereRaw('WEEK(ngay_hoc, 1) = ?', [$tuan]); // WEEK(date, 1) để tuần bắt đầu từ Thứ Hai
            });
        }

        return $query->get();
    }
    // --- Hết hàm lấy danh sách ---

    public function getHocVienFormSchema(): array
    {
        try {
            $form = HocVienResource::form(Form::make());
            return $form->getSchema();
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi lấy HocVienResource form schema: ' . $e->getMessage());
            return [];
        }
    }

    public static function getSlug(): string
    {
        return 'dang-ky-hoc-vien';
    }
}
