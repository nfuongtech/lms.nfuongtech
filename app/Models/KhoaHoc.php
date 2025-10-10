<?php

namespace App\Models;

use App\Enums\TrangThaiKhoaHoc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'khoa_hocs';

    protected $fillable = [
        'ma_khoa_hoc',
        'ten_khoa_hoc',
        'chuong_trinh_id',
        'nam',
        'yeu_cau_phan_tram_gio',
        'yeu_cau_diem_tb',
    ];

    protected $appends = [
        'trang_thai_hien_thi',
    ];

    protected $casts = [
        // Bảo đảm hiển thị đúng định dạng
        'yeu_cau_phan_tram_gio' => 'integer',
        'yeu_cau_diem_tb'       => 'decimal:1',
        'tam_hoan'              => 'boolean',
    ];

    public function chuongTrinh()
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    // Dự phòng: nếu 'ten_khoa_hoc' trống thì lấy theo chương trình
    public function getTenKhoaHocAttribute(): string
    {
        $val = $this->attributes['ten_khoa_hoc'] ?? null;
        if ($val !== null && $val !== '') return (string) $val;
        return (string) ($this->chuongTrinh->ten_chuong_trinh ?? '');
    }

    public function getTrangThaiHienThiAttribute(): string
    {
        $rawStatus = $this->attributes['trang_thai'] ?? null;

        if ($this->attributes['tam_hoan'] ?? false) {
            return 'Tạm hoãn';
        }

        if ($rawStatus instanceof TrangThaiKhoaHoc) {
            $rawStatus = $rawStatus->value;
        }

        return match ($rawStatus) {
            TrangThaiKhoaHoc::BAN_HANH->value, 'Ban hành' => 'Ban hành',
            TrangThaiKhoaHoc::DANG_DAO_TAO->value, 'Đang đào tạo' => 'Đang đào tạo',
            TrangThaiKhoaHoc::TAM_HOAN->value, 'Tạm hoãn' => 'Tạm hoãn',
            TrangThaiKhoaHoc::KET_THUC->value, 'Kết thúc' => 'Kết thúc',
            TrangThaiKhoaHoc::CHINH_SUA_KE_HOACH->value, 'Chỉnh sửa kế hoạch' => 'Dự thảo',
            TrangThaiKhoaHoc::KE_HOACH->value, 'Kế hoạch', 'Soạn thảo', null, '' => 'Dự thảo',
            default => is_string($rawStatus) && trim($rawStatus) !== '' ? $rawStatus : 'Dự thảo',
        };
    }

    public static function trangThaiHienThiOptions(): array
    {
        return [
            'du-thao'       => 'Dự thảo',
            'ban-hanh'      => 'Ban hành',
            'dang-dao-tao'  => 'Đang đào tạo',
            'ket-thuc'      => 'Kết thúc',
            'tam-hoan'      => 'Tạm hoãn',
        ];
    }

    public function scopeWhereTrangThaiHienThi(Builder $query, array $states): Builder
    {
        $normalized = collect($states)
            ->flatten()
            ->map(fn ($value) => Str::slug((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($normalized) {
            if ($normalized->contains('tam-hoan')) {
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->where('tam_hoan', true)
                            ->orWhere('tam_hoan', 1)
                            ->orWhere('tam_hoan', '1');
                    })->orWhereIn('trang_thai', ['tam_hoan', 'Tạm hoãn', 'tam hoan']);
                });
            }

            if ($normalized->contains('ban-hanh')) {
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->whereIn('trang_thai', ['ban_hanh', 'Ban hành']);
                });
            }

            if ($normalized->contains('dang-dao-tao')) {
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->whereIn('trang_thai', ['dang_dao_tao', 'Đang đào tạo']);
                });
            }

            if ($normalized->contains('ket-thuc')) {
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->whereIn('trang_thai', ['ket_thuc', 'Kết thúc']);
                });
            }

            if ($normalized->contains('du-thao')) {
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->where(function (Builder $inner) {
                        $inner->whereNull('trang_thai')
                            ->orWhere('trang_thai', '')
                            ->orWhereIn('trang_thai', [
                                TrangThaiKhoaHoc::KE_HOACH->value,
                                'Kế hoạch',
                                TrangThaiKhoaHoc::CHINH_SUA_KE_HOACH->value,
                                'Chỉnh sửa kế hoạch',
                                'Dự thảo',
                                'Soạn thảo',
                            ]);
                    });
                });
            }
        });
    }
}
