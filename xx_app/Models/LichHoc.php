<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichHoc extends Model
{
    protected $table = 'lich_hocs';

    protected $fillable = [
        'khoa_hoc_id',
        'chuyen_de_id',
        'giang_vien_id',
        'dia_diem_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'so_bai_kiem_tra',
        'so_gio_giang',
        'tuan',
        'thang',
        'nam',
    ];

    protected $casts = [
        'ngay_hoc'     => 'date',
        'so_gio_giang' => 'decimal:1', // 1 số lẻ (vd 3.5)
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function chuyenDe(): BelongsTo
    {
        return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id');
    }

    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function diaDiemDaoTao(): BelongsTo
    {
        return $this->belongsTo(DiaDiemDaoTao::class, 'dia_diem_id');
    }

    public function getStartDateTime(): ?Carbon
    {
        if (!$this->ngay_hoc) return null;
        $start = $this->gio_bat_dau ? substr($this->gio_bat_dau, 0, 5) : '00:00';
        return Carbon::parse($this->ngay_hoc->format('Y-m-d').' '.$start.':00');
    }

    public function getEndDateTime(): ?Carbon
    {
        if (!$this->ngay_hoc) return null;
        $end = $this->gio_ket_thuc ? substr($this->gio_ket_thuc, 0, 5) : '00:00';
        return Carbon::parse($this->ngay_hoc->format('Y-m-d').' '.$end.':00');
    }
}
