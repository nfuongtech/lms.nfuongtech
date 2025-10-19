<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'ten_mau',
        'loai_thong_bao',
        'tieu_de',
        'noi_dung',
    ];
}
