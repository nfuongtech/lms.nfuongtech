<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
// Bỏ use Filament\Support\RawJs;

class ThongKeHocVienChart extends ChartWidget
{
    protected static ?string $heading = 'Thống kê Học viên theo tháng';
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = 12;
    protected static ?string $maxHeight = '420px';
    protected static bool $isLazy = false;

    protected ?Collection $planYearCache = null;
    protected ?array $trainingTypeOptionsCache = null;
    protected array $courseMonthCache = [];

    protected function getFormSchema(): array
    {
        Log::info('ThongKeHocVienChart: getFormSchema() IS BEING CALLED.'); // Dùng Log::info để dễ thấy
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Select::make('year')
                        ->label('Năm')
                        ->options($this->getPlanYearOptions())
                        ->default($this->getDefaultYear())
                        ->live()
                        ->required(),
                    Forms\Components\Select::make('selectedTrainingTypes')
                        ->label('Loại hình đào tạo')
                        ->options($this->getTrainingTypeOptions())
                        ->multiple()
                        ->placeholder('Tất cả loại hình')
                        ->live(),
                ])
        ];
    }

    protected function getData(): array
    {
        Log::debug('ThongKeHocVienChart: getData() called.');
        $year = (int) ($this->filterFormData['year'] ?? $this->getDefaultYear());
        $selectedTrainingTypes = $this->filterFormData['selectedTrainingTypes'] ?? [];
        Log::info("ChartWidget Filters - Year: {$year}, Types: " . implode(', ', $selectedTrainingTypes ?: ['Tất cả']));
        $series = $this->compileMonthlySeries($year, $selectedTrainingTypes);
        return [
            'labels'   => collect(range(1, 12))->map(fn ($m) => sprintf('T%02d', $m))->all(),
            'datasets' => [
                $this->makeBarDataset('Đăng ký', array_values($series['dangKy']), 'dang-ky'),
                $this->makeBarDataset('Hoàn thành', array_values($series['hoanThanh']), 'hoan-thanh'),
                $this->makeBarDataset('Không hoàn thành', array_values($series['khongHoanThanh']), 'khong-hoan-thanh'),
            ],
        ];
    }

    protected function getOptions(): array
    {
        Log::debug('ThongKeHocVienChart: getOptions() called.');
        return [ // Options không có RawJs
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'padding' => 20, 'boxWidth' => 12]], 'tooltip' => ['mode' => 'index', 'intersect' => false, 'backgroundColor' => 'rgba(15, 23, 42, 0.95)', 'titleFont' => ['weight' => '600'], 'padding' => 12]],
            'responsive' => true, 'maintainAspectRatio' => false, 'interaction' => ['mode' => 'index', 'intersect' => false],
            'layout' => ['padding' => ['top' => 24, 'right' => 16, 'bottom' => 12, 'left' => 8]],
            'scales' => ['x' => ['grid' => ['display' => false], 'ticks' => ['color' => '#475569', 'font' => ['size' => 12, 'weight' => '500']]], 'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0, 'color' => '#475569', 'font' => ['size' => 12, 'weight' => '500']], 'grid' => ['color' => 'rgba(148, 163, 184, 0.18)', 'drawBorder' => false]]],
            'animation' => ['duration' => 900, 'easing' => 'easeOutQuart'],
        ];
    }

    protected function getType(): string
    {
        Log::debug('ThongKeHocVienChart: getType() called.');
        return 'bar';
    }

    // --- Các hàm helper giữ nguyên như trước ---
    protected function getPlanYearOptions(): array { /* ... */ return $this->planYears()->mapWithKeys(fn ($year) => [$year => (string) $year])->all(); }
    protected function getTrainingTypeOptions(): array { /* ... */ if ($this->trainingTypeOptionsCache !== null) return $this->trainingTypeOptionsCache; $table = (new KhoaHoc)->getTable(); if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'loai_hinh_dao_tao')) return []; try { $options = KhoaHoc::query()->whereNotNull('loai_hinh_dao_tao')->where('loai_hinh_dao_tao', '!=', '')->distinct()->orderBy('loai_hinh_dao_tao')->pluck('loai_hinh_dao_tao')->mapWithKeys(fn ($label) => [$label => $this->formatTrainingTypeLabel($label)])->all(); return $this->trainingTypeOptionsCache = $options; } catch (\Exception $e) { Log::error("Get training types error: ".$e->getMessage()); return []; } }
    protected function getDefaultYear(): int { /* ... */ $years = $this->getPlanYearOptions(); $currentYear = now()->year; return isset($years[$currentYear]) ? $currentYear : (int) (array_key_first($years) ?? $currentYear); }
    protected function planYears(): Collection { /* ... */ if ($this->planYearCache !== null) return $this->planYearCache; $years = collect(); if (Schema::hasTable('lich_hocs')) { if (Schema::hasColumn('lich_hocs', 'nam')) { $years = $years->merge(DB::table('lich_hocs')->whereNotNull('nam')->distinct()->orderByDesc('nam')->pluck('nam')); } elseif (Schema::hasColumn('lich_hocs', 'ngay_hoc')) { $years = $years->merge(DB::table('lich_hocs')->whereNotNull('ngay_hoc')->selectRaw('DISTINCT YEAR(ngay_hoc) as y')->orderByDesc('y')->pluck('y')); } } if ($years->isEmpty() && Schema::hasTable('khoa_hocs')) { $col = Schema::hasColumn('khoa_hocs', 'ngay_bat_dau') ? 'ngay_bat_dau' : (Schema::hasColumn('khoa_hocs', 'created_at') ? 'created_at' : null); if($col && Schema::hasColumn('khoa_hocs', $col)) { $years = DB::table('khoa_hocs')->whereNotNull($col)->selectRaw("DISTINCT YEAR($col) as y")->orderByDesc('y')->pluck('y'); } } $now = now()->year; if ($years->isEmpty()) $years = collect([$now]); elseif (!$years->contains($now)) $years->prepend($now)->sortDesc(); return $this->planYearCache = $years->map(fn ($v) => filter_var($v, FILTER_VALIDATE_INT))->filter(fn ($v) => $v !== false && $v > 1900)->unique()->values(); }
    private function compileMonthlySeries(int $year, array $selectedTrainingTypes = []): array { $map = $this->courseIdsByMonth($year, $selectedTrainingTypes); return ['dangKy'=> $this->countByMonthUsingCourseMap(DangKy::query(), $map),'hoanThanh'=>$this->countByMonthUsingCourseMap(HocVienHoanThanh::query(), $map),'khongHoanThanh'=>$this->countByMonthUsingCourseMap(HocVienKhongHoanThanh::query(), $map)]; }
    private function courseIdsByMonth(int $year, array $selectedTrainingTypes = []): array { /* ... */ sort($selectedTrainingTypes); $cacheKey = $year . '_' . implode('-', $selectedTrainingTypes); if (isset($this->courseMonthCache[$cacheKey])) return $this->courseMonthCache[$cacheKey]; $buckets = $this->emptyMonthlyCourseBuckets(); if (! Schema::hasTable('lich_hocs')) { Log::warning('Table lich_hocs does not exist.'); return $this->courseMonthCache[$cacheKey] = $buckets; } try { $allowedCourseIds = null; if (!empty($selectedTrainingTypes) && Schema::hasTable('khoa_hocs') && Schema::hasColumn('khoa_hocs', 'loai_hinh_dao_tao')) { $allowedCourseIds = KhoaHoc::query()->whereIn('loai_hinh_dao_tao', $selectedTrainingTypes)->pluck('id')->all(); if (empty($allowedCourseIds)) return $this->courseMonthCache[$cacheKey] = $buckets; } $query = DB::table('lich_hocs')->select(['khoa_hoc_id', 'thang', 'ngay_hoc'])->whereNotNull('khoa_hoc_id'); $yearCol = Schema::hasColumn('lich_hocs', 'nam'); $dateCol = Schema::hasColumn('lich_hocs', 'ngay_hoc'); if ($yearCol) $query->where('nam', $year); elseif ($dateCol) $query->whereYear('ngay_hoc', $year); else { Log::warning('Cannot filter lich_hocs by year.'); return $this->courseMonthCache[$cacheKey] = $buckets; } if ($allowedCourseIds !== null) $query->whereIn('khoa_hoc_id', $allowedCourseIds); $rows = $query->cursor(); foreach ($rows as $row) { $month = null; if (isset($row->thang)) { $m = filter_var($row->thang, FILTER_VALIDATE_INT); if ($m !== false && $m >= 1 && $m <= 12) $month = $m; } if ($month === null && $dateCol && isset($row->ngay_hoc)) { try { if (!empty($row->ngay_hoc) && $row->ngay_hoc !== '0000-00-00' && preg_match('/^\d{4}-\d{2}-\d{2}/', $row->ngay_hoc)) { $date = Carbon::parse($row->ngay_hoc); $m = (int) $date->month; if ($m >= 1 && $m <= 12) $month = $m; } } catch (\Exception $e) {} } if ($month === null) continue; $courseId = filter_var($row->khoa_hoc_id, FILTER_VALIDATE_INT); if ($courseId === false || $courseId <= 0) continue; if (!isset($buckets[$month][$courseId])) $buckets[$month][$courseId] = true; } $normalized = []; foreach ($buckets as $m => $ids) $normalized[$m] = array_keys($ids); return $this->courseMonthCache[$cacheKey] = $normalized; } catch (\Exception $e) { Log::error("courseIdsByMonth error: ".$e->getMessage()); return $this->courseMonthCache[$cacheKey] = $this->emptyMonthlyCourseBuckets(); } }
    private function emptyMonthlyBuckets(): array { return array_fill(1, 12, 0); }
    private function emptyMonthlyCourseBuckets(): array { return array_fill(1, 12, []); }
    private function makeBarDataset(string $label, array $data, string $key, array $extra = []): array { $palette = [ 'dang-ky'=>[59,130,246], 'hoan-thanh'=>[16,185,129], 'khong-hoan-thanh'=>[249,115,22] ]; $rgb = $palette[$key] ?? [107,114,128]; $base = ['label' => $label, 'data' => array_values($data), 'backgroundColor' => sprintf('rgba(%d,%d,%d,0.85)', ...$rgb), 'borderColor' => sprintf('rgba(%d,%d,%d,1)', ...$rgb), 'borderWidth' => 1, 'borderRadius' => 8, 'maxBarThickness' => 46]; return array_replace_recursive($base, $extra); }
    private function countByMonthUsingCourseMap(Builder $query, array $courseMap): array { $buckets = $this->emptyMonthlyBuckets(); $allCourseIds = collect($courseMap)->flatten()->unique()->values()->all(); if (empty($allCourseIds)) return $buckets; $table = $query->getModel()->getTable(); $keyName = $query->getModel()->getQualifiedKeyName(); try { $rows = $query->whereIn("{$table}.khoa_hoc_id", $allCourseIds)->selectRaw("{$table}.khoa_hoc_id as course_id, COUNT(DISTINCT {$keyName}) as aggregate")->groupBy("{$table}.khoa_hoc_id")->pluck('aggregate', 'course_id'); foreach ($courseMap as $month => $idsInMonth) { $total = 0; foreach ($idsInMonth as $courseId) $total += (int) ($rows[$courseId] ?? 0); if (isset($buckets[$month])) $buckets[$month] = $total; } } catch (\Exception $e) { Log::error("Count error ({$table}): ".$e->getMessage()); return $this->emptyMonthlyBuckets(); } return $buckets; }
    private function formatTrainingTypeLabel(string $label): string { $value = trim((string) $label); if ($value === '') return $label; $clean = $value; try { $clean = preg_replace('/^[Vv✓✔☑✅•\-\/\s]+/u','',$value)??$value; $clean = preg_replace('/^[-–—]\s*/u','',$clean)??$clean; $clean = preg_replace('/[✓✔☑✅]+/u','',$clean)??$clean; $clean = preg_replace('/\b[Vv]\b/u','',$clean)??$clean; $clean = preg_replace('/\s{2,}/u',' ',$clean)??$clean; } catch (\Exception $e) { $clean = $value; } $normalized = trim($clean, " \t\n\r\0\x0B-–—"); return $normalized !== '' ? $normalized : $value; }
}
