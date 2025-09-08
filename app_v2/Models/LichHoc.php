<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class LichHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'khoa_hoc_id','chuyen_de_id','giang_vien_id','ngay_hoc','gio_bat_dau','gio_ket_thuc','dia_diem','tuan','thang','nam'
    ];

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function chuyenDe()
    {
        return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->ngay_hoc) {
                $d = Carbon::parse($model->ngay_hoc);
                $model->tuan = (int) $d->isoWeek();
                $model->thang = (int) $d->month;
                $model->nam = (int) $d->year;
            }
        });
    }
}
