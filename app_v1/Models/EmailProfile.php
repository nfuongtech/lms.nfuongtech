<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailProfile extends Model
{
    protected $fillable = [
        'ten_hien_thi','from_email','reply_to','host','port',
        'encryption','username','password','mac_dinh',
    ];

    public function scopeMacDinh($q)
    {
        return $q->where('mac_dinh', true);
    }
}
