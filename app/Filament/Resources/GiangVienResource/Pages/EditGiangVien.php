<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditGiangVien extends EditRecord
{
    protected static string $resource = GiangVienResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;
        if ($user) {
            $data['email'] = $user->email;
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $giangVienData = Arr::only($data, [
                'ma_so', 'ho_ten', 'hinh_anh_path', 'gioi_tinh',
                'nam_sinh', 'email', 'dien_thoai', 'ho_khau_noi_lam_viec',
                'don_vi', 'trinh_do', 'chuyen_mon', 'so_nam_kinh_nghiem',
                'tom_tat_kinh_nghiem', 'tinh_trang',
            ]);

            $newEmail = trim($data['email'] ?? '');
            $newTinhTrang = $data['tinh_trang'] ?? $record->tinh_trang;

            if (in_array($newTinhTrang, ['Đang làm việc', 'Đang giảng dạy'])) {
                if (empty($newEmail)) {
                    throw new \Exception('Khi đặt trạng thái này, bắt buộc phải có Email để tạo User.');
                }

                if (!$record->user) {
                    $user = User::firstOrCreate(
                        ['email' => $newEmail],
                        [
                            'name' => $giangVienData['ho_ten'] ?? $giangVienData['ma_so'],
                            'password' => Hash::make(Str::random(12)),
                        ]
                    );
                    $giangVienData['user_id'] = $user->id;
                } else {
                    if ($record->user->email !== $newEmail) {
                        $exists = User::where('email', $newEmail)->where('id', '!=', $record->user->id)->exists();
                        if ($exists) {
                            throw new \Exception('Email này đã tồn tại ở User khác.');
                        }
                        $record->user->update([
                            'email' => $newEmail,
                            'name'  => $giangVienData['ho_ten'] ?? $record->user->name,
                        ]);
                    } else {
                        $record->user->update(['name' => $giangVienData['ho_ten'] ?? $record->user->name]);
                    }
                }
            }

            $record->update($giangVienData);
            return $record;
        });
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cập nhật Giảng viên thành công!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
