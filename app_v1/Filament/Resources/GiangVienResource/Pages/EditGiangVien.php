<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditGiangVien extends EditRecord
{
    protected static string $resource = GiangVienResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Nạp dữ liệu của user và roles vào form
        $user = $this->record->user;
        if ($user) {
            $data['email'] = $user->email;
            $data['roles'] = $user->roles->pluck('id')->toArray();
            $data['user_id'] = $user->id;
        }
    
        // Nạp dữ liệu chuyên đề
        $data['chuyenDes'] = $this->record->chuyenDes->pluck('id')->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $roleData = $data['roles'] ?? [];
            $chuyenDeData = $data['chuyenDes'] ?? [];
            $giangVienData = Arr::except($data, ['email', 'password', 'roles', 'chuyenDes']);
            
            // Cập nhật thông tin Giảng viên
            $record->update($giangVienData);

            // Cập nhật thông tin User
            if ($record->user) {
                $userDataToUpdate = ['name' => $data['ho_ten'], 'email' => $data['email']];
                if (!empty($data['password'])) {
                    $userDataToUpdate['password'] = Hash::make($data['password']);
                }
                $record->user->update($userDataToUpdate);
                $record->user->roles()->sync($roleData);
            }
            
            // Cập nhật chuyên đề
            $record->chuyenDes()->sync($chuyenDeData);

            return $record;
        });
    }
}
