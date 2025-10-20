<?php

namespace App\Support;

use App\Models\KhoaHoc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TrainingTypeFilter
{
    /**
     * Build the display options for training types using both course and program sources.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $values = collect();

        if (self::hasCourseTrainingTypeColumn()) {
            $values = $values->merge(
                DB::table('khoa_hocs')
                    ->select('loai_hinh_dao_tao')
                    ->whereNotNull('loai_hinh_dao_tao')
                    ->pluck('loai_hinh_dao_tao')
            );
        }

        if (self::hasProgramTrainingTypeColumn()) {
            $values = $values->merge(
                DB::table('chuong_trinhs')
                    ->select('loai_hinh_dao_tao')
                    ->whereNotNull('loai_hinh_dao_tao')
                    ->pluck('loai_hinh_dao_tao')
            );
        }

        return $values
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $value) => [$value => self::normalizeLabel($value)])
            ->sortBy(fn (string $label, string $value) => Str::lower($label))
            ->toArray();
    }

    /**
     * Apply the training type constraint directly on a course query.
     */
    public static function apply(Builder $builder, array $selectedTypes): void
    {
        $types = self::sanitizeSelection($selectedTypes);

        if (empty($types)) {
            return;
        }

        $builder->where(function (Builder $query) use ($types) {
            $applied = false;

            if (self::hasCourseTrainingTypeColumn()) {
                $query->whereIn('loai_hinh_dao_tao', $types);
                $applied = true;
            }

            if (self::hasProgramTrainingTypeColumn()) {
                $method = $applied ? 'orWhereHas' : 'whereHas';
                $query->{$method}('chuongTrinh', function (Builder $relation) use ($types) {
                    $relation->whereIn('loai_hinh_dao_tao', $types);
                });
                $applied = true;
            }

            if (! $applied) {
                $query->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Apply the training type constraint on a query that references courses via a foreign key.
     */
    public static function applyViaCourse(Builder $builder, string $courseColumn, array $selectedTypes): void
    {
        $types = self::sanitizeSelection($selectedTypes);

        if (empty($types)) {
            return;
        }

        $courseIdsQuery = KhoaHoc::query()->select('id');
        self::apply($courseIdsQuery, $types);

        $builder->whereIn($courseColumn, $courseIdsQuery);
    }

    /**
     * Remove duplicates, nulls and ensure scalar string values for selection arrays.
     *
     * @param  array<int, mixed>  $values
     * @return array<int, string>
     */
    protected static function sanitizeSelection(array $values): array
    {
        return collect($values)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->values()
            ->all();
    }

    protected static function normalizeLabel(string $value): string
    {
        $label = trim($value);
        $normalized = $label;

        $pattern = '/^[\s]*(?:[Vv]|✓|\-|–|—|•)+(?:\.|:)?(?:\s+|$)/u';
        $normalized = preg_replace($pattern, '', $normalized) ?? $normalized;

        // Remove stray decorative characters and excessive whitespace.
        $normalized = str_replace(['✓', '–', '—', '•'], '', $normalized);
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized, " \t\n\r\0\x0B-–—•✓");

        if ($normalized === '') {
            return $label;
        }

        return $normalized;
    }

    protected static function hasCourseTrainingTypeColumn(): bool
    {
        return Schema::hasTable('khoa_hocs') && Schema::hasColumn('khoa_hocs', 'loai_hinh_dao_tao');
    }

    protected static function hasProgramTrainingTypeColumn(): bool
    {
        return Schema::hasTable('chuong_trinhs') && Schema::hasColumn('chuong_trinhs', 'loai_hinh_dao_tao');
    }
}
