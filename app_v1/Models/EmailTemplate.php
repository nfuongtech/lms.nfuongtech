<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'ten_mau','su_kien','doi_tuong','subject','body_markdown','kich_hoat',
    ];

    public static function for(string $suKien, string $doiTuong = 'both'): ?self
    {
        return static::query()
            ->where('su_kien', $suKien)
            ->whereIn('doi_tuong', [$doiTuong, 'both'])
            ->where('kich_hoat', true)
            ->orderByRaw("FIELD(doi_tuong, ?, 'both')", [$doiTuong])
            ->first();
    }
}
