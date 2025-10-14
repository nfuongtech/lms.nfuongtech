<?php

namespace App\Filament\Resources;

use App\Exports\SimpleArrayExport;
use App\Filament\Resources\HocVienHoanThanhResource\Pages;
use App\Models\DangKy;
use App\Models\HocVien;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KetQuaKhoaHoc;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\QuyTacMaKhoa;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class HocVienHoanThanhResource extends Resource
{
    protected static ?string $model = HocVienHoanThanh::class;

    protected static ?string $navigationGroup = 'Đào tạo';

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Học viên hoàn thành';

    protected static ?string $modelLabel = 'Học viên hoàn thành';

    protected static ?string $pluralModelLabel = 'Học viên hoàn thành';

    public static function getSlug(): string
    {
        return 'hoc-vien-hoan-thanhs';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'hocVien.donVi',
                'hocVien.donViPhapNhan',
                'khoaHoc.chuongTrinh',
                'ketQua',
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('TT')
                    ->rowIndex()
                    ->alignment(Alignment::Center)
                    ->toggleable(false),
                Tables\Columns\TextColumn::make('hocVien.msnv')
                    ->label('MS')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable()
                    ->toggleable(false),
                Tables\Columns\TextColumn::make('hocVien.ho_ten')
                    ->label('Họ & Tên')
                    ->sortable()
                    ->searchable()
                    ->toggleable(false),
                Tables\Columns\TextColumn::make('hocVien.nam_sinh')
                    ->label('Năm sinh')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hocVien.gioi_tinh')
                    ->label('Giới tính')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hocVien.chuc_vu')
                    ->label('Chức vụ')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hocVien.donVi.phong_bo_phan')
                    ->label('Phòng/Bộ phận')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hocVien.donVi.cong_ty_ban_nvqt')
                    ->label('Công ty/Ban NVQT')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hocVien.donVi.thaco_tdtv')
                    ->label('THACO/TĐTV')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hocVien.donViPhapNhan.ten_don_vi')
                    ->label('Đơn vị pháp nhân/trả lương')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('khoaHoc.ten_khoa_hoc')
                    ->label('Tên khóa học')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')
                    ->label('Mã khóa')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('loai_hinh_dao_tao')
                    ->label('Loại hình đào tạo')
                    ->state(fn (HocVienHoanThanh $record) => self::resolveTrainingType($record))
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ketQua.diem_trung_binh')
                    ->label('ĐTB')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => self::decimalOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ketQua.tong_so_gio_thuc_te')
                    ->label('Giờ thực học')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => self::decimalOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ngay_hoan_thanh')
                    ->label('Ngày hoàn thành')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('chi_phi_dao_tao')
                    ->label('Chi phí đào tạo')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => self::currencyOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('so_chung_nhan')
                    ->label('Số chứng nhận')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('certificate_links')
                    ->label('File/Link Chứng nhận')
                    ->state(fn (HocVienHoanThanh $record) => self::certificateState($record))
                    ->html()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ngay_het_han_chung_nhan')
                    ->label('Ngày hết hạn')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn (HocVienHoanThanh $record, $state) => self::formatExpiry($record, $state))
                    ->extraAttributes(function (HocVienHoanThanh $record) {
                        if ($record->thoi_han_chung_nhan === 'khong_thoi_han') {
                            return [];
                        }

                        if (! $record->ngay_het_han_chung_nhan) {
                            return [];
                        }

                        return [
                            'class' => 'bg-rose-50 text-rose-700 font-semibold',
                        ];
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ketQua.danh_gia_ren_luyen')
                    ->label('Đánh giá rèn luyện')
                    ->alignment(Alignment::Center)
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
                BadgeColumn::make('ketQua.ket_qua')
                    ->label('Kết quả')
                    ->alignment(Alignment::Center)
                    ->colors([
                        'success' => fn (?string $state) => $state === 'hoan_thanh',
                        'danger' => fn (?string $state) => $state === 'khong_hoan_thanh',
                    ])
                    ->formatStateUsing(fn (?string $state) => $state === 'khong_hoan_thanh' ? 'Không hoàn thành' : 'Hoàn thành')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ghi_chu')
                    ->label('Ghi chú')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
            ])
            ->defaultPaginationPageOption(50)
            ->filters([
                Tables\Filters\Filter::make('bo_loc')
                    ->form([
                        Forms\Components\Select::make('year')
                            ->label('Năm')
                            ->options(fn () => self::getYearOptions())
                            ->default(now()->year)
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('week', null))
                            ->searchable(),
                        Forms\Components\Select::make('month')
                            ->label('Tháng')
                            ->options(fn (callable $get) => self::getMonthOptions($get('year')))
                            ->default(now()->month)
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('week', null))
                            ->searchable(),
                        Forms\Components\Select::make('week')
                            ->label('Tuần')
                            ->options(fn (callable $get) => self::getWeekOptions($get('year'), $get('month')))
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('course_id', null))
                            ->searchable(),
                        Forms\Components\DatePicker::make('from_date')
                            ->label('Từ ngày')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('Đến ngày')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('training_types')
                            ->label('Loại hình đào tạo')
                            ->options(fn () => self::getTrainingTypeOptions())
                            ->multiple()
                            ->searchable(),
                        Forms\Components\Select::make('course_id')
                            ->label('Khóa học')
                            ->options(fn (callable $get) => self::getCourseOptions(
                                $get('year'),
                                $get('month'),
                                $get('week'),
                                $get('from_date'),
                                $get('to_date'),
                                $get('training_types') ?? []
                            ))
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data) => self::applyFilterConstraints($query, $data))
                    ->default([
                        'year' => now()->year,
                        'month' => now()->month,
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (! empty($data['year'])) {
                            $indicators['year'] = 'Năm: ' . $data['year'];
                        }

                        if (! empty($data['month'])) {
                            $indicators['month'] = 'Tháng: ' . $data['month'];
                        }

                        if (! empty($data['week'])) {
                            $indicators['week'] = 'Tuần: ' . $data['week'];
                        }

                        if (! empty($data['course_id'])) {
                            $course = KhoaHoc::find($data['course_id']);
                            if ($course) {
                                $indicators['course_id'] = 'Khóa học: ' . ($course->ma_khoa_hoc ?? $course->ten_khoa_hoc);
                            }
                        }

                        if (! empty($data['training_types']) && is_array($data['training_types'])) {
                            $indicators['training_types'] = 'Loại hình: ' . implode(', ', $data['training_types']);
                        }

                        if (! empty($data['from_date'])) {
                            $indicators['from_date'] = 'Từ ngày: ' . Carbon::parse($data['from_date'])->format('d/m/Y');
                        }

                        if (! empty($data['to_date'])) {
                            $indicators['to_date'] = 'Đến ngày: ' . Carbon::parse($data['to_date'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersTriggerAction(fn (Tables\Actions\Action $action) => $action->label('Chọn lọc thông tin'))
            ->actions([
                Tables\Actions\Action::make('cap_nhat')
                    ->label('Cập nhật')
                    ->icon('heroicon-o-pencil-square')
                    ->form(self::getUpdateFormSchema())
                    ->fillForm(fn (HocVienHoanThanh $record): array => self::getUpdateFormDefaults($record))
                    ->action(fn (HocVienHoanThanh $record, array $data) => self::handleUpdateAction($record, $data))
                    ->visible(fn (HocVienHoanThanh $record) => (bool) $record->ketQua),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHocVienHoanThanhs::route('/'),
        ];
    }

    public static function applyFilterConstraints(Builder $query, array $data): Builder
    {
        $year = (int) ($data['year'] ?? now()->year);
        $month = isset($data['month']) && $data['month'] !== '' ? (int) $data['month'] : null;
        $week = isset($data['week']) && $data['week'] !== '' ? (int) $data['week'] : null;
        $courseId = $data['course_id'] ?? null;
        $fromDate = $data['from_date'] ?? null;
        $toDate = $data['to_date'] ?? null;
        $trainingTypes = is_array($data['training_types'] ?? null) ? array_filter($data['training_types']) : [];

        $query->whereHas('khoaHoc.lichHocs', function (Builder $lichHocQuery) use ($year, $month, $week, $fromDate, $toDate) {
            $lichHocQuery->where('nam', $year)
                ->when($month, fn ($q) => $q->where('thang', $month))
                ->when($week, fn ($q) => $q->where('tuan', $week))
                ->when($fromDate, fn ($q) => $q->whereDate('ngay_hoc', '>=', $fromDate))
                ->when($toDate, fn ($q) => $q->whereDate('ngay_hoc', '<=', $toDate));
        });

        if (! empty($trainingTypes)) {
            $query->whereHas('khoaHoc', function (Builder $courseQuery) use ($trainingTypes) {
                self::applyTrainingTypeFilter($courseQuery, $trainingTypes);
            });
        }

        if (! empty($courseId)) {
            $query->where('khoa_hoc_id', $courseId);
        }

        return $query;
    }

    public static function getYearOptions(): array
    {
        return LichHoc::query()
            ->select('nam')
            ->distinct()
            ->orderByDesc('nam')
            ->pluck('nam', 'nam')
            ->toArray();
    }

    public static function getMonthOptions(?int $year): array
    {
        if (! $year) {
            return [];
        }

        return LichHoc::query()
            ->where('nam', $year)
            ->select('thang')
            ->distinct()
            ->orderBy('thang')
            ->pluck('thang', 'thang')
            ->toArray();
    }

    public static function getWeekOptions(?int $year, ?int $month = null): array
    {
        if (! $year) {
            return [];
        }

        $query = LichHoc::query()
            ->where('nam', $year);

        if ($month) {
            $query->where('thang', $month);
        }

        return $query->select('tuan')
            ->distinct()
            ->orderBy('tuan')
            ->pluck('tuan', 'tuan')
            ->toArray();
    }

    public static function getCourseOptions(
        ?int $year,
        ?int $month,
        ?int $week,
        ?string $fromDate,
        ?string $toDate,
        array $trainingTypes
    ): array {
        if (! $year) {
            return [];
        }

        $courseQuery = KhoaHoc::query()
            ->with('chuongTrinh')
            ->whereHas('lichHocs', function (Builder $lichHocQuery) use ($year, $month, $week, $fromDate, $toDate) {
                $lichHocQuery->where('nam', $year)
                    ->when($month, fn ($q) => $q->where('thang', $month))
                    ->when($week, fn ($q) => $q->where('tuan', $week))
                    ->when($fromDate, fn ($q) => $q->whereDate('ngay_hoc', '>=', $fromDate))
                    ->when($toDate, fn ($q) => $q->whereDate('ngay_hoc', '<=', $toDate));
            })
            ->whereIn('id', DangKy::query()->select('khoa_hoc_id')->distinct())
            ->orderBy('ma_khoa_hoc');

        if (! empty($trainingTypes)) {
            $courseQuery->where(function (Builder $builder) use ($trainingTypes) {
                self::applyTrainingTypeFilter($builder, $trainingTypes);
            });
        }

        return $courseQuery->get()
            ->mapWithKeys(function (KhoaHoc $course) {
                $label = trim(implode(' - ', array_filter([
                    $course->ma_khoa_hoc,
                    $course->ten_khoa_hoc,
                ])));

                return [$course->id => $label ?: ($course->ma_khoa_hoc ?? (string) $course->id)];
            })
            ->toArray();
    }

    protected static function applyTrainingTypeFilter(Builder $builder, array $trainingTypes): void
    {
        $trainingTypes = collect($trainingTypes)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        if (empty($trainingTypes)) {
            return;
        }

        $hasCourseColumn = Schema::hasColumn('khoa_hocs', 'loai_hinh_dao_tao');
        $hasProgramTable = Schema::hasTable('chuong_trinhs') && Schema::hasColumn('chuong_trinhs', 'loai_hinh_dao_tao');

        $builder->where(function (Builder $query) use ($trainingTypes, $hasCourseColumn, $hasProgramTable) {
            $applied = false;

            if ($hasCourseColumn) {
                $query->whereIn('loai_hinh_dao_tao', $trainingTypes);
                $applied = true;
            }

            if ($hasProgramTable) {
                $method = $applied ? 'orWhereHas' : 'whereHas';
                $query->{$method}('chuongTrinh', fn ($q) => $q->whereIn('loai_hinh_dao_tao', $trainingTypes));
                $applied = true;
            }

            if (! $applied) {
                $query->whereRaw('1 = 0');
            }
        });
    }

    public static function getTrainingTypeOptions(): array
    {
        $fromRules = QuyTacMaKhoa::pluck('loai_hinh_dao_tao', 'loai_hinh_dao_tao')->filter()->toArray();

        $fromPrograms = KhoaHoc::query()
            ->with('chuongTrinh')
            ->whereHas('chuongTrinh')
            ->get()
            ->map(fn (KhoaHoc $course) => $course->chuongTrinh?->loai_hinh_dao_tao)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return collect($fromRules)
            ->merge(array_combine($fromPrograms, $fromPrograms))
            ->sort()
            ->toArray();
    }

    public static function decimalOrDash(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        $float = (float) $value;

        if (abs($float) < 0.0001) {
            return '-';
        }

        return number_format($float, 1, '.', '');
    }

    public static function currencyOrDash(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        $float = (float) $value;

        if (abs($float) < 0.0001) {
            return '-';
        }

        return number_format($float, 0, ',', '.');
    }

    public static function textOrDash(mixed $value): string
    {
        $string = trim((string) ($value ?? ''));

        return $string !== '' ? $string : '-';
    }

    protected static function certificateState(HocVienHoanThanh $record): string
    {
        $labels = self::certificateLabels($record);

        if (empty($labels)) {
            return '-';
        }

        return implode('<br>', array_map(fn ($entry) => sprintf(
            '<a href="%s" target="_blank" class="text-primary-600 underline">%s</a>',
            e($entry['url']),
            e($entry['label'])
        ), $labels));
    }

    protected static function certificateLabels(HocVienHoanThanh $record): array
    {
        $entries = [];

        if ($record->chung_chi_tap_tin) {
            $url = self::resolveStorageUrl($record->chung_chi_tap_tin);
            if ($url) {
                $entries[] = [
                    'url' => $url,
                    'label' => basename($record->chung_chi_tap_tin),
                ];
            }
        }

        if ($record->chung_chi_link) {
            $entries[] = [
                'url' => $record->chung_chi_link,
                'label' => self::humanizeLink($record->chung_chi_link),
            ];
        }

        return $entries;
    }

    protected static function resolveStorageUrl(string $path): ?string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    protected static function humanizeLink(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        return $path ? $host . '/' . $path : $host;
    }

    protected static function resolveTrainingType(HocVienHoanThanh $record): ?string
    {
        $course = $record->khoaHoc;

        if (! $course) {
            return null;
        }

        return $course->loai_hinh_dao_tao
            ?? $course->chuongTrinh?->loai_hinh_dao_tao
            ?? null;
    }

    protected static function formatExpiry(HocVienHoanThanh $record, $state): string
    {
        if ($record->thoi_han_chung_nhan === 'khong_thoi_han') {
            return 'Không thời hạn';
        }

        if ($state) {
            return Carbon::parse($state)->format('d/m/Y');
        }

        return '-';
    }

    protected static function getUpdateFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('ket_qua')
                    ->label('Kết quả cuối cùng')
                    ->options([
                        'hoan_thanh' => 'Hoàn thành',
                        'khong_hoan_thanh' => 'Không hoàn thành',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('ngay_hoan_thanh')
                    ->label('Ngày hoàn thành')
                    ->closeOnDateSelection(),
                Forms\Components\TextInput::make('diem_trung_binh')
                    ->label('Điểm trung bình')
                    ->numeric()
                    ->step('0.1'),
                Forms\Components\TextInput::make('tong_so_gio_thuc_te')
                    ->label('Giờ thực học')
                    ->numeric()
                    ->step('0.1'),
                Forms\Components\TextInput::make('chi_phi_dao_tao')
                    ->label('Chi phí đào tạo')
                    ->numeric()
                    ->step('1')
                    ->prefix('VND')
                    ->nullable(),
                Forms\Components\Toggle::make('chung_chi_da_cap')
                    ->label('Đã cấp chứng nhận'),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('so_chung_nhan')
                    ->label('Số chứng nhận')
                    ->maxLength(255),
                Forms\Components\Select::make('thoi_han_chung_nhan')
                    ->label('Thời hạn Chứng nhận')
                    ->options([
                        'khong_thoi_han' => 'Không thời hạn',
                        '1' => '1 năm',
                        '2' => '2 năm',
                        '3' => '3 năm',
                        '4' => '4 năm',
                        '5' => '5 năm',
                    ])
                    ->nullable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $completionDate = $get('ngay_hoan_thanh');

                        if ($state === 'khong_thoi_han') {
                            $set('ngay_het_han_chung_nhan', null);
                            return;
                        }

                        if ($state && $completionDate) {
                            $expiry = Carbon::parse($completionDate)->addYears((int) $state)->format('Y-m-d');
                            $set('ngay_het_han_chung_nhan', $expiry);
                        }
                    })
                    ->helperText('Chọn gợi ý để tự động tính thời hạn hoặc nhập thủ công bên dưới.'),
            ]),
            Forms\Components\DatePicker::make('ngay_het_han_chung_nhan')
                ->label('Thời hạn chứng nhận đến')
                ->closeOnDateSelection()
                ->nullable(),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('chung_chi_link')
                    ->label('Link chứng nhận')
                    ->url()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('chung_chi_tap_tin')
                    ->label('Tập tin chứng nhận (PDF)')
                    ->directory('chung-chi')
                    ->disk('public')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(5120)
                    ->nullable()
                    ->preserveFilenames(),
            ]),
            Forms\Components\Textarea::make('danh_gia_ren_luyen')
                ->label('Đánh giá rèn luyện')
                ->rows(3),
            Forms\Components\Textarea::make('ghi_chu')
                ->label('Ghi chú')
                ->rows(3),
        ];
    }

    protected static function getUpdateFormDefaults(HocVienHoanThanh $record): array
    {
        return [
            'ket_qua' => $record->ketQua?->ket_qua ?? 'hoan_thanh',
            'ngay_hoan_thanh' => $record->ngay_hoan_thanh,
            'diem_trung_binh' => $record->ketQua?->diem_trung_binh,
            'tong_so_gio_thuc_te' => $record->ketQua?->tong_so_gio_thuc_te,
            'chi_phi_dao_tao' => $record->chi_phi_dao_tao,
            'chung_chi_da_cap' => $record->chung_chi_da_cap,
            'chung_chi_link' => $record->chung_chi_link,
            'chung_chi_tap_tin' => $record->chung_chi_tap_tin,
            'so_chung_nhan' => $record->so_chung_nhan,
            'thoi_han_chung_nhan' => $record->thoi_han_chung_nhan,
            'ngay_het_han_chung_nhan' => $record->ngay_het_han_chung_nhan,
            'danh_gia_ren_luyen' => $record->ketQua?->danh_gia_ren_luyen,
            'ghi_chu' => $record->ghi_chu,
        ];
    }

    protected static function handleUpdateAction(HocVienHoanThanh $record, array $data): void
    {
        $ketQua = self::normalizeKetQua($data['ket_qua'] ?? 'hoan_thanh');
        $ketQuaModel = $record->ketQua;

        if (! $ketQuaModel) {
            $ketQuaModel = KetQuaKhoaHoc::find($record->ket_qua_khoa_hoc_id);
        }

        if ($ketQuaModel) {
            $ketQuaModel->diem_trung_binh = $data['diem_trung_binh'] ?? $ketQuaModel->diem_trung_binh;
            $ketQuaModel->tong_so_gio_thuc_te = $data['tong_so_gio_thuc_te'] ?? $ketQuaModel->tong_so_gio_thuc_te;
            $ketQuaModel->ket_qua = $ketQua;
            $ketQuaModel->ket_qua_goi_y = $ketQuaModel->ket_qua_goi_y ?? $ketQua;
            $ketQuaModel->danh_gia_ren_luyen = $data['danh_gia_ren_luyen'] ?? $ketQuaModel->danh_gia_ren_luyen;
            $ketQuaModel->can_hoc_lai = $ketQua === 'khong_hoan_thanh';
            $ketQuaModel->save();
        }

        $updateData = [
            'ngay_hoan_thanh' => $data['ngay_hoan_thanh'] ?? null,
            'chi_phi_dao_tao' => self::toDecimal($data['chi_phi_dao_tao'] ?? null),
            'chung_chi_da_cap' => $data['chung_chi_da_cap'] ?? false,
            'chung_chi_link' => $data['chung_chi_link'] ?? null,
            'chung_chi_tap_tin' => $data['chung_chi_tap_tin'] ?? $record->chung_chi_tap_tin,
            'so_chung_nhan' => $data['so_chung_nhan'] ?? null,
            'thoi_han_chung_nhan' => $data['thoi_han_chung_nhan'] ?? null,
            'ngay_het_han_chung_nhan' => $data['ngay_het_han_chung_nhan'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ];

        if (($data['thoi_han_chung_nhan'] ?? null) === 'khong_thoi_han') {
            $updateData['thoi_han_chung_nhan'] = 'khong_thoi_han';
            $updateData['ngay_het_han_chung_nhan'] = null;
        }

        $record->update($updateData);

        if (! empty($data['chung_chi_tap_tin'])) {
            self::renameCertificateFile($record);
        }

        if ($ketQua === 'hoan_thanh') {
            HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $record->ket_qua_khoa_hoc_id)->delete();
        } else {
            HocVienKhongHoanThanh::updateOrCreate(
                [
                    'hoc_vien_id' => $record->hoc_vien_id,
                    'khoa_hoc_id' => $record->khoa_hoc_id,
                    'ket_qua_khoa_hoc_id' => $record->ket_qua_khoa_hoc_id,
                ],
                [
                    'ly_do_khong_hoan_thanh' => $data['ghi_chu'] ?? null,
                ]
            );

            $record->delete();
        }

        Notification::make()->title('Đã cập nhật kết quả học viên')->success()->send();
    }

    protected static function renameCertificateFile(HocVienHoanThanh $record): void
    {
        if (! $record->chung_chi_tap_tin) {
            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($record->chung_chi_tap_tin)) {
            return;
        }

        $extension = pathinfo($record->chung_chi_tap_tin, PATHINFO_EXTENSION);
        $ms = $record->hocVien?->msnv ?? 'hoc-vien';
        $courseCode = $record->khoaHoc?->ma_khoa_hoc ?? 'khoa-hoc';
        $cleanCourseCode = Str::slug($courseCode, '-');
        $filename = $ms . '-' . $cleanCourseCode . ($extension ? '.' . $extension : '');
        $newPath = 'chung-chi/' . $filename;

        if ($record->chung_chi_tap_tin === $newPath) {
            return;
        }

        if ($disk->exists($newPath)) {
            $disk->delete($newPath);
        }

        $disk->move($record->chung_chi_tap_tin, $newPath);
        $record->updateQuietly(['chung_chi_tap_tin' => $newPath]);
    }

    public static function normalizeKetQua(?string $value): string
    {
        $normalized = Str::slug((string) $value, '_');

        return $normalized === 'khong_hoan_thanh' ? 'khong_hoan_thanh' : 'hoan_thanh';
    }

    public static function getExportColumnDefinitions(): array
    {
        return [
            'index' => [
                'heading' => 'TT',
                'format' => fn (HocVienHoanThanh $record, int $index) => $index + 1,
            ],
            'hocVien.msnv' => [
                'heading' => 'Mã số',
                'format' => fn (HocVienHoanThanh $record, int $index) => $record->hocVien?->msnv ?? '-',
            ],
            'hocVien.ho_ten' => [
                'heading' => 'Họ & Tên',
                'format' => fn (HocVienHoanThanh $record, int $index) => $record->hocVien?->ho_ten ?? '-',
            ],
            'hocVien.nam_sinh' => [
                'heading' => 'Ngày tháng năm sinh',
                'format' => function (HocVienHoanThanh $record, int $index = 0) {
                    $value = $record->hocVien?->nam_sinh;

                    if (! $value) {
                        return '-';
                    }

                    try {
                        return Carbon::parse($value)->format('d/m/Y');
                    } catch (\Throwable $e) {
                        return '-';
                    }
                },
            ],
            'hocVien.gioi_tinh' => [
                'heading' => 'Giới tính',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->hocVien?->gioi_tinh),
            ],
            'hocVien.chuc_vu' => [
                'heading' => 'Chức vụ',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->hocVien?->chuc_vu),
            ],
            'hocVien.donVi.phong_bo_phan' => [
                'heading' => 'Phòng/Bộ phận',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->hocVien?->donVi?->phong_bo_phan),
            ],
            'hocVien.donVi.cong_ty_ban_nvqt' => [
                'heading' => 'Công ty/Ban NVQT',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->hocVien?->donVi?->cong_ty_ban_nvqt),
            ],
            'hocVien.donVi.thaco_tdtv' => [
                'heading' => 'THACO/TĐTV',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->hocVien?->donVi?->thaco_tdtv),
            ],
            'hocVien.donViPhapNhan.ten_don_vi' => [
                'heading' => 'Đơn vị pháp nhân/trả lương',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->hocVien?->donViPhapNhan?->ten_don_vi),
            ],
            'khoaHoc.ten_khoa_hoc' => [
                'heading' => 'Tên khóa học',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->khoaHoc?->ten_khoa_hoc),
            ],
            'khoaHoc.ma_khoa_hoc' => [
                'heading' => 'Mã khóa',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->khoaHoc?->ma_khoa_hoc),
            ],
            'loai_hinh_dao_tao' => [
                'heading' => 'Loại hình đào tạo',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash(self::resolveTrainingType($record)),
            ],
            'ketQua.diem_trung_binh' => [
                'heading' => 'ĐTB',
                'format' => function (HocVienHoanThanh $record, int $index = 0) {
                    $value = $record->ketQua?->diem_trung_binh;

                    return $value !== null ? number_format((float) $value, 1, '.', '') : '-';
                },
            ],
            'ketQua.tong_so_gio_thuc_te' => [
                'heading' => 'Giờ thực học',
                'format' => function (HocVienHoanThanh $record, int $index = 0) {
                    $value = $record->ketQua?->tong_so_gio_thuc_te;

                    return $value !== null ? number_format((float) $value, 1, '.', '') : '-';
                },
            ],
            'ngay_hoan_thanh' => [
                'heading' => 'Ngày hoàn thành',
                'format' => function (HocVienHoanThanh $record, int $index = 0) {
                    if (! $record->ngay_hoan_thanh) {
                        return '-';
                    }

                    try {
                        return Carbon::parse($record->ngay_hoan_thanh)->format('d/m/Y');
                    } catch (\Throwable $e) {
                        return '-';
                    }
                },
            ],
            'chi_phi_dao_tao' => [
                'heading' => 'Chi phí đào tạo',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::currencyOrDash($record->chi_phi_dao_tao),
            ],
            'so_chung_nhan' => [
                'heading' => 'Số chứng nhận',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->so_chung_nhan),
            ],
            'certificate_links' => [
                'heading' => 'File/Link Chứng nhận',
                'format' => function (HocVienHoanThanh $record, int $index = 0) {
                    $certificates = collect(self::certificateLabels($record))
                        ->map(fn ($entry) => $entry['label'])
                        ->implode('\n');

                    return $certificates !== '' ? $certificates : '-';
                },
            ],
            'ngay_het_han_chung_nhan' => [
                'heading' => 'Ngày hết hạn',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::formatExpiry($record, $record->ngay_het_han_chung_nhan),
            ],
            'ketQua.danh_gia_ren_luyen' => [
                'heading' => 'Đánh giá rèn luyện',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->ketQua?->danh_gia_ren_luyen),
            ],
            'ketQua.ket_qua' => [
                'heading' => 'Kết quả',
                'format' => fn (HocVienHoanThanh $record, int $index) => $record->ketQua && $record->ketQua->ket_qua === 'khong_hoan_thanh'
                    ? 'Không hoàn thành'
                    : 'Hoàn thành',
            ],
            'ghi_chu' => [
                'heading' => 'Ghi chú',
                'format' => fn (HocVienHoanThanh $record, int $index) => self::textOrDash($record->ghi_chu),
            ],
        ];
    }

    public static function getExportHeadings(array $columnKeys): array
    {
        $definitions = self::getExportColumnDefinitions();

        return collect($columnKeys)
            ->filter(fn ($key) => isset($definitions[$key]))
            ->map(fn ($key) => $definitions[$key]['heading'])
            ->values()
            ->all();
    }

    public static function buildExportRows(Collection $records, array $columnKeys): array
    {
        $definitions = self::getExportColumnDefinitions();

        $keys = collect($columnKeys)
            ->filter(fn ($key) => isset($definitions[$key]))
            ->values();

        return $records->values()->map(function (HocVienHoanThanh $record, int $index) use ($keys, $definitions) {
            return $keys->map(function (string $key) use ($definitions, $record, $index) {
                $formatter = $definitions[$key]['format'];

                return $formatter($record, $index);
            })->all();
        })->toArray();
    }

    public static function buildSummaryExportRows(Collection $summaryRows, array $totals): array
    {
        if ($summaryRows->isEmpty()) {
            return [];
        }

        $rows = [
            ['Danh sách khóa học'],
            [],
            [
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
            ],
        ];

        foreach ($summaryRows as $row) {
            $rows[] = [
                $row['index'] ?? '-',
                $row['ma_khoa'] ?? '-',
                $row['ten_khoa'] ?? '-',
                $row['trang_thai'] ?? '-',
                $row['tong_gio'] ?? '-',
                $row['giang_vien'] ?? '-',
                $row['thoi_gian'] ?? '-',
                number_format((int) ($row['so_luong_hv'] ?? 0), 0, ',', '.'),
                number_format((int) ($row['hoan_thanh'] ?? 0), 0, ',', '.'),
                number_format((int) ($row['khong_hoan_thanh'] ?? 0), 0, ',', '.'),
                ($row['tong_thu'] ?? 0) > 0
                    ? number_format((float) $row['tong_thu'], 0, ',', '.')
                    : '-',
                $row['ghi_chu'] ?? '-',
            ];
        }

        $rows[] = [
            'Tổng cộng',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format((int) ($totals['so_luong_hv'] ?? 0), 0, ',', '.'),
            number_format((int) ($totals['hoan_thanh'] ?? 0), 0, ',', '.'),
            number_format((int) ($totals['khong_hoan_thanh'] ?? 0), 0, ',', '.'),
            ($totals['tong_thu'] ?? 0) > 0
                ? number_format((float) $totals['tong_thu'], 0, ',', '.')
                : '-',
            '',
        ];

        $rows[] = [];

        return $rows;
    }

    public static function export(
        Collection $records,
        array $columnKeys,
        Collection $summaryRows,
        array $summaryTotals,
        string $filename
    ) {
        $columnKeys = collect($columnKeys)
            ->filter(fn ($key) => isset(self::getExportColumnDefinitions()[$key]))
            ->values()
            ->all();

        $rows = [];

        $summaryBlock = self::buildSummaryExportRows($summaryRows, $summaryTotals);
        if (! empty($summaryBlock)) {
            $rows = array_merge($rows, $summaryBlock);
        }

        if (! empty($columnKeys)) {
            $rows[] = ['Danh sách học viên'];
            $rows[] = [];
            $rows[] = self::getExportHeadings($columnKeys);
            $rows = array_merge($rows, self::buildExportRows($records, $columnKeys));
        }

        if (empty($rows)) {
            $rows[] = ['Không có dữ liệu phù hợp với bộ lọc hiện tại.'];
        }

        return Excel::download(new SimpleArrayExport($rows), $filename);
    }

    public static function parseImportRows(string $filePath): array
    {
        $sheets = Excel::toArray([], $filePath);
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return [];
        }

        $header = array_map(fn ($value) => Str::slug(trim((string) $value), '_'), array_shift($rows));

        return collect($rows)
            ->filter(fn ($row) => array_filter($row))
            ->map(function (array $row) use ($header) {
                $mapped = [];

                foreach ($header as $index => $key) {
                    $mapped[$key] = $row[$index] ?? null;
                }

                return $mapped;
            })
            ->values()
            ->toArray();
    }

    public static function handleImportRow(array $row): array
    {
        $errors = [];

        $ms = Arr::get($row, 'ma_so') ?? Arr::get($row, 'ms');
        if (! $ms) {
            return ['errors' => ['Thiếu mã số học viên.']];
        }

        $hocVien = HocVien::where('msnv', $ms)->first();
        if (! $hocVien) {
            return ['errors' => ["Mã số {$ms} chưa tồn tại. Vui lòng cập nhật trong trang Học viên."]];
        }

        $maKhoa = Arr::get($row, 'ma_khoa') ?? Arr::get($row, 'ma_khoa_hoc');
        if (! $maKhoa) {
            return ['errors' => ['Thiếu mã khóa học.']];
        }

        $course = KhoaHoc::where('ma_khoa_hoc', $maKhoa)->first();
        if (! $course) {
            return ['errors' => ["Không tìm thấy khóa học {$maKhoa}."]];
        }

        $dangKy = DangKy::where('hoc_vien_id', $hocVien->id)
            ->where('khoa_hoc_id', $course->id)
            ->first();

        if (! $dangKy) {
            return ['errors' => ["Học viên {$ms} chưa ghi danh khóa {$maKhoa}."]];
        }

        $ketQua = KetQuaKhoaHoc::firstOrCreate(['dang_ky_id' => $dangKy->id]);

        $ketQua->diem_trung_binh = self::toDecimal(Arr::get($row, 'dtb')) ?? $ketQua->diem_trung_binh;
        $ketQua->tong_so_gio_thuc_te = self::toDecimal(Arr::get($row, 'gio_thuc_hoc')) ?? $ketQua->tong_so_gio_thuc_te;
        $ketQua->ket_qua = 'hoan_thanh';
        $ketQua->ket_qua_goi_y = 'hoan_thanh';
        $ketQua->save();

        $ngayHoanThanh = self::parseDate(Arr::get($row, 'ngay_hoan_thanh'));
        $ngayHetHan = self::parseDate(Arr::get($row, 'ngay_het_han_chung_nhan'));

        $record = HocVienHoanThanh::updateOrCreate(
            [
                'hoc_vien_id' => $hocVien->id,
                'khoa_hoc_id' => $course->id,
                'ket_qua_khoa_hoc_id' => $ketQua->id,
            ],
            [
                'ngay_hoan_thanh' => $ngayHoanThanh,
                'chi_phi_dao_tao' => self::toDecimal(Arr::get($row, 'chi_phi_dao_tao')),
                'so_chung_nhan' => Arr::get($row, 'so_chung_nhan'),
                'chung_chi_link' => Arr::get($row, 'file_link_chung_nhan') ?? Arr::get($row, 'link_chung_nhan'),
                'thoi_han_chung_nhan' => Arr::get($row, 'thoi_han_chung_nhan'),
                'ngay_het_han_chung_nhan' => $ngayHetHan,
                'ghi_chu' => Arr::get($row, 'ghi_chu_hanh_dong') ?? Arr::get($row, 'ghi_chu'),
            ]
        );

        HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $ketQua->id)->delete();

        return [
            'record' => $record,
            'errors' => $errors,
        ];
    }

    protected static function toDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    protected static function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
