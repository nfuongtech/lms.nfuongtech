<?php

namespace App\Filament\Resources\HocVienHoanThanhResource\Pages;

use App\Exports\SimpleArrayExport;
use App\Filament\Resources\HocVienHoanThanhResource;
use App\Mail\PlanNotificationMail;
use App\Models\DangKy;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ListHocVienHoanThanhs extends ListRecords
{
    protected static string $resource = HocVienHoanThanhResource::class;

    protected static ?string $title = 'Học viên hoàn thành';

    /** Không render heading trên Blade để tránh trùng title */
    protected ?string $heading = null;

    protected static string $view = 'filament.resources.hoc-vien-hoan-thanh-resource.pages.list-hoc-vien-hoan-thanhs';

    /** Trạng thái filter của bảng */
    public ?array $tableFilters = [];

    /* ==========================================================
     |  ACTIONS
     |  - Đăng ký actions qua getHeaderActions() để Filament mount
     |  - Trên Blade, render lại đúng thứ tự và ẩn header mặc định
     * ========================================================== */

    /** Mount actions vào Page (để action chạy được) */
    protected function getHeaderActions(): array
    {
        return $this->makePageActions();
    }

    /** (Giữ lại) Cho phép component khác đọc được mảng actions nếu cần */
    public function getActions(): array
    {
        return $this->makePageActions();
    }

    /** Sắp xếp đúng thứ tự theo yêu cầu */
    protected function makePageActions(): array
    {
        return [
            // 1) Tải mẫu import
            Actions\Action::make('download_template')
                ->label('Tải mẫu import')
                ->extraAttributes([
                    'class' => 'fi-btn fi-btn-sm border border-gray-300 bg-white text-black hover:bg-gray-50',
                    'style' => 'color:#000000;',
                ])
                ->action(fn () => Excel::download(new SimpleArrayExport([], [
                    'MS','Họ & Tên','Tên khóa học','Mã khóa','ĐTB','Giờ thực học','Ngày hoàn thành',
                    'Chi phí đào tạo','Số chứng nhận','Link chứng nhận','Thời hạn chứng nhận (năm)',
                    'Ngày hết hạn chứng nhận','Ghi chú',
                ]), 'mau_hoc_vien_hoan_thanh.xlsx')),

            // 2) Import
            Actions\Action::make('import_excel')
                ->label('Import')
                ->extraAttributes([
                    'class' => 'fi-btn fi-btn-sm border border-gray-300 bg-white text-black hover:bg-gray-50',
                    'style' => 'color:#000000;',
                ])
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Chọn file Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    $file = $data['file'] ?? null;

                    if (! $file instanceof TemporaryUploadedFile) {
                        Notification::make()->title('Không tìm thấy file tải lên')->danger()->send();
                        return;
                    }

                    $rows = HocVienHoanThanhResource::parseImportRows($file->getRealPath());

                    if (empty($rows)) {
                        Notification::make()->title('File không có dữ liệu hợp lệ')->warning()->send();
                        return;
                    }

                    $imported = 0;
                    $errors = [];

                    foreach ($rows as $index => $row) {
                        $result = HocVienHoanThanhResource::handleImportRow($row);

                        if (! empty($result['errors'])) {
                            foreach ($result['errors'] as $message) {
                                $errors[] = 'Dòng ' . ($index + 2) . ': ' . $message;
                            }
                            continue;
                        }

                        $imported++;
                    }

                    if (! empty($errors)) {
                        Notification::make()
                            ->title('Có lỗi khi import')
                            ->body(implode("\n", array_slice($errors, 0, 10)))
                            ->danger()
                            ->send();
                    }

                    Notification::make()
                        ->title('Đã import dữ liệu')
                        ->body('Số dòng cập nhật: ' . $imported . (empty($errors) ? '' : '. Có ' . count($errors) . ' lỗi.'))
                        ->success()
                        ->send();
                }),

            // 3) Xuất Excel
            Actions\Action::make('export_excel')
                ->label('Xuất Excel')
                ->extraAttributes([
                    'style' => 'background-color:#CCFFD8;color:#00529C;',
                    'class' => 'fi-btn fi-btn-sm border border-gray-200',
                ])
                ->action(fn () => $this->exportCurrentView()),

            // 4) Gửi Email
            Actions\Action::make('send_email')
                ->label('Gửi Email')
                ->extraAttributes([
                    'style' => 'background-color:#FFFCD5;color:#00529C;',
                    'class' => 'fi-btn fi-btn-sm border border-gray-200',
                ])
                ->form([
                    Forms\Components\Select::make('email_template_id')
                        ->label('Mẫu email')
                        ->options(fn () => EmailTemplate::orderBy('ten_mau')->pluck('ten_mau', 'id')->toArray())
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('email_account_id')
                        ->label('Tài khoản gửi')
                        ->options(fn () => EmailAccount::where('active', 1)->orderBy('name')->pluck('name', 'id')->toArray())
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $records = $this->getExportCollection();

                    if ($records->isEmpty()) {
                        Notification::make()->title('Không có học viên để gửi email')->warning()->send();
                        return;
                    }

                    $template = EmailTemplate::find($data['email_template_id'] ?? null);
                    $account  = EmailAccount::find($data['email_account_id'] ?? null);

                    if (! $template || ! $account) {
                        Notification::make()->title('Thiếu thông tin gửi email')->danger()->send();
                        return;
                    }

                    Config::set('mail.mailers.dynamic', [
                        'transport' => 'smtp',
                        'host' => $account->host,
                        'port' => $account->port,
                        'encryption' => $account->encryption_tls ? 'tls' : null,
                        'username' => $account->username,
                        'password' => $account->password,
                    ]);

                    Config::set('mail.from', [
                        'address' => $account->email,
                        'name' => $account->name,
                    ]);

                    $success = 0;
                    $failed  = 0;

                    foreach ($records as $record) {
                        $hocVien   = $record->hocVien;
                        $ketQua    = $record->ketQua;
                        $course    = $record->khoaHoc;
                        $recipient = $hocVien?->email;

                        if (! $recipient) {
                            $failed++;
                            EmailLog::create([
                                'email_account_id' => $account->id,
                                'recipient_email'  => 'N/A',
                                'subject'          => 'Không gửi (thiếu email học viên)',
                                'content'          => '',
                                'status'           => 'failed',
                                'error_message'    => 'Học viên không có email.',
                            ]);
                            continue;
                        }

                        $placeholders = [
                            '{ten_hoc_vien}'  => $hocVien?->ho_ten ?? 'N/A',
                            '{msnv}'          => $hocVien?->msnv ?? 'N/A',
                            '{ten_khoa_hoc}'  => $course?->ten_khoa_hoc ?? 'N/A',
                            '{ma_khoa_hoc}'   => $course?->ma_khoa_hoc ?? 'N/A',
                            '{diem_tb}'       => $ketQua?->diem_trung_binh ? number_format((float) $ketQua->diem_trung_binh, 1, '.', '') : '-',
                            '{gio_thuc_hoc}'  => $ketQua?->tong_so_gio_thuc_te ? number_format((float) $ketQua->tong_so_gio_thuc_te, 1, '.', '') : '-',
                            '{ket_qua}'       => $ketQua && $ketQua->ket_qua === 'hoan_thanh' ? 'Hoàn thành' : 'Không hoàn thành',
                        ];

                        $subject = strtr($template->tieu_de, $placeholders);
                        $body    = strtr($template->noi_dung, $placeholders);

                        try {
                            Mail::mailer('dynamic')->to($recipient)->send(new PlanNotificationMail($subject, $body));
                            $success++; $status = 'success'; $error = null;
                        } catch (\Throwable $exception) {
                            $failed++; $status = 'failed'; $error = $exception->getMessage();
                            Log::error('Lỗi gửi email học viên hoàn thành: ' . $exception->getMessage());
                        }

                        EmailLog::create([
                            'email_account_id' => $account->id,
                            'recipient_email'  => $recipient,
                            'subject'          => $subject,
                            'content'          => $body,
                            'status'           => $status,
                            'error_message'    => $error,
                        ]);
                    }

                    Notification::make()
                        ->title('Gửi email hoàn tất')
                        ->body("Thành công: {$success}. Thất bại: {$failed}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    /* ===================== LIFECYCLE ===================== */

    public function mount(): void
    {
        parent::mount();
        $this->applyFilterState($this->defaultFilterState());
    }

    /* ===================== EXPORT ===================== */

    public function exportCurrentView()
    {
        $records = $this->getExportCollection();

        if ($records->isEmpty()) {
            Notification::make()->title('Không có dữ liệu để xuất')->warning()->send();
            return null;
        }

        $columns = [];

        if (method_exists($this, 'getVisibleTableColumns')) {
            $columns = $this->getVisibleTableColumns();
        } elseif (method_exists($this, 'getCachedTableColumns')) {
            $columns = $this->getCachedTableColumns();
        } elseif (method_exists($this, 'getTableColumns')) {
            $columns = $this->getTableColumns();
        }

        $visibleColumns = collect($columns)
            ->filter(function ($column) {
                if (method_exists($column, 'isHidden') && $column->isHidden()) return false;
                if (method_exists($column, 'isVisible')) return $column->isVisible();
                return true;
            })
            ->map(function ($column) {
                $name  = method_exists($column, 'getName') ? $column->getName()
                    : (method_exists($column, 'getColumn') ? $column->getColumn() : null);
                $label = method_exists($column, 'getLabel') ? $column->getLabel()
                    : ($name ? Str::of($name)->headline()->toString() : '');
                return ['name' => $name, 'label' => $label];
            })
            ->values()->all();

        return HocVienHoanThanhResource::exportWithSummary(
            $this->summaryRows,
            $records,
            $visibleColumns,
            $this->resolveFilterState(),
            $this->summaryTotals,
            'hoc_vien_hoan_thanh.xlsx'
        );
    }

    /* ====== OPTION LISTS TỪ KẾ HOẠCH ĐÀO TẠO / QUY TẮC MÃ KHÓA ====== */

    public function getAvailableYearsProperty(): array
    {
        try {
            return DB::table('lich_hocs')->distinct()->orderByDesc('nam')->pluck('nam')->map(fn($v)=>(int)$v)->toArray();
        } catch (\Throwable $e) {
            return [now()->year];
        }
    }

    public function getAvailableMonthsProperty(): array
    {
        $year = $this->resolveFilterState()['year'] ?? now()->year;
        try {
            return DB::table('lich_hocs')->where('nam', $year)->distinct()->orderBy('thang')->pluck('thang')->map(fn($v)=>(int)$v)->toArray();
        } catch (\Throwable $e) {
            return range(1, 12);
        }
    }

    public function getAvailableWeeksProperty(): array
    {
        $state = $this->resolveFilterState();
        $q = DB::table('lich_hocs')->where('nam', $state['year']);
        if (!empty($state['month'])) $q->where('thang', $state['month']);

        try {
            return $q->distinct()->orderBy('tuan')->pluck('tuan')->map(fn($v)=>(int)$v)->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getTrainingTypeOptions(): array
    {
        $options = HocVienHoanThanhResource::getTrainingTypeOptions();

        return collect($options)
            ->filter(fn ($label, $value) => ($label ?? '') !== '')
            ->mapWithKeys(function ($label, $value) {
                $label = trim((string) $label);
                $value = trim((string) $value);

                return [$value => $label];
            })
            ->unique()
            ->sort()
            ->toArray();
    }

    public function getCourseFilterOptionsProperty(): array
    {
        $filters = $this->resolveFilterState();
        $filters['course_id'] = null;

        return $this->makeSummaryCourseQuery($filters, false)
            ->orderBy('ma_khoa_hoc')
            ->get()
            ->mapWithKeys(function (KhoaHoc $course) {
                $label = trim(implode(' - ', array_filter([
                    $course->ma_khoa_hoc,
                    $course->ten_khoa_hoc,
                ])));

                return [$course->id => $label ?: ($course->ma_khoa_hoc ?? (string) $course->id)];
            })
            ->toArray();
    }

    public function getSelectedCourseIdProperty(): ?int
    {
        $courseId = data_get($this->tableFilters, 'bo_loc.data.course_id');

        if ($courseId === null || $courseId === '') {
            return null;
        }

        return (int) $courseId;
    }

    /* ===================== TÓM TẮT (bảng trên) ===================== */

    protected function makeSummaryCourseQuery(array $filters, bool $respectSelectedCourses = true): Builder
    {
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

        $trainingTypes = $filters['training_types'] ?? [];
        if (! empty($trainingTypes)) {
            $courseQuery->where(function (Builder $builder) use ($trainingTypes) {
                HocVienHoanThanhResource::applyTrainingTypeFilter($builder, $trainingTypes);
            });
        }

        if ($filters['course_id']) {
            $courseQuery->where('id', $filters['course_id']);
        }

        return $courseQuery;
    }

    public function getSummaryRowsProperty(): Collection
    {
        $filters = $this->resolveFilterState();

        $courses = $this->makeSummaryCourseQuery($filters)
            ->orderBy('ma_khoa_hoc')
            ->get();
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
            $lichHocs   = $course->lichHocs;
            $totalHours = $lichHocs->sum(fn ($lich) => (float) ($lich->so_gio_giang ?? 0));
            $giangVien  = $lichHocs->pluck('giangVien.ho_ten')->filter()->unique()->implode(', ');
            $dates      = $lichHocs->pluck('ngay_hoc')->filter()->unique()->sort()
                ->map(fn ($d) => Carbon::parse($d)->format('d/m/Y'))->implode("\n");

            return [
                'id'                => $course->id,
                'ma_khoa'           => $course->ma_khoa_hoc ?? '-',
                'ten_khoa'          => $course->ten_khoa_hoc ?? '-',
                'trang_thai'        => $course->trang_thai_hien_thi ?? '-',
                'tong_gio'          => $totalHours > 0 ? number_format($totalHours, 1, '.', '') : '-',
                'giang_vien'        => $giangVien ?: '-',
                'thoi_gian'         => $dates ?: '-',
                'so_luong_hv'       => (int) ($registrations[$course->id] ?? 0),
                'hoan_thanh'        => (int) data_get($completed, $course->id . '.total', 0),
                'khong_hoan_thanh'  => (int) ($failed[$course->id] ?? 0),
                'tong_thu'          => (float) data_get($completed, $course->id . '.total_cost', 0),
                'ghi_chu'           => $course->da_chuyen_ket_qua ? 'Đã khóa' : '-',
            ];
        })->filter(fn (array $row) => $row['so_luong_hv'] > 0)->values()
          ->map(function (array $row, int $index) { $row['index'] = $index + 1; return $row; });

        return $rows;
    }

    public function getSummaryTotalsProperty(): array
    {
        $rows = $this->summaryRows;

        return [
            'so_luong_hv'      => $rows->sum('so_luong_hv'),
            'hoan_thanh'       => $rows->sum('hoan_thanh'),
            'khong_hoan_thanh' => $rows->sum('khong_hoan_thanh'),
            'tong_thu'         => $rows->sum('tong_thu'),
        ];
    }

    /* ===================== UI EVENTS ===================== */

    public function selectCourseFromSummary(int $courseId): void
    {
        $state = $this->resolveFilterState();
        $state['course_id'] = $state['course_id'] === $courseId ? null : $courseId;

        $this->applyFilterState($state);
    }

    public function handleYearChange($value): void
    {
        $state = $this->resolveFilterState();
        $state['year'] = (int) ($value ?: now()->year);
        $state['month'] = null;
        $state['week'] = null;
        $state['course_id'] = null;

        $this->applyFilterState($state);
    }

    public function handleMonthChange($value): void
    {
        $state = $this->resolveFilterState();
        $state['month'] = $value === '' ? null : (int) $value;
        $state['week'] = null;
        $state['course_id'] = null;

        $this->applyFilterState($state);
    }

    public function handleWeekChange($value): void
    {
        $state = $this->resolveFilterState();
        $state['week'] = $value === '' ? null : (int) $value;

        $this->applyFilterState($state);
    }

    public function handleCourseChange($value): void
    {
        $state = $this->resolveFilterState();
        $state['course_id'] = $value === '' ? null : (int) $value;

        $this->applyFilterState($state);
    }

    public function handleFromDateChange($value): void
    {
        $state = $this->resolveFilterState();
        $state['from_date'] = $this->normalizeDate($value);

        if ($state['from_date'] && $state['to_date'] && $state['from_date'] > $state['to_date']) {
            $state['to_date'] = $state['from_date'];
        }

        $this->applyFilterState($state);
    }

    public function handleToDateChange($value): void
    {
        $state = $this->resolveFilterState();
        $state['to_date'] = $this->normalizeDate($value);

        if ($state['from_date'] && $state['to_date'] && $state['from_date'] > $state['to_date']) {
            $state['from_date'] = $state['to_date'];
        }

        $this->applyFilterState($state);
    }

    public function toggleTrainingType(string $value): void
    {
        $state = $this->resolveFilterState();
        $value = (string) $value;

        $types = collect($state['training_types'] ?? [])
            ->filter(fn ($type) => $type !== null && $type !== '')
            ->map(fn ($type) => (string) $type)
            ->values();

        if ($types->contains($value)) {
            $types = $types->reject(fn ($type) => $type === $value)->values();
        } else {
            $types->push($value);
        }

        $state['training_types'] = $types->unique()->values()->all();

        $this->applyFilterState($state);
    }

    public function selectAllTrainingTypes(): void
    {
        $state = $this->resolveFilterState();
        $state['training_types'] = array_values(array_map('strval', array_keys($this->getTrainingTypeOptions())));

        $this->applyFilterState($state);
    }

    public function clearTrainingTypeFilters(): void
    {
        $state = $this->resolveFilterState();
        $state['training_types'] = [];

        $this->applyFilterState($state);
    }

    /* ===================== STATE HELPERS ===================== */

    protected function resolveFilterState(): array
    {
        $filters = data_get($this->tableFilters, 'bo_loc', []);

        if (is_array($filters) && array_key_exists('data', $filters) && is_array($filters['data'])) {
            $filters = $filters['data'];
        }

        $defaults = $this->defaultFilterState();

        $year = (int) ($filters['year'] ?? $defaults['year']);

        $month = $defaults['month'];
        if (array_key_exists('month', (array) $filters)) {
            $value = $filters['month'];
            $month = ($value === null || $value === '') ? null : (int) $value;
        }

        $week = null;
        if (array_key_exists('week', (array) $filters) && $filters['week'] !== '' && $filters['week'] !== null) {
            $week = (int) $filters['week'];
        }

        $courseId = isset($filters['course_id']) && $filters['course_id'] !== '' ? (int) $filters['course_id'] : null;

        $fromDate = $this->normalizeDate($filters['from_date'] ?? $defaults['from_date']);
        $toDate   = $this->normalizeDate($filters['to_date'] ?? $defaults['to_date']);
        if ($fromDate && $toDate && $fromDate > $toDate) {
            $toDate = $fromDate;
        }

        $trainingTypes = $filters['training_types'] ?? $defaults['training_types'];
        if (is_string($trainingTypes)) $trainingTypes = [$trainingTypes];
        if (! is_array($trainingTypes)) $trainingTypes = [];
        $trainingTypes = collect($trainingTypes)
            ->filter(fn ($type) => $type !== null && $type !== '')
            ->map(fn ($type) => (string) $type)
            ->unique()
            ->values()
            ->all();

        return [
            'year'           => $year,
            'month'          => $month,
            'week'           => $week,
            'from_date'      => $fromDate,
            'to_date'        => $toDate,
            'course_id'      => $courseId,
            'training_types' => $trainingTypes,
        ];
    }

    protected function defaultFilterState(): array
    {
        $now = now();

        return [
            'year'           => $now->year,
            'month'          => $now->month,
            'week'           => null,
            'from_date'      => null,
            'to_date'        => null,
            'course_id'      => null,
            'training_types' => [],
        ];
    }

    protected function applyFilterState(array $state): void
    {
        $data = [
            'year'           => (string) $state['year'],
            'month'          => $state['month'] ? (string) $state['month'] : null,
            'week'           => $state['week'] ? (string) $state['week'] : null,
            'from_date'      => $state['from_date'],
            'to_date'        => $state['to_date'],
            'course_id'      => $state['course_id'] ? (string) $state['course_id'] : null,
            'training_types' => $state['training_types'],
        ];

        $this->tableFilters['bo_loc'] = [
            'isActive' => (bool) collect($data)
                ->reject(fn ($value, $key) => in_array($key, ['year', 'month'], true))
                ->reject(fn ($value) => $value === null || $value === '' || (is_array($value) && empty($value)))
                ->count(),
            'data' => $data,
        ];

        if (method_exists($this, 'resetTablePage')) {
            $this->resetTablePage();
        }
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

    protected function getExportCollection(): Collection
    {
        $query = clone $this->getTableQuery();

        return $query->get();
    }

    /** Lọc bảng dưới theo các course đã chọn */
    protected function getTableQuery(): Builder
    {
        $query = static::getResource()::getEloquentQuery();

        $filters = $this->resolveFilterState();

        if (! empty($filters['course_id'])) {
            $query->where('khoa_hoc_id', $filters['course_id']);
        }

        // Áp dụng phạm vi thời gian vào bảng dưới nếu cần:
        if (!empty($filters['from_date']) || !empty($filters['to_date'])) {
            $from = $filters['from_date'];
            $to   = $filters['to_date'];
            $query->when($from, fn($q)=>$q->whereDate('ngay_hoan_thanh','>=',$from))
                  ->when($to,   fn($q)=>$q->whereDate('ngay_hoan_thanh','<=',$to));
        }

        return $query;
    }

    /** Badge trạng thái cho Tổng quan */
    public function statusBadgeClass(?string $status): string
    {
        $slug = Str::slug($status ?? '');

        return match ($slug) {
            'tam-hoan'      => 'bg-amber-100 text-amber-800',
            'ket-thuc'      => 'bg-rose-100 text-rose-700',
            'dang-dao-tao'  => 'bg-blue-100 text-blue-700',
            'ban-hanh'      => 'bg-emerald-100 text-emerald-700',
            default         => 'bg-gray-100 text-gray-700',
        };
    }
}
