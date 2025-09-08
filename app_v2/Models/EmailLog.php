<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $table = 'email_logs';

    protected $fillable = [
        'khoa_hoc_id',
        'email_account_id',
        'recipient_email',
        'subject',
        'content',
        'status',
        'error_message',
    ];
}
