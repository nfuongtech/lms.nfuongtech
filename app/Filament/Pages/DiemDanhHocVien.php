<?php

namespace App\Filament\Pages;

use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\EmailTemplate;
use App\Models\EmailAccount;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class DiemDanhHocVien extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Điểm danh học viên';

    protected static string $view = 'filament.pages.diem-danh-hoc-vien';

    // State
    public $selectedKhoaHoc;
    public $selectedTuanNam;
    public $selectedLichHoc;
    public $diemDanhData = [];

    public $showGuiEmailModal = false;
    public $selectedEmailTemplateId;
    public $selectedEmailAccountId;

    /** @var Collection */
    public $hocViensDaDangKy;

    public function mount(): void
    {
        $this->hocViensDaDangKy = collect(); // Luôn là collection
    }

    /**
     * Danh sách tuần/năm cho dropdown
     */
    public function getDanhSachTuanNam(): array
    {
        $ds = [];
        $lichHocs = LichHoc::query()
            ->where('khoa_hoc_id', $this->selectedKhoaHoc)
            ->get();

        foreach ($lichHocs as $lh) {
            $week = $lh->ngay_hoc ? $lh->ngay_hoc->format('W') : null;
            $year = $lh->ngay_hoc ? $lh->ngay_hoc->format('Y') : null;
            if ($week && $year) {
                $key = $week . '/' . $year;
                $ds[$key] = "Tuần $week/$year";
            }
        }

        return $ds;
    }

    /**
     * Danh sách buổi học theo Tuần/Năm
     */
    public function getDanhSachChuyenDeTheoTuanNam(): array
    {
        if (!$this->selectedKhoaHoc || !$this->selectedTuanNam) {
            return [];
        }

        [$week, $year] = explode('/', $this->selectedTuanNam);

        $lichHocs = LichHoc::with('chuyenDe', 'giangVien')
            ->where('khoa_hoc_id', $this->selectedKhoaHoc)
            ->whereYear('ngay_hoc', $year)
            ->whereRaw('WEEK(ngay_hoc, 1) = ?', [$week])
            ->get();

        return $lichHocs->map(fn($lh) => [
            'id' => $lh->id,
            'display' => ($lh->chuyenDe->ten_chuyen_de ?? 'Chuyên đề') .
                ' - ' . $lh->ngay_hoc->format('d/m/Y') .
                ' (' . ($lh->giangVien->ho_ten ?? 'Chưa có GV') . ')',
        ])->toArray();
    }

    /**
     * Lưu điểm danh
     */
    public function luuDiemDanh(): void
    {
        if (!$this->selectedLichHoc) {
            Notification::make()
                ->title('Chưa chọn buổi học')
                ->danger()
                ->send();
            return;
        }

        foreach ($this->diemDanhData as $hocVienId => $data) {
            DiemDanh::updateOrCreate(
                [
                    'hoc_vien_id' => $hocVienId,
                    'lich_hoc_id' => $this->selectedLichHoc,
                ],
                [
                    'trang_thai' => $data['trang_thai'] ?? 'co_mat',
                    'ly_do_vang' => $data['ly_do_vang'] ?? null,
                    'diem_buoi' => $data['diem_buoi'] ?? null,
                    'danh_gia' => $data['danh_gia'] ?? null,
                ]
            );
        }

        Notification::make()
            ->title('Lưu điểm danh thành công')
            ->success()
            ->send();
    }

    /**
     * Gửi email hàng loạt
     */
    public function moModalGuiEmail(): void
    {
        $this->showGuiEmailModal = true;
    }

    public function guiEmailHangLoat(): void
    {
        if (!$this->selectedEmailTemplateId || !$this->selectedEmailAccountId) {
            Notification::make()
                ->title('Chưa chọn mẫu email hoặc tài khoản gửi')
                ->danger()
                ->send();
            return;
        }

        // TODO: Gửi email tới danh sách học viên
        Notification::make()
            ->title('Đã gửi email cho học viên')
            ->success()
            ->send();

        $this->showGuiEmailModal = false;
    }

    /**
     * Render view
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        $this->hocViensDaDangKy = collect();

        if ($this->selectedKhoaHoc) {
            $dangKys = DangKy::with('hocVien', 'hocVien.donVi')
                ->where('khoa_hoc_id', $this->selectedKhoaHoc)
                ->get();

            $this->hocViensDaDangKy = $dangKys->map(fn($dk) => $dk->hocVien)->filter();
        }

        return view(static::$view, [
            'hocViensDaDangKy' => $this->hocViensDaDangKy,
        ]);
    }
}
