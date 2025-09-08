<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
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
            // Tách dữ liệu của ChuyenDe ra khỏi data chính
            $chuyenDeData = $data['chuyenDes'] ?? [];
            $giangVienData = Arr::except($data, ['email', 'chuyenDes']);

            // 1. Tìm hoặc tạo mới tài khoản người dùng
            $user = User::firstOrCreate(
                ['email' => $data['email']], // Điều kiện tìm kiếm
                [
                    'name' => $data['ho_ten'],
                    'password' => Hash::make(Str::random(12)), // Tạo mật khẩu ngẫu nhiên an toàn
                ]
            );

            // 2. Gán user_id vào dữ liệu của Giảng viên
            $giangVienData['user_id'] = $user->id;

            // 3. Tạo bản ghi Giảng viên
            $giangVien = static::getModel()::create($giangVienData);

            // 4. Liên kết các chuyên đề (nếu có)
            if (!empty($chuyenDeData)) {
                $giangVien->chuyenDes()->sync($chuyenDeData);
            }

            return $giangVien;
        });
    }
}
