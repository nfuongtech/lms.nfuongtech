<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
use App\Models\GiangVien;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateGiangVien extends CreateRecord
{
    protected static string $resource = GiangVienResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $giangVienData = Arr::only($data, [
                'ma_so', 'ho_ten', 'hinh_anh_path', 'gioi_tinh',
                'nam_sinh', 'email', 'dien_thoai', 'ho_khau_noi_lam_viec',
                'don_vi', 'trinh_do', 'chuyen_mon', 'so_nam_kinh_nghiem',
                'tom_tat_kinh_nghiem', 'tinh_trang',
            ]);

            $email = trim($data['email'] ?? '');

            if (in_array($giangVienData['tinh_trang'], ['Đang làm việc', 'Đang giảng dạy']) && empty($email)) {
                throw new \Exception('Trạng thái này yêu cầu phải có Email để tạo User đăng nhập.');
            }

            $userId = null;
            if (!empty($email)) {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $giangVienData['ho_ten'] ?? $giangVienData['ma_so'],
                        'password' => Hash::make(Str::random(12)),
                    ]
                );
                $userId = $user->id;
            }

            $giangVienData['user_id'] = $userId;

            return GiangVien::create($giangVienData);
        });
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Đã tạo Giảng viên thành công!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
