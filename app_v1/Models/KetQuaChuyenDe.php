<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KetQuaChuyenDe extends Model
{
    use HasFactory;

    protected $table = 'ket_qua_chuyen_des';

    protected $fillable = [
        'ket_qua_khoa_hoc_id',
        'chuyen_de_id',
        'ten_chuyen_de',
        'lich_hoc_id',
        'diem',
        'trang_thai',
        'ly_do_vang',
    ];

    protected $casts = [
        'diem' => 'decimal:2',
    ];

    public function ketQuaKhoaHoc()
    {
        return $this->belongsTo(KetQuaKhoaHoc::class, 'ket_qua_khoa_hoc_id');
    }

    public function chuyenDe()
    {
        return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id');
    }

    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }
}
