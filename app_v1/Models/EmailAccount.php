<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'smtp_host',
        'smtp_port',
        'username',
        'password',
        'is_active',
        'is_default',
    ];
}
