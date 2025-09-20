<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Các cột cho phép gán giá trị hàng loạt
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Các cột ẩn khi serialize
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Các cột cast
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Check if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Quan hệ: Một User có một Giảng viên
     */
    public function giangVien(): HasOne
    {
        return $this->hasOne(GiangVien::class);
    }

    /**
     * Logic khi xóa User:
     * - Không xóa Giảng viên
     * - Giữ lại bản ghi giang_viens nhưng set user_id = null
     * - Đặt tình trạng thành "Không tồn tại user"
     */
    protected static function booted()
    {
        static::deleting(function ($user) {
            if ($user->giangVien) {
                $user->giangVien->update([
                    'user_id' => null,
                    'tinh_trang' => 'Không tồn tại user',
                ]);
            }
        });
    }
}
