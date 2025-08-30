<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetQuaKhoaHoc extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dang_ky_id',
        'diem',
        'ket_qua',
        'can_hoc_lai',
        'hoc_phi',
    ];

    /**
     * Get the dangKy that the KetQuaKhoaHoc belongs to.
     */
    public function dangKy(): BelongsTo
    {
        return $this->belongsTo(DangKy::class);
    }
}
