<?php

namespace App\Filament\Pages;

use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\DangKy;
use App\Models\DiemDanhBuoiHoc;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class DiemDanhHocVien extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Điểm danh học viên';
    protected static string $view = 'filament.pages.diem-danh-hoc-vien';

    public $selectedKhoaHoc = null;
    public $selectedLichHoc = null;
    public $hocViens = [];
    public $diemDanhData = [];

    public function mount(): void
    {
        $this->loadHocViens();
    }

    public function updatedSelectedKhoaHoc(): void
    {
        $this->selectedLichHoc = null;
        $this->loadHocViens();
    }

    public function updatedSelectedLichHoc(): void
    {
        $this->loadHocViens();
    }

    private function loadHocViens(): void
    {
        $this->hocViens = [];
        $this->diemDanhData = [];

        if (!$this->selectedKhoaHoc || !$this->selectedLichHoc) {
            return;
        }

        // Lấy danh sách học viên đã đăng ký khóa học
        $dangKies = DangKy::with('hocVien')
            ->where('khoa_hoc_id', $this->selectedKhoaHoc)
            ->get();

        foreach ($dangKies as $dk) {
            $hocVien = $dk->hocVien;
            $diemDanh = DiemDanhBuoiHoc::where('dang_ky_id', $dk->id)
                ->where('lich_hoc_id', $this->selectedLichHoc)
                ->first();

            $this->hocViens[] = $hocVien;
            $this->diemDanhData[$dk->id] = [
                'trang_thai' => $diemDanh->trang_thai ?? 'co_mat',
                'ly_do_vang' => $diemDanh->ly_do_vang ?? '',
                'diem_buoi_hoc' => $diemDanh->diem_buoi_hoc ?? '',
            ];
        }
    }

    public function luuDiemDanh(): void
    {
        if (!$this->selectedLichHoc) {
            Notification::make()
                ->title('Vui lòng chọn buổi học')
                ->danger()
                ->send();
            return;
        }

        foreach ($this->diemDanhData as $dangKyId => $data) {
            DiemDanhBuoiHoc::updateOrCreate(
                [
                    'dang_ky_id' => $dangKyId,
                    'lich_hoc_id' => $this->selectedLichHoc,
                ],
                $data
            );
        }

        // Tự động tính lại kết quả khóa học
        $this->tinhToanKetQuaKhoaHoc();

        Notification::make()
            ->title('Lưu điểm danh thành công')
            ->success()
            ->send();
    }

    private function tinhToanKetQuaKhoaHoc(): void
    {
        // Lấy tất cả đăng ký của khóa học
        $dangKies = DangKy::where('khoa_hoc_id', $this->selectedKhoaHoc)->get();

        foreach ($dangKies as $dk) {
            // Lấy tất cả điểm danh của học viên này trong khóa học
            $diemDanhs = DiemDanhBuoiHoc::where('dang_ky_id', $dk->id)->get();

            $tongDiem = 0;
            $soBuoiCoDiem = 0;
            $soBuoiVang = 0;
            $tongSoBuoi = $diemDanhs->count();

            foreach ($diemDanhs as $dd) {
                if ($dd->diem_buoi_hoc !== null) {
                    $tongDiem += $dd->diem_buoi_hoc;
                    $soBuoiCoDiem++;
                }
                if (in_array($dd->trang_thai, ['vang_phep', 'vang_khong_phep'])) {
                    $soBuoiVang++;
                }
            }

            $diemTongKhoa = $soBuoiCoDiem > 0 ? round($tongDiem / $soBuoiCoDiem, 2) : null;
            $tyLeVang = $tongSoBuoi > 0 ? ($soBuoiVang / $tongSoBuoi) * 100 : 0;

            // Xác định kết quả
            $ketQua = null;
            $trangThaiHocVien = null;
            if ($tyLeVang <= 20 && ($diemTongKhoa === null || $diemTongKhoa >= 5)) {
                $ketQua = 'hoan_thanh';
                $trangThaiHocVien = 'hoan_thanh';
            } else {
                $ketQua = 'khong_hoan_thanh';
                $trangThaiHocVien = 'khong_hoan_thanh';
            }

            // Cập nhật hoặc tạo mới kết quả
            \App\Models\KetQuaKhoaHoc::updateOrCreate(
                ['dang_ky_id' => $dk->id],
                [
                    'diem_tong_khoa' => $diemTongKhoa,
                    'ket_qua' => $ketQua,
                    'trang_thai_hoc_vien' => $trangThaiHocVien,
                ]
            );
        }
    }

    public static function getSlug(): string
    {
        return 'diem-danh-hoc-vien';
    }
}
