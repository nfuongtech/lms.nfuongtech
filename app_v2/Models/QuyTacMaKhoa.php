<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuyTacMaKhoa extends Model
{
    use HasFactory;

    protected $fillable = ['loai_hinh_dao_tao', 'tien_to'];
}
