<?php

namespace App\Filament\Resources\HocVienHoanThanhResource\Pages;

use App\Exports\SimpleArrayExport;
use App\Filament\Resources\HocVienHoanThanhResource;
use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ListHocVienHoanThanhs extends ListRecords
{
    protected static string $resource = HocVienHoanThanhResource::class;

    protected static ?string $title = 'Học viên hoàn thành';

    protected ?string $heading = null;

    protected static string $view = 'filament.resources.hoc-vien-hoan-thanh-resource.pages.list-hoc-vien-hoan-thanhs';

    public ?array $tableFilters = [];

    public int $filterYear;

    public ?int $filterMonth = null;

    public ?int $filterWeek = null;

    public ?string $filterFromDate = null;

    public ?string $filterToDate = null;

    public ?int $filterCourseId = null;

    public array $filterTrainingTypes = [];

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function mount(): void
    {
        parent::mount();

        $state = $this->defaultFilterState();
        $this->applyFilterState($state, false);
    }

    public function hydrate(): void
    {
        $this->syncFilterInputsFromState($this->resolveFilterState());
    }

    public function exportFilteredExcel()
    {
        $records = $this->getExportCollection();

        if ($records->isEmpty()) {
            Notification::make()->title('Không có dữ liệu để xuất')->warning()->send();

            return null;
        }

        $columnDefinitions = $this->getExportColumnDefinitions();
        $visibleColumnKeys = $this->getVisibleColumnKeys();

        $studentHeaders = [];
        foreach ($visibleColumnKeys as $columnKey) {
            if (! isset($columnDefinitions[$columnKey])) {
                continue;
            }

            $studentHeaders[] = $columnDefinitions[$columnKey]['label'];
        }

        if (empty($studentHeaders)) {
            Notification::make()->title('Chưa có cột hiển thị để xuất')->warning()->send();

            return null;
        }

        $studentRows = [];
        foreach ($records as $index => $record) {
            $studentRows[] = $this->mapRecordToExportRow($record, $index + 1, $visibleColumnKeys, $columnDefinitions);
        }

        $rows = [];
        $rows[] = ['Tổng quan khóa học'];

        foreach ($this->buildSummaryExportRows() as $summaryRow) {
            $rows[] = $summaryRow;
        }

        if (! empty($studentRows)) {
            $rows[] = [];
            $rows[] = ['Danh sách học viên hoàn thành'];
            $rows[] = $studentHeaders;

            foreach ($studentRows as $row) {
                $rows[] = $row;
            }
        }

        return Excel::download(new SimpleArrayExport($rows), 'hoc_vien_hoan_thanh.xlsx');
    }

    public function getSummaryRowsProperty(): Collection
    {
        $filters = $this->resolveFilterState();

        $courseQuery = KhoaHoc::query()
            ->with(['lichHocs' => function ($query) use ($filters) {
                $query->where('nam', $filters['year'])
                    ->when($filters['month'], fn ($q) => $q->where('thang', $filters['month']))
                    ->when($filters['week'], fn ($q) => $q->where('tuan', $filters['week']))
                    ->when($filters['from_date'], fn ($q) => $q->whereDate('ngay_hoc', '>=', $filters['from_date']))
                    ->when($filters['to_date'], fn ($q) => $q->whereDate('ngay_hoc', '<=', $filters['to_date']))
                    ->orderBy('ngay_hoc')
                    ->with('giangVien');
            }])
            ->whereHas('lichHocs', function (Builder $query) use ($filters) {
                $query->where('nam', $filters['year'])
                    ->when($filters['month'], fn ($q) => $q->where('thang', $filters['month']))
                    ->when($filters['week'], fn ($q) => $q->where('tuan', $filters['week']))
                    ->when($filters['from_date'], fn ($q) => $q->whereDate('ngay_hoc', '>=', $filters['from_date']))
                    ->when($filters['to_date'], fn ($q) => $q->whereDate('ngay_hoc', '<=', $filters['to_date']));
            });

        if (! empty($filters['training_types'])) {
            $courseQuery->where(function (Builder $builder) use ($filters) {
                HocVienHoanThanhResource::applyTrainingTypeFilter($builder, $filters['training_types']);
            });
        }

        if ($filters['course_id']) {
            $courseQuery->where('id', $filters['course_id']);
        }

        $courses = $courseQuery->orderBy('ma_khoa_hoc')->get();
        $courseIds = $courses->pluck('id');

        if ($courseIds->isEmpty()) {
            return collect();
        }

        $registrations = DangKy::whereIn('khoa_hoc_id', $courseIds)
            ->selectRaw('khoa_hoc_id, COUNT(*) as total')
            ->groupBy('khoa_hoc_id')
            ->pluck('total', 'khoa_hoc_id');

        $completed = HocVienHoanThanh::whereIn('khoa_hoc_id', $courseIds)
            ->selectRaw('khoa_hoc_id, COUNT(*) as total, SUM(COALESCE(chi_phi_dao_tao, 0)) as total_cost')
            ->groupBy('khoa_hoc_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->khoa_hoc_id => [
                    'total' => (int) $row->total,
                    'total_cost' => (float) $row->total_cost,
                ],
            ]);

        $failed = HocVienKhongHoanThanh::whereIn('khoa_hoc_id', $courseIds)
            ->selectRaw('khoa_hoc_id, COUNT(*) as total')
            ->groupBy('khoa_hoc_id')
            ->pluck('total', 'khoa_hoc_id');

        $rows = $courses->map(function (KhoaHoc $course) use ($registrations, $completed, $failed) {
            $lichHocs = $course->lichHocs;
            $totalHours = $lichHocs->sum(fn ($lich) => (float) ($lich->so_gio_giang ?? 0));
            $giangVien = $lichHocs->pluck('giangVien.ho_ten')->filter()->unique()->implode(', ');
            $dates = $lichHocs->pluck('ngay_hoc')->filter()->unique()->sort()->map(fn ($date) => Carbon::parse($date)->format('d/m/Y'))->implode("\n");

            return [
                'id' => $course->id,
                'ma_khoa' => $course->ma_khoa_hoc ?? '-',
                'ten_khoa' => $course->ten_khoa_hoc ?? '-',
                'trang_thai' => $course->trang_thai_hien_thi ?? '-',
                'tong_gio' => $totalHours > 0 ? number_format($totalHours, 1, '.', '') : '-',
                'giang_vien' => $giangVien ?: '-',
                'thoi_gian' => $dates ?: '-',
                'so_luong_hv' => (int) ($registrations[$course->id] ?? 0),
                'hoan_thanh' => (int) data_get($completed, $course->id . '.total', 0),
                'khong_hoan_thanh' => (int) ($failed[$course->id] ?? 0),
                'tong_thu' => (float) data_get($completed, $course->id . '.total_cost', 0),
                'ghi_chu' => $course->da_chuyen_ket_qua ? 'Đã khóa' : '-',
            ];
        })->filter(fn (array $row) => $row['so_luong_hv'] > 0)->values()->map(function (array $row, int $index) {
            $row['index'] = $index + 1;
            return $row;
        });

        return $rows;
    }

    public function getSummaryTotalsProperty(): array
    {
        $rows = $this->summaryRows;

        return [
            'so_luong_hv' => $rows->sum('so_luong_hv'),
            'hoan_thanh' => $rows->sum('hoan_thanh'),
            'khong_hoan_thanh' => $rows->sum('khong_hoan_thanh'),
            'tong_thu' => $rows->sum('tong_thu'),
        ];
    }

    public function selectCourseFromSummary(int $courseId): void
    {
        $current = $this->filterState['course_id'] ?? null;
        $newCourse = $current === $courseId ? null : $courseId;
        $this->filterCourseId = $newCourse;
        $this->setCourseFilter($newCourse);
    }

    public function getFilterStateProperty(): array
    {
        return $this->resolveFilterState();
    }

    protected function resolveFilterState(): array
    {
        $filters = data_get($this->tableFilters, 'bo_loc', []);

        if (is_array($filters) && array_key_exists('data', $filters) && is_array($filters['data'])) {
            $filters = $filters['data'];
        }

        $defaults = $this->defaultFilterState();

        $year = (int) ($filters['year'] ?? $defaults['year']);
        $month = isset($filters['month']) && $filters['month'] !== '' ? (int) $filters['month'] : $defaults['month'];
        $week = isset($filters['week']) && $filters['week'] !== '' ? (int) $filters['week'] : null;
        $courseId = isset($filters['course_id']) && $filters['course_id'] !== '' ? (int) $filters['course_id'] : null;
        $fromDate = $this->normalizeDate($filters['from_date'] ?? $defaults['from_date']);
        $toDate = $this->normalizeDate($filters['to_date'] ?? $defaults['to_date']);

        if ($fromDate && $toDate && $fromDate > $toDate) {
            $toDate = $fromDate;
        }

        $trainingTypes = $filters['training_types'] ?? $defaults['training_types'];
        if (is_string($trainingTypes)) {
            $trainingTypes = [$trainingTypes];
        }
        if (! is_array($trainingTypes)) {
            $trainingTypes = [];
        }
        $trainingTypes = collect($trainingTypes)
            ->filter(fn ($type) => $type !== null && $type !== '')
            ->map(fn ($type) => (string) $type)
            ->unique()
            ->values()
            ->all();

        return [
            'year' => $year,
            'month' => $month,
            'week' => $week,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'course_id' => $courseId,
            'training_types' => $trainingTypes,
        ];
    }

    protected function setCourseFilter(?int $courseId): void
    {
        $state = $this->resolveFilterState();
        $state['course_id'] = $courseId;
        $this->applyFilterState($state);
    }

    public function statusBadgeClass(?string $status): string
    {
        $slug = Str::slug($status ?? '');

        return match ($slug) {
            'tam-hoan' => 'bg-amber-100 text-amber-800',
            'ket-thuc' => 'bg-rose-100 text-rose-700',
            'dang-dao-tao' => 'bg-blue-100 text-blue-700',
            'ban-hanh' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getYearOptionsProperty(): array
    {
        return HocVienHoanThanhResource::getYearOptions();
    }

    public function getMonthOptionsProperty(): array
    {
        return HocVienHoanThanhResource::getMonthOptions($this->filterYear ?? now()->year);
    }

    public function getWeekOptionsProperty(): array
    {
        return HocVienHoanThanhResource::getWeekOptions($this->filterYear ?? now()->year, $this->filterMonth);
    }

    public function getCourseOptionsProperty(): array
    {
        return HocVienHoanThanhResource::getCourseOptions(
            $this->filterYear ?? now()->year,
            $this->filterMonth,
            $this->filterWeek,
            $this->filterFromDate,
            $this->filterToDate,
            $this->filterTrainingTypes
        );
    }

    public function getTrainingTypeOptionsProperty(): array
    {
        return HocVienHoanThanhResource::getTrainingTypeOptions();
    }

    public function updatedFilterYear($value): void
    {
        $year = (int) ($value ?: now()->year);
        $this->filterYear = $year;
        $this->filterWeek = null;
        if ($this->filterMonth === null) {
            $this->filterMonth = now()->month;
        }

        $this->updateFilter('year', $year);
    }

    public function updatedFilterMonth($value): void
    {
        $month = $value !== '' && $value !== null ? (int) $value : null;
        $this->filterMonth = $month;
        $this->filterWeek = null;
        $this->updateFilter('month', $month);
    }

    public function updatedFilterWeek($value): void
    {
        $week = $value !== '' && $value !== null ? (int) $value : null;
        $this->filterWeek = $week;
        $this->updateFilter('week', $week);
    }

    public function updatedFilterFromDate($value): void
    {
        $from = $this->normalizeDate($value);
        $this->filterFromDate = $from;

        if ($from && $this->filterToDate && $from > $this->filterToDate) {
            $this->filterToDate = $from;
        }

        $this->updateFilter('from_date', $from);
    }

    public function updatedFilterToDate($value): void
    {
        $to = $this->normalizeDate($value);
        $this->filterToDate = $to;

        if ($to && $this->filterFromDate && $to < $this->filterFromDate) {
            $this->filterFromDate = $to;
        }

        $this->updateFilter('to_date', $to);
    }

    public function updatedFilterCourseId($value): void
    {
        $course = $value !== '' && $value !== null ? (int) $value : null;
        $this->filterCourseId = $course;
        $this->updateFilter('course_id', $course);
    }

    public function updatedFilterTrainingTypes($value): void
    {
        $types = collect($value ?? [])
            ->filter(fn ($item) => $item !== null && $item !== '')
            ->map(fn ($item) => (string) $item)
            ->unique()
            ->values()
            ->all();

        $this->filterTrainingTypes = $types;
        $this->updateFilter('training_types', $types);
    }

    protected function updateFilter(string $key, $value): void
    {
        $state = $this->resolveFilterState();
        $state[$key] = $value;

        if ($key === 'year') {
            $state['year'] = (int) $value ?: now()->year;
        }

        if ($key === 'month' && $value === null) {
            $state['month'] = null;
        }

        $this->applyFilterState($state);
    }

    protected function defaultFilterState(): array
    {
        $now = now();

        return [
            'year' => $now->year,
            'month' => $now->month,
            'week' => null,
            'from_date' => null,
            'to_date' => null,
            'course_id' => null,
            'training_types' => [],
        ];
    }

    protected function applyFilterState(array $state, bool $resetInputs = true): void
    {
        $data = [
            'year' => (string) $state['year'],
            'month' => $state['month'] ? (string) $state['month'] : null,
            'week' => $state['week'] ? (string) $state['week'] : null,
            'from_date' => $state['from_date'],
            'to_date' => $state['to_date'],
            'course_id' => $state['course_id'] ? (string) $state['course_id'] : null,
            'training_types' => $state['training_types'],
        ];

        $this->tableFilters['bo_loc'] = [
            'isActive' => (bool) collect($data)
                ->reject(fn ($value, $key) => in_array($key, ['year', 'month'], true))
                ->reject(fn ($value) => $value === null || $value === '' || (is_array($value) && empty($value)))
                ->count(),
            'data' => $data,
        ];

        if ($resetInputs) {
            $this->syncFilterInputsFromState($state);
        }

        if (method_exists($this, 'resetTablePage')) {
            $this->resetTablePage();
        }
    }

    protected function syncFilterInputsFromState(array $state): void
    {
        $this->filterYear = (int) $state['year'];
        $this->filterMonth = $state['month'] ? (int) $state['month'] : null;
        $this->filterWeek = $state['week'] ? (int) $state['week'] : null;
        $this->filterFromDate = $state['from_date'];
        $this->filterToDate = $state['to_date'];
        $this->filterCourseId = $state['course_id'];
        $this->filterTrainingTypes = $state['training_types'];
    }

    protected function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function buildSummaryExportRows(): array
    {
        $header = [
            'TT',
            'Mã khóa',
            'Tên khóa học',
            'Trạng thái',
            'Tổng số giờ',
            'Giảng viên',
            'Thời gian đào tạo',
            'Số lượng HV',
            'Hoàn thành',
            'Không hoàn thành',
            'Tổng thu',
            'Ghi chú',
        ];

        $rows = [$header];

        foreach ($this->summaryRows as $row) {
            $rows[] = [
                $row['index'],
                $row['ma_khoa'],
                $row['ten_khoa'],
                $row['trang_thai'],
                $row['tong_gio'],
                $row['giang_vien'],
                str_replace("\n", ', ', $row['thoi_gian'] ?? ''),
                number_format($row['so_luong_hv'] ?? 0, 0, ',', '.'),
                number_format($row['hoan_thanh'] ?? 0, 0, ',', '.'),
                number_format($row['khong_hoan_thanh'] ?? 0, 0, ',', '.'),
                ($row['tong_thu'] ?? 0) > 0
                    ? number_format((float) $row['tong_thu'], 0, ',', '.')
                    : '-',
                $row['ghi_chu'] ?? '-',
            ];
        }

        if ($this->summaryRows->isNotEmpty()) {
            $totals = $this->summaryTotals;

            $rows[] = [
                '',
                '',
                '',
                '',
                '',
                '',
                'Tổng cộng',
                number_format($totals['so_luong_hv'] ?? 0, 0, ',', '.'),
                number_format($totals['hoan_thanh'] ?? 0, 0, ',', '.'),
                number_format($totals['khong_hoan_thanh'] ?? 0, 0, ',', '.'),
                ($totals['tong_thu'] ?? 0) > 0
                    ? number_format((float) $totals['tong_thu'], 0, ',', '.')
                    : '-',
                '',
            ];
        }

        return $rows;
    }

    protected function getVisibleColumnKeys(): array
    {
        try {
            $columns = collect($this->getTableColumns());
        } catch (\Throwable $exception) {
            return array_keys($this->getExportColumnDefinitions());
        }

        $toggleState = [];

        if (method_exists($this, 'getTableColumnToggleForm')) {
            try {
                $form = $this->getTableColumnToggleForm();
                if ($form && method_exists($form, 'getState')) {
                    $toggleState = (array) $form->getState();
                }
            } catch (\Throwable $exception) {
                $toggleState = [];
            }
        }

        $keys = $columns
            ->filter(function ($column) use ($toggleState) {
                if (! method_exists($column, 'getName')) {
                    return false;
                }

                $name = $column->getName();

                if (method_exists($column, 'isHidden') && $column->isHidden()) {
                    return false;
                }

                if (method_exists($column, 'isToggleable') && $column->isToggleable()) {
                    if (array_key_exists($name, $toggleState)) {
                        return (bool) $toggleState[$name];
                    }

                    if (method_exists($column, 'isToggledHiddenByDefault') && $column->isToggledHiddenByDefault()) {
                        return false;
                    }
                }

                return true;
            })
            ->map(fn ($column) => $column->getName())
            ->filter()
            ->values()
            ->all();

        if (empty($keys)) {
            $keys = array_keys($this->getExportColumnDefinitions());
        }

        return $keys;
    }

    /**
     * @return array<string, array{label: string, value: callable}>
     */
    protected function getExportColumnDefinitions(): array
    {
        return [
            'index' => [
                'label' => 'TT',
                'value' => fn (HocVienHoanThanh $record, int $index): string => (string) $index,
            ],
            'hocVien.msnv' => [
                'label' => 'MS',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->msnv),
            ],
            'hocVien.ho_ten' => [
                'label' => 'Họ & Tên',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->ho_ten),
            ],
            'hocVien.nam_sinh' => [
                'label' => 'Năm sinh',
                'value' => function (HocVienHoanThanh $record, int $index): string {
                    $date = $record->hocVien?->nam_sinh;

                    return $date ? Carbon::parse($date)->format('d/m/Y') : '-';
                },
            ],
            'hocVien.gioi_tinh' => [
                'label' => 'Giới tính',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->gioi_tinh),
            ],
            'hocVien.chuc_vu' => [
                'label' => 'Chức vụ',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->chuc_vu),
            ],
            'hocVien.donVi.phong_bo_phan' => [
                'label' => 'Phòng/Bộ phận',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->donVi?->phong_bo_phan),
            ],
            'hocVien.donVi.cong_ty_ban_nvqt' => [
                'label' => 'Công ty/Ban NVQT',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->donVi?->cong_ty_ban_nvqt),
            ],
            'hocVien.donVi.thaco_tdtv' => [
                'label' => 'THACO/TĐTV',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->donVi?->thaco_tdtv),
            ],
            'hocVien.donViPhapNhan.ten_don_vi' => [
                'label' => 'Đơn vị pháp nhân/trả lương',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->hocVien?->donViPhapNhan?->ten_don_vi),
            ],
            'khoaHoc.ten_khoa_hoc' => [
                'label' => 'Tên khóa học',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->khoaHoc?->ten_khoa_hoc),
            ],
            'khoaHoc.ma_khoa_hoc' => [
                'label' => 'Mã khóa',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->khoaHoc?->ma_khoa_hoc),
            ],
            'loai_hinh_dao_tao' => [
                'label' => 'Loại hình đào tạo',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::trainingTypeText($record),
            ],
            'ketQua.diem_trung_binh' => [
                'label' => 'ĐTB',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::decimalOrDash($record->ketQua?->diem_trung_binh),
            ],
            'ketQua.tong_so_gio_thuc_te' => [
                'label' => 'Giờ thực học',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::decimalOrDash($record->ketQua?->tong_so_gio_thuc_te),
            ],
            'ngay_hoan_thanh' => [
                'label' => 'Ngày hoàn thành',
                'value' => function (HocVienHoanThanh $record, int $index): string {
                    return $record->ngay_hoan_thanh ? Carbon::parse($record->ngay_hoan_thanh)->format('d/m/Y') : '-';
                },
            ],
            'chi_phi_dao_tao' => [
                'label' => 'Chi phí đào tạo',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::currencyOrDash($record->chi_phi_dao_tao),
            ],
            'so_chung_nhan' => [
                'label' => 'Số chứng nhận',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->so_chung_nhan),
            ],
            'certificate_links' => [
                'label' => 'File/Link Chứng nhận',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::certificatePlainText($record),
            ],
            'ngay_het_han_chung_nhan' => [
                'label' => 'Ngày hết hạn',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::expiryText($record),
            ],
            'ketQua.danh_gia_ren_luyen' => [
                'label' => 'Đánh giá rèn luyện',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->ketQua?->danh_gia_ren_luyen),
            ],
            'ketQua.ket_qua' => [
                'label' => 'Kết quả',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::resultText($record->ketQua?->ket_qua),
            ],
            'ghi_chu' => [
                'label' => 'Ghi chú',
                'value' => fn (HocVienHoanThanh $record, int $index): string => HocVienHoanThanhResource::textOrDash($record->ghi_chu),
            ],
        ];
    }

    /**
     * @param  array<string, array{label: string, value: callable}>  $definitions
     * @return array<int, string>
     */
    protected function mapRecordToExportRow(HocVienHoanThanh $record, int $index, array $visibleColumnKeys, array $definitions): array
    {
        $row = [];

        foreach ($visibleColumnKeys as $columnKey) {
            if (! isset($definitions[$columnKey])) {
                continue;
            }

            $resolver = $definitions[$columnKey]['value'];

            $row[] = (string) $resolver($record, $index);
        }

        return $row;
    }

    protected function getExportCollection(): Collection
    {
        $query = clone $this->getTableQuery();

        return $query->get();
    }
}
