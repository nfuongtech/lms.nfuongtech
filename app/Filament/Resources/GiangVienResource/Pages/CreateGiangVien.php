<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateGiangVien extends CreateRecord
{
    protected static string $resource = GiangVienResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Tách dữ liệu của User và Giảng viên ra
        $userData = Arr::only($data, ['email', 'password']);
        $giangVienData = Arr::except($data, ['email', 'password', 'role_id']);

        // Tạo tài khoản người dùng trước
        $user = User::create([
            'name' => $data['ho_ten'],
            'email' => $userData['email'],
            'password' => $userData['password'], // Mật khẩu đã được hash ở form
        ]);

        // Gán vai trò cho người dùng
        $user->roles()->sync($data['role_id']);

        // Gán user_id vào dữ liệu để tạo Giảng viên
        $giangVienData['user_id'] = $user->id;

        // Tạo bản ghi Giảng viên
        $giangVien = static::getModel()::create($giangVienData);

        // Gán chuyên đề dạy (mối quan hệ nhiều-nhiều)
        if (!empty($data['chuyenDes'])) {
            $giangVien->chuyenDes()->sync($data['chuyenDes']);
        }

        return $giangVien;
    }
}
