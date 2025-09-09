<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'email_account_id',
        'recipient_email', // Tên cột đúng theo laravel_db.pdf
        'subject',
        'content',
        'status',
        'error_message',
        // 'khoa_hoc_id', // Nếu bạn muốn lưu thêm khóa học liên quan
    ];

    public function emailAccount()
    {
        return $this->belongsTo(EmailAccount::class);
    }
}
