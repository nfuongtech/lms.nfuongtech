<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\HocVienHoanThanhResource;
use App\Models\HocVienHoanThanh;
use App\Models\KhoaHoc;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Js;

class ChiPhiDaoTaoChart extends Widget
{
    protected static string $view = 'filament.widgets.training-cost-chart';

    /**
     * @var int|null
     */
    public $year = null;

    /**
     * @var int|null
     */
    public $month = null;

    /**
     * @var array<int, string>
     */
    public $selectedTrainingTypes = [];

    /**
     * @var array<string, mixed>
     */
    public $chartData = [];

    /**
     * @var array<string, mixed>
     */
    public $chartOptions = [];

    /**
     * @var array<string, string>
     */
    public $trainingTypeOptions = [];

    /**
     * @var array<int, string>
     */
    public $monthOptions = [];

    /**
     * @var array<int, array<string, float>>
     */
    protected $aggregatedCosts = [];

    /**
     * @var array<int, string>
     */
    public $yearOptions = [];

    /**
     * @var float
     */
    public $totalCost = 0.0;

    /**
     * @var array<string, float>
     */
    public $typeTotals = [];

    public function mount(): void
    {
        $this->yearOptions = $this->formatYearOptions($this->getAvailableYears());
        $this->monthOptions = $this->formatMonthOptions();
        $this->year = $this->resolveDefaultYear();
        $this->month = $this->resolveDefaultMonth();
        $this->trainingTypeOptions = $this->getTrainingTypeOptions();
        $this->refreshState();
    }

    public function updatedYear(): void
    {
        $this->refreshState(resetSelections: false);
    }

    public function updatedMonth($value): void
    {
        $this->month = $value === '' ? null : (int) $value;
        $this->refreshState(resetSelections: false);
    }

    public function updatedSelectedTrainingTypes(): void
    {
        $this->refreshState(resetSelections: false);
    }

    protected function refreshState(bool $resetSelections = true): void
    {
        if ($resetSelections) {
            $this->selectedTrainingTypes = [];
        }

        $this->yearOptions = $this->formatYearOptions($this->getAvailableYears());
        $this->monthOptions = $this->formatMonthOptions();

        if ($this->year !== null && ! array_key_exists($this->year, $this->yearOptions)) {
            $this->year = null;
        }

        if ($this->month !== null && ($this->month < 1 || $this->month > 12)) {
            $this->month = null;
        }

        $year = $this->year ?? $this->resolveDefaultYear();
        $types = $this->selectedTrainingTypes;

        $this->aggregatedCosts = $this->buildCostMatrix($year, $types, $this->month);
        $this->chartData = $this->buildChartData($this->aggregatedCosts, $types, $this->month);
        $this->chartOptions = $this->buildChartOptions($this->month);
        $this->totalCost = $this->calculateTotalCost($this->aggregatedCosts);
        $this->typeTotals = $this->calculateTypeTotals($this->aggregatedCosts);
    }

    protected function buildCostMatrix(int $year, array $selectedTypes, ?int $selectedMonth = null): array
    {
        $courseQuery = KhoaHoc::query()->where('nam', $year);

        if (! empty($selectedTypes)) {
            HocVienHoanThanhResource::applyTrainingTypeFilter($courseQuery, $selectedTypes);
        }

        $courseIds = $courseQuery
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $matrix = [];

        foreach (range(1, 12) as $month) {
            $matrix[$month] = [];
        }

        if (empty($courseIds)) {
            return $matrix;
        }

        $records = HocVienHoanThanh::query()
            ->with(['khoaHoc.chuongTrinh'])
            ->whereIn('khoa_hoc_id', $courseIds)
            ->whereNotNull('chi_phi_dao_tao')
            ->where(function (Builder $query) use ($year, $selectedMonth) {
                $query->where(function (Builder $completed) use ($year, $selectedMonth) {
                    $completed->whereYear('ngay_hoan_thanh', $year);

                    if ($selectedMonth) {
                        $completed->whereMonth('ngay_hoan_thanh', $selectedMonth);
                    }
                })->orWhere(function (Builder $sub) use ($year, $selectedMonth) {
                    $sub->whereNull('ngay_hoan_thanh')
                        ->whereYear('created_at', $year);

                    if ($selectedMonth) {
                        $sub->whereMonth('created_at', $selectedMonth);
                    }
                });
            })
            ->get();

        foreach ($records as $record) {
            $dateValue = $record->ngay_hoan_thanh ?? $record->created_at;

            if (! $dateValue) {
                continue;
            }

            $date = $dateValue instanceof Carbon
                ? $dateValue
                : Carbon::parse($dateValue);

            $month = (int) $date->format('n');

            if ($selectedMonth !== null && $month !== $selectedMonth) {
                continue;
            }

            if ($month < 1 || $month > 12) {
                continue;
            }

            $course = $record->khoaHoc;

            if (! $course) {
                continue;
            }

            $course->loadMissing('chuongTrinh');

            $type = $this->resolveTrainingType($course);

            if ($type === null) {
                continue;
            }

            if (! isset($matrix[$month][$type])) {
                $matrix[$month][$type] = 0.0;
            }

            $matrix[$month][$type] += (float) $record->chi_phi_dao_tao;
        }

        if ($selectedMonth !== null) {
            foreach (array_keys($matrix) as $monthIndex) {
                if ($monthIndex !== $selectedMonth) {
                    $matrix[$monthIndex] = [];
                }
            }
        }

        return $matrix;
    }

    protected function calculateTotalCost(array $matrix): float
    {
        $sum = 0.0;

        foreach ($matrix as $values) {
            foreach ($values as $amount) {
                $sum += (float) $amount;
            }
        }

        return round($sum, 2);
    }

    protected function calculateTypeTotals(array $matrix): array
    {
        $totals = [];

        foreach ($matrix as $values) {
            foreach ($values as $type => $amount) {
                $label = $this->formatTrainingTypeLabel($type);

                if (! isset($totals[$label])) {
                    $totals[$label] = 0.0;
                }

                $totals[$label] += (float) $amount;
            }
        }

        ksort($totals);

        return array_map(fn ($value) => round($value, 2), $totals);
    }

    protected function buildChartData(array $matrix, array $selectedTypes, ?int $selectedMonth = null): array
    {
        $labels = $selectedMonth === null
            ? $this->monthLabels()
            : [sprintf('T%02d', $selectedMonth)];
        $datasets = [];

        $types = ! empty($selectedTypes)
            ? $selectedTypes
            : $this->collectTypesFromMatrix($matrix);

        $types = $this->sortTypesByLabel($types);

        $palette = $this->buildPalette(count($types));

        foreach ($types as $index => $type) {
            $data = [];

            if ($selectedMonth === null) {
                foreach (range(1, 12) as $month) {
                    $data[] = round(Arr::get($matrix, "$month.$type", 0), 2);
                }
            } else {
                $data[] = round(Arr::get($matrix, "$selectedMonth.$type", 0), 2);
            }

            $color = $palette[$index % count($palette)];

            $datasets[] = [
                'label' => $this->formatTrainingTypeLabel($type),
                'data' => $data,
                'backgroundColor' => $color['background'],
                'borderColor' => $color['border'],
                'borderWidth' => 1,
                'tension' => 0.3,
                'borderRadius' => 8,
                'hoverBorderWidth' => 2,
                'borderSkipped' => false,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function buildChartOptions(?int $selectedMonth = null): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
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
                    'usePointStyle' => true,
                    'padding' => 12,
                ],
                'barValueLabels' => [
                    'padding' => 10,
                    'color' => '#0f172a',
                    'font' => [
                        'size' => 11,
                        'weight' => '600',
                    ],
                    'align' => 'left',
                    'verticalAlign' => 'middle',
                    'locale' => 'vi-VN',
                    'formatter' => new Js(<<<'JS'
                        (value) => {
                            const numeric = Number(value || 0);
                            if (!Number.isFinite(numeric)) {
                                return '';
                            }

                            return `${numeric.toLocaleString('vi-VN')} đ`;
                        }
                    JS),
                ],
            ],
            'datasets' => [
                'bar' => [
                    'borderRadius' => 10,
                    'maxBarThickness' => 46,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(148, 163, 184, 0.15)',
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'precision' => 0,
                        'color' => '#475569',
                        'font' => ['size' => 12, 'weight' => '500'],
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#475569',
                        'font' => ['size' => 12, 'weight' => '500'],
                    ],
                ],
            ],
            'animation' => [
                'duration' => 900,
                'easing' => 'easeOutQuart',
                'delay' => 100,
            ],
            '__meta' => [
                'tooltipLocale' => 'vi-VN',
                'tooltipSuffix' => ' VND',
                'tickLocale' => 'vi-VN',
                'tickDivisor' => 1000000,
                'tickSuffix' => ' tr',
            ],
        ];
    }

    protected function collectTypesFromMatrix(array $matrix): array
    {
        return collect($matrix)
            ->flatMap(fn ($items) => array_keys($items))
            ->unique()
            ->values()
            ->all();
    }

    protected function buildPalette(int $count): array
    {
        $base = [
            ['background' => 'rgba(59, 130, 246, 0.85)', 'border' => 'rgba(37, 99, 235, 1)'],
            ['background' => 'rgba(16, 185, 129, 0.85)', 'border' => 'rgba(5, 150, 105, 1)'],
            ['background' => 'rgba(249, 115, 22, 0.85)', 'border' => 'rgba(234, 88, 12, 1)'],
            ['background' => 'rgba(168, 85, 247, 0.85)', 'border' => 'rgba(147, 51, 234, 1)'],
            ['background' => 'rgba(14, 165, 233, 0.85)', 'border' => 'rgba(2, 132, 199, 1)'],
            ['background' => 'rgba(244, 114, 182, 0.85)', 'border' => 'rgba(236, 72, 153, 1)'],
        ];

        if ($count <= count($base)) {
            return array_slice($base, 0, max($count, 1));
        }

        $palette = [];

        for ($i = 0; $i < $count; $i++) {
            $palette[] = $base[$i % count($base)];
        }

        return $palette;
    }

    protected function resolveTrainingType(?KhoaHoc $course): ?string
    {
        if (! $course) {
            return null;
        }

        $courseType = $course->loai_hinh_dao_tao ?? null;

        if (! $courseType && $course->relationLoaded('chuongTrinh')) {
            $courseType = $course->chuongTrinh?->loai_hinh_dao_tao;
        }

        if (! $courseType && method_exists($course, 'chuongTrinh')) {
            $courseType = $course->chuongTrinh()->value('loai_hinh_dao_tao');
        }

        return $courseType ? trim((string) $courseType) : null;
    }

    protected function monthLabels(): array
    {
        return collect(range(1, 12))
            ->map(fn (int $month) => sprintf('T%02d', $month))
            ->toArray();
    }

    protected function getTrainingTypeOptions(): array
    {
        $options = HocVienHoanThanhResource::getTrainingTypeOptions();

        if (empty($options)) {
            return [];
        }

        return collect($options)
            ->mapWithKeys(fn ($label, $value) => [
                $value => $this->formatTrainingTypeLabel($label),
            ])
            ->filter(fn ($label) => trim((string) $label) !== '')
            ->sort(fn ($a, $b) => strcmp($a, $b))
            ->toArray();
    }

    protected function resolveDefaultYear(): int
    {
        $currentYear = (int) now()->format('Y');
        $years = $this->getAvailableYears();

        if (in_array($currentYear, $years, true)) {
            return $currentYear;
        }

        return $years[0] ?? $currentYear;
    }

    protected function resolveDefaultMonth(): ?int
    {
        return null;
    }

    protected function formatYearOptions(array $years): array
    {
        $options = [];

        foreach ($years as $year) {
            $options[$year] = (string) $year;
        }

        return $options;
    }

    protected function formatMonthOptions(): array
    {
        $options = [];

        foreach (range(1, 12) as $month) {
            $options[$month] = sprintf('T%02d', $month);
        }

        return $options;
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

    protected function formatTrainingTypeLabel(string $label): string
    {
        $value = trim((string) $label);

        $clean = preg_replace('/^[Vv✓✔☑✅•\-\/\s]+/u', '', $value) ?? $value;
        $clean = preg_replace('/^[-–—]\s*/u', '', $clean) ?? $clean;
        $clean = preg_replace('/\s{2,}/u', ' ', $clean) ?? $clean;

        $normalized = trim($clean, " \t\n\r\0\x0B-–—");

        return $normalized !== '' ? $normalized : $value;
    }

    protected function sortTypesByLabel(array $types): array
    {
        $sorted = $types;

        usort($sorted, function ($a, $b) {
            return strcmp(
                $this->formatTrainingTypeLabel((string) $a),
                $this->formatTrainingTypeLabel((string) $b)
            );
        });

        return $sorted;
    }
}
