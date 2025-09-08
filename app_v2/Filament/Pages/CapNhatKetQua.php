<?php

namespace App\Filament\Pages;

use App\Models\HocVien;
use App\Models\DangKy;
use App\Models\KhoaHoc;
use App\Models\ChuyenDe;
use App\Models\KetQuaKhoaHoc;
use App\Models\KetQuaChuyenDe;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CapNhatKetQua extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.pages.cap-nhat-ket-qua';
    protected static ?string $title = 'Cập nhật kết quả học tập';

    public $hoc_vien_id;
    public $khoa_hoc_id;
    public $chuyen_de_data = [];
    public $diem_tong_ket;
    public $ket_qua;
    public $hoc_phi;
    public $vang;
    public $ly_do_vang;

    public function mount(): void
    {
        $this->chuyen_de_data = [];
    }

    public function updatedKhoaHocId($value): void
    {
        if ($this->hoc_vien_id && $value) {
            $this->loadData();
        }
    }

    private function loadData(): void
    {
        $dangKy = DangKy::where('hoc_vien_id', $this->hoc_vien_id)
            ->where('khoa_hoc_id', $this->khoa_hoc_id)
            ->first();

        if (!$dangKy) {
            $this->resetKetQua();
            return;
        }

        // Lấy kết quả khóa học
        $ketQuaKhoaHoc = KetQuaKhoaHoc::where('dang_ky_id', $dangKy->id)->first();
        $this->diem_tong_ket = $ketQuaKhoaHoc->diem ?? null;
        $this->ket_qua = $ketQuaKhoaHoc->ket_qua ?? null;
        $this->hoc_phi = $ketQuaKhoaHoc->hoc_phi ?? null;

        // Lấy chuyên đề
        $chuyenDes = ChuyenDe::where('khoa_hoc_id', $this->khoa_hoc_id)->get();
        $this->chuyen_de_data = [];

        foreach ($chuyenDes as $chuyenDe) {
            $ketQuaChuyenDe = KetQuaChuyenDe::where('ket_qua_khoa_hoc_id', $ketQuaKhoaHoc->id ?? 0)
                ->where('chuyen_de_id', $chuyenDe->id)
                ->first();

            $this->chuyen_de_data[] = [
                'id' => $chuyenDe->id,
                'ten_chuyen_de' => $chuyenDe->ten_chuyen_de,
                'diem' => $ketQuaChuyenDe->diem ?? null,
                'ket_qua' => $ketQuaChuyenDe->ket_qua ?? null,
            ];
        }
    }

    private function resetKetQua(): void
    {
        $this->diem_tong_ket = null;
        $this->ket_qua = null;
        $this->hoc_phi = null;
        $this->vang = null;
        $this->ly_do_vang = null;
        $this->chuyen_de_data = [];
    }

    public function save(): void
    {
        DB::transaction(function () {
            $dangKy = DangKy::where('hoc_vien_id', $this->hoc_vien_id)
                ->where('khoa_hoc_id', $this->khoa_hoc_id)
                ->firstOrFail();

            // Cập nhật hoặc tạo kết quả khóa học
            $ketQuaKhoaHoc = KetQuaKhoaHoc::updateOrCreate(
                ['dang_ky_id' => $dangKy->id],
                [
                    'diem' => $this->diem_tong_ket,
                    'ket_qua' => $this->ket_qua,
                    'hoc_phi' => $this->hoc_phi,
                    'can_hoc_lai' => in_array($this->ket_qua, ['Không hoàn thành', 'Không đạt yêu cầu']),
                ]
            );

            // Cập nhật kết quả chuyên đề
            foreach ($this->chuyen_de_data as $item) {
                if (!isset($item['id'])) {
                    continue;
                }

                KetQuaChuyenDe::updateOrCreate(
                    [
                        'ket_qua_khoa_hoc_id' => $ketQuaKhoaHoc->id,
                        'chuyen_de_id' => $item['id'],
                    ],
                    [
                        'diem' => $item['diem'] ?? null,
                        'ket_qua' => $item['ket_qua'] ?? null,
                    ]
                );
            }
        });

        Notification::make()
            ->title('Cập nhật kết quả thành công!')
            ->success()
            ->send();
    }

    public function getHocViensProperty()
    {
        return HocVien::all()->pluck('ten', 'id')->toArray();
    }

    public function getKhoaHocsProperty()
    {
        return KhoaHoc::all()->pluck('ten', 'id')->toArray();
    }
}
