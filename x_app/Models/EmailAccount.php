<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailAccount extends Model
{
    protected $fillable = [
        'name',
        'email',
        'host',
        'port',
        'username',
        'password',
        'encryption_tls',
        'active',
        'is_default',
    ];

    protected $casts = [
        'encryption_tls' => 'boolean',
        'active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $hidden = ['password'];

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    public function getPasswordAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public static function boot()
    {
        parent::boot();

        // Nếu một account được chọn làm mặc định, bỏ mặc định các account khác
        static::saving(function ($model) {
            if ($model->is_default) {
                self::where('id', '!=', $model->id)->update(['is_default' => false]);
            }
        });
    }

    // Lấy email mặc định
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->first();
    }
}
