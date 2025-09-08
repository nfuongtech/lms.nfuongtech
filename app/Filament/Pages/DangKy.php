<?php

namespace App\Filament\Pages;

use App\Models\HocVien;
use App\Models\KhoaHoc;
use App\Models\DangKy as DangKyModel;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class DangKy extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Đăng ký';
    protected static string $view = 'filament.pages.dang-ky';

    public $selectedKhoaHoc = null;
    public $filterTimeType = 'thang';
    public $filterTrangThai = null;

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
    ];

    public function mount(): void
    {
        $this->refreshHocViens();
    }

    public function updatedSelectedKhoaHoc(): void
    {
        $this->refreshHocViens();
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
            'newHocVien.email' => 'nullable|email',
        ], [
            'newHocVien.msnv.unique' => 'MSNV đã tồn tại',
            'newHocVien.msnv.required' => 'Vui lòng nhập MSNV',
            'newHocVien.ho_ten.required' => 'Vui lòng nhập họ tên',
            'newHocVien.email.email' => 'Email không hợp lệ',
        ]);

        $hocVien = HocVien::create([
            'msnv' => $this->newHocVien['msnv'],
            'ho_ten' => $this->newHocVien['ho_ten'],
            'email' => $this->newHocVien['email'],
            'chuc_vu' => $this->newHocVien['chuc_vu'],
            'don_vi_id' => $this->newHocVien['don_vi_id'],
            'tinh_trang' => 'Đang làm việc',
        ]);

        // Thêm học viên mới vào danh sách đăng ký
        $this->parsedHocViens[] = [
            'id' => $hocVien->id,
            'msnv' => $hocVien->msnv,
            'ho_ten' => $hocVien->ho_ten,
            'chuc_vu' => $hocVien->chuc_vu,
            'don_vi' => $hocVien->donVi->ten_hien_thi ?? '',
            'display' => "{$hocVien->msnv} - {$hocVien->ho_ten}, {$hocVien->donVi->ten_hien_thi}",
        ];

        // Xóa khỏi danh sách không tìm thấy
        $this->parsedMsnvNotFound = array_diff($this->parsedMsnvNotFound, [$this->newHocVien['msnv']]);

        // Đóng modal
        $this->showAddHocVienModal = false;

        // Reset form
        $this->newHocVien = [
            'msnv' => '',
            'ho_ten' => '',
            'email' => '',
            'chuc_vu' => '',
            'don_vi_id' => null,
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

        foreach ($this->parsedHocViens as $hv) {
            $exists = DangKyModel::where('hoc_vien_id', $hv['id'])
                ->where('khoa_hoc_id', $this->selectedKhoaHoc)
                ->exists();

            if (!$exists) {
                DangKyModel::create([
                    'hoc_vien_id' => $hv['id'],
                    'khoa_hoc_id' => $this->selectedKhoaHoc,
                ]);
            }
        }

        $this->refreshHocViens();
        $this->msnvInput = '';
        $this->parsedHocViens = [];

        Notification::make()
            ->title('Ghi danh thành công')
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

    public static function getSlug(): string
    {
        return 'dang-ky'; // URL sẽ là /admin/dang-ky
    }
}
