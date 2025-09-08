<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'email_profile_id','khoa_hoc_id','su_kien','doi_tuong',
        'to_email','status','error_message','sent_at',
    ];

    public function profile(): BelongsTo { return $this->belongsTo(EmailProfile::class, 'email_profile_id'); }
    public function khoaHoc(): BelongsTo { return $this->belongsTo(\App\Models\KhoaHoc::class, 'khoa_hoc_id'); }
}
