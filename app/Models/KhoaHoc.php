<?php

namespace App\Models;

use App\Enums\TrangThaiKhoaHoc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
        'da_chuyen_ket_qua',
        'thoi_gian_chuyen_ket_qua',
        'nguoi_chuyen_ket_qua',
    ];

    protected $appends = [
        'trang_thai_hien_thi',
    ];

    protected $casts = [
        // Bảo đảm hiển thị đúng định dạng
        'yeu_cau_phan_tram_gio'   => 'integer',
        'yeu_cau_diem_tb'         => 'decimal:1',
        'tam_hoan'                => 'boolean',
        'da_chuyen_ket_qua'       => 'boolean',
        'thoi_gian_chuyen_ket_qua' => 'datetime',
    ];

    protected ?array $scheduleBoundsCache = null;

    public function chuongTrinh()
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    public function setRelation($relation, $value)
    {
        if ($relation === 'lichHocs') {
            $this->scheduleBoundsCache = null;
        }

        return parent::setRelation($relation, $value);
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

        if (is_string($rawStatus) && Str::slug($rawStatus) === 'tam-hoan') {
            return 'Tạm hoãn';
        }

        return $this->calculateScheduleStatus();
    }

    public function calculateScheduleStatus(): string
    {
        [$start, $end] = $this->resolveScheduleBounds();

        if (!$start || !$end) {
            return 'Dự thảo';
        }

        $now = now();

        if ($now->lt($start)) {
            return 'Ban hành';
        }

        if ($now->gt($end)) {
            return 'Kết thúc';
        }

        return 'Đang đào tạo';
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

        $now = now();

        return $query->where(function (Builder $builder) use ($normalized, $now) {
            if ($normalized->contains('tam-hoan')) {
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->where('tam_hoan', true)
                            ->orWhere('tam_hoan', 1)
                            ->orWhere('tam_hoan', '1');
                    })->orWhereRaw('LOWER(COALESCE(trang_thai, "")) IN (?, ?, ?)', ['tam_hoan', 'tạm hoãn', 'tam hoan']);
                });
            }

            if ($normalized->contains('du-thao')) {
                $builder->orWhere(function (Builder $sub) {
                    $sub->whereDoesntHave('lichHocs')
                        ->where(function (Builder $inner) {
                            $inner->whereNull('tam_hoan')
                                ->orWhere('tam_hoan', false)
                                ->orWhere('tam_hoan', 0)
                                ->orWhere('tam_hoan', '0');
                        })
                        ->whereRaw('LOWER(COALESCE(trang_thai, "")) NOT IN (?, ?, ?)', ['tam_hoan', 'tạm hoãn', 'tam hoan']);
                });
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->whereRaw('LOWER(COALESCE(trang_thai, "")) IN (?, ?, ?, ?, ?)', [
                        'du_thao',
                        'dự thảo',
                        'ke hoach',
                        'kế hoạch',
                        'soan thao',
                    ]);
                });
            }

            if ($normalized->contains('ban-hanh')) {
                $builder->orWhere(function (Builder $sub) use ($now) {
                    $sub->whereHas('lichHocs')
                        ->whereDoesntHave('lichHocs', fn ($q) => $q
                            ->whereRaw('TIMESTAMP(ngay_hoc, COALESCE(gio_bat_dau, "00:00:00")) <= ?', [$now]))
                        ->where(function (Builder $inner) {
                            $inner->whereNull('tam_hoan')
                                ->orWhere('tam_hoan', false)
                                ->orWhere('tam_hoan', 0)
                                ->orWhere('tam_hoan', '0');
                        })
                        ->whereRaw('LOWER(COALESCE(trang_thai, "")) NOT IN (?, ?, ?)', ['tam_hoan', 'tạm hoãn', 'tam hoan']);
                });
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->whereRaw('LOWER(COALESCE(trang_thai, "")) IN (?, ?, ?)', [
                        'ban_hanh',
                        'ban hanh',
                        'ban hành',
                    ]);
                });
            }

            if ($normalized->contains('dang-dao-tao')) {
                $builder->orWhere(function (Builder $sub) use ($now) {
                    $sub->whereHas('lichHocs', fn ($q) => $q
                        ->whereRaw('TIMESTAMP(ngay_hoc, COALESCE(gio_bat_dau, "00:00:00")) <= ?', [$now])
                        ->whereRaw('TIMESTAMP(ngay_hoc, COALESCE(gio_ket_thuc, "23:59:59")) >= ?', [$now]))
                        ->where(function (Builder $inner) {
                            $inner->whereNull('tam_hoan')
                                ->orWhere('tam_hoan', false)
                                ->orWhere('tam_hoan', 0)
                                ->orWhere('tam_hoan', '0');
                        })
                        ->whereRaw('LOWER(COALESCE(trang_thai, "")) NOT IN (?, ?, ?)', ['tam_hoan', 'tạm hoãn', 'tam hoan']);
                });
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->whereRaw('LOWER(COALESCE(trang_thai, "")) IN (?, ?, ?)', [
                        'dang_dao_tao',
                        'dang dao tao',
                        'đang đào tạo',
                    ]);
                });
            }

            if ($normalized->contains('ket-thuc')) {
                $builder->orWhere(function (Builder $sub) use ($now) {
                    $sub->whereHas('lichHocs')
                        ->whereDoesntHave('lichHocs', fn ($q) => $q
                            ->whereRaw('TIMESTAMP(ngay_hoc, COALESCE(gio_ket_thuc, "23:59:59")) >= ?', [$now]))
                        ->where(function (Builder $inner) {
                            $inner->whereNull('tam_hoan')
                                ->orWhere('tam_hoan', false)
                                ->orWhere('tam_hoan', 0)
                                ->orWhere('tam_hoan', '0');
                        })
                        ->whereRaw('LOWER(COALESCE(trang_thai, "")) NOT IN (?, ?, ?)', ['tam_hoan', 'tạm hoãn', 'tam hoan']);
                });
                $builder->orWhere(function (Builder $sub) {
                    $sub->where(function (Builder $inner) {
                        $inner->whereNull('tam_hoan')
                            ->orWhere('tam_hoan', false)
                            ->orWhere('tam_hoan', 0)
                            ->orWhere('tam_hoan', '0');
                    })->whereRaw('LOWER(COALESCE(trang_thai, "")) IN (?, ?, ?)', [
                        'ket_thuc',
                        'ket thuc',
                        'kết thúc',
                    ]);
                });
            }
        });
    }

    protected function resolveScheduleBounds(): array
    {
        if ($this->scheduleBoundsCache !== null) {
            return $this->scheduleBoundsCache;
        }

        $schedules = $this->relationLoaded('lichHocs')
            ? $this->lichHocs
            : $this->lichHocs()->get(['ngay_hoc', 'gio_bat_dau', 'gio_ket_thuc']);

        if ($schedules->isEmpty()) {
            return $this->scheduleBoundsCache = [null, null];
        }

        $earliest = null;
        $latest = null;

        foreach ($schedules as $schedule) {
            if (!$schedule->ngay_hoc) {
                continue;
            }

            $startTime = $schedule->gio_bat_dau ?: '00:00:00';
            $endTime = $schedule->gio_ket_thuc ?: '23:59:59';

            try {
                $start = Carbon::parse($schedule->ngay_hoc . ' ' . $startTime);
            } catch (\Throwable $e) {
                $start = Carbon::parse($schedule->ngay_hoc)->startOfDay();
            }

            try {
                $end = Carbon::parse($schedule->ngay_hoc . ' ' . $endTime);
            } catch (\Throwable $e) {
                $end = Carbon::parse($schedule->ngay_hoc)->endOfDay();
            }

            if ($earliest === null || $start->lt($earliest)) {
                $earliest = $start->clone();
            }

            if ($latest === null || $end->gt($latest)) {
                $latest = $end->clone();
            }
        }

        if (!$earliest || !$latest) {
            return $this->scheduleBoundsCache = [null, null];
        }

        return $this->scheduleBoundsCache = [$earliest, $latest];
    }
}
