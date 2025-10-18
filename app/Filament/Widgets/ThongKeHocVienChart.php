<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ThongKeHocVienChart extends Widget
{
    protected static string $view = 'filament.widgets.enrollment-overview-chart';

    public ?int $year = null;

    public ?int $month = null;

    public array $chartData = [];

    public array $chartOptions = [];

    public array $monthSummary = [];

    public array $monthOptions = [];

    public array $yearOptions = [];

    protected array $monthlyAggregates = [];

    public function mount(): void
    {
        $this->year = $this->resolveDefaultYear();
        $this->refreshState(resetMonth: true);
    }

    public function updatedYear(): void
    {
        $this->refreshState(resetMonth: true);
    }

    public function updatedMonth(): void
    {
        $this->refreshMonthSummary();
    }

    protected function refreshState(bool $resetMonth = false): void
    {
        if ($resetMonth) {
            $this->month = null;
        }

        $this->yearOptions = $this->formatYearOptions($this->getAvailableYears());

        if ($this->year !== null && ! array_key_exists($this->year, $this->yearOptions)) {
            $this->year = null;
        }

        $year = $this->year ?? $this->resolveDefaultYear();
        $this->monthlyAggregates = $this->buildMonthlyAggregates($year);
        $this->chartData = $this->buildChartData($this->monthlyAggregates);
        $this->chartOptions = $this->buildChartOptions();
        $this->monthOptions = $this->buildMonthOptions($this->monthlyAggregates);
        $this->refreshMonthSummary();
    }

    protected function refreshMonthSummary(): void
    {
        $month = $this->month;
        $summary = [
            'label' => null,
            'dang_ky' => 0,
            'hoan_thanh' => 0,
            'khong_hoan_thanh' => 0,
            'vang_phep' => 0,
            'vang_khong_phep' => 0,
            'khac' => 0,
        ];

        if ($month !== null && isset($this->monthlyAggregates[$month])) {
            $summary = array_merge($summary, $this->monthlyAggregates[$month]);
            $summary['label'] = 'Tháng ' . $month;
        }

        $this->monthSummary = $summary;
    }

    protected function buildMonthlyAggregates(int $year): array
    {
        $courseIds = $this->resolveCourseIdsForYear($year);
        $base = [];

        foreach (range(1, 12) as $month) {
            $base[$month] = [
                'dang_ky' => 0,
                'hoan_thanh' => 0,
                'khong_hoan_thanh' => 0,
                'vang_phep' => 0,
                'vang_khong_phep' => 0,
                'khac' => 0,
            ];
        }

        if (empty($courseIds)) {
            return $base;
        }

        $registrationCounts = DangKy::query()
            ->whereIn('khoa_hoc_id', $courseIds)
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        foreach ($registrationCounts as $month => $count) {
            if (isset($base[(int) $month])) {
                $base[(int) $month]['dang_ky'] = (int) $count;
            }
        }

        $completionCounts = HocVienHoanThanh::query()
            ->whereIn('khoa_hoc_id', $courseIds)
            ->where(function (Builder $query) use ($year) {
                $query->whereYear('ngay_hoan_thanh', $year)
                    ->orWhere(function (Builder $sub) use ($year) {
                        $sub->whereNull('ngay_hoan_thanh')
                            ->whereYear('created_at', $year);
                    });
            })
            ->selectRaw('MONTH(COALESCE(ngay_hoan_thanh, created_at)) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        foreach ($completionCounts as $month => $count) {
            if (isset($base[(int) $month])) {
                $base[(int) $month]['hoan_thanh'] = (int) $count;
            }
        }

        $incompleteRecords = HocVienKhongHoanThanh::query()
            ->whereIn('khoa_hoc_id', $courseIds)
            ->get(['id', 'ly_do_khong_hoan_thanh', 'created_at', 'updated_at']);

        foreach ($incompleteRecords as $record) {
            $date = $record->created_at ?? $record->updated_at;

            if (! $date) {
                continue;
            }

            $recordYear = (int) $date->format('Y');

            if ($recordYear !== $year) {
                continue;
            }

            $month = (int) $date->format('n');

            if (! isset($base[$month])) {
                continue;
            }

            $base[$month]['khong_hoan_thanh']++;

            $category = $this->resolveAbsenceCategory($record->ly_do_khong_hoan_thanh);
            $base[$month][$category]++;
        }

        return $base;
    }

    protected function buildChartData(array $aggregates): array
    {
        $labels = $this->monthLabels();
        $registrations = [];
        $completions = [];
        $incomplete = [];

        foreach (range(1, 12) as $index) {
            $registrations[] = Arr::get($aggregates, "$index.dang_ky", 0);
            $completions[] = Arr::get($aggregates, "$index.hoan_thanh", 0);
            $incomplete[] = Arr::get($aggregates, "$index.khong_hoan_thanh", 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Đăng ký',
                    'data' => $registrations,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.85)',
                    'borderColor' => 'rgba(37, 99, 235, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.3,
                    'borderRadius' => 8,
                    'hoverBorderWidth' => 2,
                    'borderSkipped' => false,
                ],
                [
                    'label' => 'Hoàn thành',
                    'data' => $completions,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.85)',
                    'borderColor' => 'rgba(22, 163, 74, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.3,
                    'borderRadius' => 8,
                    'hoverBorderWidth' => 2,
                    'borderSkipped' => false,
                ],
                [
                    'label' => 'Không hoàn thành',
                    'data' => $incomplete,
                    'backgroundColor' => 'rgba(248, 113, 113, 0.85)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 1,
                    'tension' => 0.3,
                    'borderRadius' => 8,
                    'hoverBorderWidth' => 2,
                    'borderSkipped' => false,
                ],
            ],
        ];
    }

    protected function buildChartOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'layout' => [
                'padding' => ['top' => 12, 'bottom' => 12, 'left' => 8, 'right' => 8],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'boxWidth' => 12,
                        'color' => '#1e293b',
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(15, 23, 42, 0.95)',
                    'titleFont' => ['weight' => '600'],
                    'bodySpacing' => 6,
                    'usePointStyle' => true,
                    'padding' => 12,
                ],
            ],
            'datasets' => [
                'bar' => [
                    'borderRadius' => 10,
                    'maxBarThickness' => 30,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => ['size' => 12, 'weight' => '500'],
                        'color' => '#475569',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                        'color' => '#475569',
                        'font' => ['size' => 12, 'weight' => '500'],
                    ],
                    'grid' => [
                        'color' => 'rgba(148, 163, 184, 0.15)',
                        'drawBorder' => false,
                    ],
                ],
            ],
            'animation' => [
                'duration' => 800,
                'easing' => 'easeOutQuart',
                'delay' => 80,
            ],
        ];
    }

    protected function buildMonthOptions(array $aggregates): array
    {
        $options = [];

        foreach (range(1, 12) as $month) {
            $total = Arr::get($aggregates, "$month.dang_ky", 0)
                + Arr::get($aggregates, "$month.hoan_thanh", 0)
                + Arr::get($aggregates, "$month.khong_hoan_thanh", 0);

            if ($total > 0) {
                $options[$month] = 'Tháng ' . $month;
            }
        }

        return $options;
    }

    protected function resolveAbsenceCategory(?string $reason): string
    {
        $normalized = Str::lower(trim((string) $reason));

        if ($normalized === '') {
            return 'khac';
        }

        if (Str::contains($normalized, ['kp', 'không phép', 'khong phep'])) {
            return 'vang_khong_phep';
        }

        if (Str::contains($normalized, ['p', 'phép', 'phep'])) {
            return 'vang_phep';
        }

        if (Str::contains($normalized, ['vang', 'vắng'])) {
            return 'vang_phep';
        }

        return 'khac';
    }

    protected function resolveCourseIdsForYear(int $year): array
    {
        return KhoaHoc::query()
            ->where('nam', $year)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function monthLabels(): array
    {
        return collect(range(1, 12))
            ->map(fn (int $month) => 'Tháng ' . $month)
            ->toArray();
    }

    protected function resolveDefaultYear(): int
    {
        $currentYear = (int) now()->format('Y');
        $availableYears = $this->getAvailableYears();

        if (in_array($currentYear, $availableYears, true)) {
            return $currentYear;
        }

        return $availableYears[0] ?? $currentYear;
    }

    protected function getAvailableYears(): array
    {
        $years = KhoaHoc::query()
            ->select('nam')
            ->distinct()
            ->orderBy('nam', 'desc')
            ->pluck('nam')
            ->map(fn ($year) => (int) $year)
            ->all();

        if (empty($years)) {
            $years[] = (int) now()->format('Y');
        }

        return $years;
    }

    protected function formatYearOptions(array $years): array
    {
        $options = [];

        foreach ($years as $year) {
            $options[$year] = (string) $year;
        }

        return $options;
    }
}
