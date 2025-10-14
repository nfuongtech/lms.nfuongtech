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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ListHocVienHoanThanhs extends ListRecords
{
    protected static string $resource = HocVienHoanThanhResource::class;

    protected static ?string $title = 'Học viên hoàn thành';

    protected ?string $heading = null;

    protected static string $view = 'filament.resources.hoc-vien-hoan-thanh-resource.pages.list-hoc-vien-hoan-thanhs';

    public array $tableFilters = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_template')
                ->label('Tải mẫu import')
                ->extraAttributes([
                    'class' => 'fi-btn fi-btn-sm border border-gray-300 bg-white text-black hover:bg-gray-50',
                    'style' => 'color:#000000;',
                ])
                ->action(function () {
                    $headings = [
                        'MS',
                        'Họ & Tên',
                        'Tên khóa học',
                        'Mã khóa',
                        'ĐTB',
                        'Giờ thực học',
                        'Ngày hoàn thành',
                        'Chi phí đào tạo',
                        'Số chứng nhận',
                        'Link chứng nhận',
                        'Thời hạn chứng nhận (năm)',
                        'Ngày hết hạn chứng nhận',
                        'Ghi chú',
                    ];

                    return Excel::download(new SimpleArrayExport([], $headings), 'mau_hoc_vien_hoan_thanh.xlsx');
                }),

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

            Actions\Action::make('export_excel')
                ->label('Xuất Excel')
                ->extraAttributes([
                    'style' => 'background-color:#CCFFD8;color:#00529C;',
                    'class' => 'fi-btn fi-btn-sm border border-gray-200',
                ])
                ->action(function () {
                    return $this->exportCurrentView();
                }),

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
                    $account = EmailAccount::find($data['email_account_id'] ?? null);

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
                    $failed = 0;

                    foreach ($records as $record) {
                        $hocVien = $record->hocVien;
                        $ketQua = $record->ketQua;
                        $course = $record->khoaHoc;

                        $recipient = $hocVien?->email;

                        if (! $recipient) {
                            $failed++;

                            EmailLog::create([
                                'email_account_id' => $account->id,
                                'recipient_email' => 'N/A',
                                'subject' => 'Không gửi (thiếu email học viên)',
                                'content' => '',
                                'status' => 'failed',
                                'error_message' => 'Học viên không có email.',
                            ]);

                            continue;
                        }

                        $placeholders = [
                            '{ten_hoc_vien}' => $hocVien?->ho_ten ?? 'N/A',
                            '{msnv}' => $hocVien?->msnv ?? 'N/A',
                            '{ten_khoa_hoc}' => $course?->ten_khoa_hoc ?? 'N/A',
                            '{ma_khoa_hoc}' => $course?->ma_khoa_hoc ?? 'N/A',
                            '{diem_tb}' => $ketQua?->diem_trung_binh ? number_format((float) $ketQua->diem_trung_binh, 1, '.', '') : '-',
                            '{gio_thuc_hoc}' => $ketQua?->tong_so_gio_thuc_te ? number_format((float) $ketQua->tong_so_gio_thuc_te, 1, '.', '') : '-',
                            '{ket_qua}' => $ketQua && $ketQua->ket_qua === 'hoan_thanh' ? 'Hoàn thành' : 'Không hoàn thành',
                        ];

                        $subject = strtr($template->tieu_de, $placeholders);
                        $body = strtr($template->noi_dung, $placeholders);

                        try {
                            Mail::mailer('dynamic')->to($recipient)->send(new PlanNotificationMail($subject, $body));
                            $success++;
                            $status = 'success';
                            $error = null;
                        } catch (\Throwable $exception) {
                            $failed++;
                            $status = 'failed';
                            $error = $exception->getMessage();
                            Log::error('Lỗi gửi email học viên hoàn thành: ' . $exception->getMessage());
                        }

                        EmailLog::create([
                            'email_account_id' => $account->id,
                            'recipient_email' => $recipient,
                            'subject' => $subject,
                            'content' => $body,
                            'status' => $status,
                            'error_message' => $error,
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

    public function mount(): void
    {
        parent::mount();

        $state = $this->defaultFilterState();
        $this->applyFilterState($state);
    }

    public function exportCurrentView()
    {
        $records = $this->getExportCollection();

        if ($records->isEmpty()) {
            Notification::make()->title('Không có dữ liệu để xuất')->warning()->send();

            return null;
        }

        $visibleColumns = collect($this->getVisibleTableColumns())
            ->map(fn ($column) => [
                'name' => $column->getName(),
                'label' => $column->getLabel(),
            ])
            ->values()
            ->all();

        return HocVienHoanThanhResource::exportWithSummary(
            $this->summaryRows,
            $records,
            $visibleColumns,
            $this->resolveFilterState(),
            $this->summaryTotals,
            'hoc_vien_hoan_thanh.xlsx'
        );
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

    protected function applyFilterState(array $state): void
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
}
