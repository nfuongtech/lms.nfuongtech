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
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('hocVien.msnv')
                    ->label('MS')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('hocVien.ho_ten')
                    ->label('Họ & Tên')
                    ->sortable()
                    ->searchable(),
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
                    ->label('Đơn vị trả lương')
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
                    ->formatStateUsing(fn ($state) => self::currencyOrDash($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('certificate_links')
                    ->label('File/Link Chứng nhận')
                    ->state(fn (HocVienHoanThanh $record) => self::certificateState($record))
                    ->html()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ngay_het_han_chung_nhan')
                    ->label('Ngày hết hạn Chứng nhận')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-')
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
                    ->formatStateUsing(fn (?string $state) => $state === 'khong_hoan_thanh' ? 'Không hoàn thành' : 'Hoàn thành'),
                Tables\Columns\TextColumn::make('ghi_chu')
                    ->label('Ghi chú')
                    ->wrap()
                    ->formatStateUsing(fn ($state) => self::textOrDash($state))
                    ->toggleable(),
            ])
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
                        Forms\Components\Select::make('week')
                            ->label('Tuần')
                            ->options(fn (callable $get) => self::getWeekOptions($get('year')))
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('course_id', null))
                            ->searchable(),
                        Forms\Components\Select::make('course_id')
                            ->label('Khóa học')
                            ->options(fn (callable $get) => self::getCourseOptions($get('year'), $get('week')))
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data) => self::applyFilterConstraints($query, $data))
                    ->default([
                        'year' => now()->year,
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (! empty($data['year'])) {
                            $indicators['year'] = 'Năm: ' . $data['year'];
                        }

                        if (! empty($data['week'])) {
                            $indicators['week'] = 'Tuần: ' . $data['week'];
                        }

                        if (! empty($data['course_id'])) {
                            $course = KhoaHoc::find($data['course_id']);
                            if ($course) {
                                $indicators['course_id'] = 'Khóa học: ' . $course->ma_khoa_hoc;
                            }
                        }

                        return $indicators;
                    }),
            ])
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
        $week = $data['week'] ?? null;
        $courseId = $data['course_id'] ?? null;

        $query->whereHas('khoaHoc.lichHocs', function (Builder $lichHocQuery) use ($year, $week) {
            $lichHocQuery->where('nam', $year);

            if (! empty($week)) {
                $lichHocQuery->where('tuan', $week);
            }
        });

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

    public static function getWeekOptions(?int $year): array
    {
        if (! $year) {
            return [];
        }

        return LichHoc::query()
            ->where('nam', $year)
            ->select('tuan')
            ->distinct()
            ->orderBy('tuan')
            ->pluck('tuan', 'tuan')
            ->toArray();
    }

    public static function getCourseOptions(?int $year, ?int $week): array
    {
        if (! $year) {
            return [];
        }

        $courseQuery = KhoaHoc::query()
            ->with('chuongTrinh')
            ->whereHas('lichHocs', function (Builder $lichHocQuery) use ($year, $week) {
                $lichHocQuery->where('nam', $year);

                if (! empty($week)) {
                    $lichHocQuery->where('tuan', $week);
                }
            })
            ->orderBy('ma_khoa_hoc');

        return $courseQuery->get()
            ->mapWithKeys(function (KhoaHoc $course) {
                $label = trim(implode(' - ', array_filter([
                    $course->ma_khoa_hoc,
                    $course->ten_khoa_hoc,
                ])));

                return [$course->id => $label ?: $course->ma_khoa_hoc];
            })
            ->toArray();
    }

    public static function decimalOrDash(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        $float = (float) $value;
        return number_format($float, 1, '.', '');
    }

    public static function currencyOrDash(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        $float = (float) $value;

        return number_format($float, 0, ',', '.');
    }

    public static function textOrDash(mixed $value): string
    {
        $string = trim((string) ($value ?? ''));

        return $string !== '' ? $string : '-';
    }

    protected static function certificateState(HocVienHoanThanh $record): string
    {
        $parts = [];

        if ($record->chung_chi_tap_tin) {
            $url = self::resolveStorageUrl($record->chung_chi_tap_tin);
            if ($url) {
                $parts[] = sprintf('<a href="%s" target="_blank" class="text-primary-600 underline">File</a>', e($url));
            }
        }

        if ($record->chung_chi_link) {
            $parts[] = sprintf('<a href="%s" target="_blank" class="text-primary-600 underline">Link</a>', e($record->chung_chi_link));
        }

        if (empty($parts)) {
            return '-';
        }

        return implode('<br>', $parts);
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
                    ->prefix('VND')
                    ->nullable(),
                Forms\Components\Toggle::make('chung_chi_da_cap')
                    ->label('Đã cấp chứng nhận'),
            ]),
            Forms\Components\Grid::make()->columns(3)->schema([
                Forms\Components\TextInput::make('so_chung_nhan')
                    ->label('Số chứng nhận')
                    ->maxLength(120)
                    ->nullable(),
                Forms\Components\Select::make('thoi_han_chung_nhan')
                    ->label('Thời hạn Chứng nhận')
                    ->options([
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
                        if ($state && $completionDate) {
                            $expiry = Carbon::parse($completionDate)->addYears((int) $state)->format('Y-m-d');
                            $set('ngay_het_han_chung_nhan', $expiry);
                        }
                    })
                    ->helperText('Chọn gợi ý để tự động tính thời hạn hoặc nhập thủ công bên dưới.'),
                Forms\Components\DatePicker::make('ngay_het_han_chung_nhan')
                    ->label('Thời hạn chứng nhận đến')
                    ->closeOnDateSelection()
                    ->nullable(),
            ]),
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

    public static function buildExportRows(Collection $records): array
    {
        return $records->map(function (HocVienHoanThanh $record, int $index) {
            $hocVien = $record->hocVien;
            $ketQua = $record->ketQua;
            $course = $record->khoaHoc;

            $sessions = $ketQua?->dangKy?->diemDanhs ?? collect();
            $sessionSummary = $sessions->map(function ($item) {
                $ngay = $item->lichHoc?->ngay_hoc ? Carbon::parse($item->lichHoc->ngay_hoc)->format('d/m/Y') : null;
                $status = match ($item->trang_thai) {
                    'vang_phep' => 'Vắng P',
                    'vang_khong_phep' => 'Vắng KP',
                    default => 'Có mặt',
                };
                $gio = $item->so_gio_hoc !== null ? number_format((float) $item->so_gio_hoc, 1, '.', '') : '-';
                $diem = $item->diem_buoi_hoc !== null ? number_format((float) $item->diem_buoi_hoc, 1, '.', '') : '-';

                return trim(implode(' - ', array_filter([
                    $ngay,
                    $status,
                    $gio !== '-' ? ($gio . ' giờ') : null,
                    $diem !== '-' ? ('Điểm: ' . $diem) : null,
                ])));
            })->filter()->implode("\n");

            return [
                $index + 1,
                $hocVien?->msnv ?? '-',
                $hocVien?->ho_ten ?? '-',
                optional($hocVien?->nam_sinh)->format('d/m/Y') ?? '-',
                self::textOrDash($hocVien?->gioi_tinh),
                self::textOrDash($hocVien?->donVi?->ten_hien_thi),
                self::textOrDash($hocVien?->donViPhapNhan?->ten_don_vi),
                self::textOrDash($course?->ten_khoa_hoc),
                self::textOrDash($course?->ma_khoa_hoc),
                $ketQua?->diem_trung_binh ? number_format((float) $ketQua->diem_trung_binh, 1, '.', '') : '-',
                $ketQua?->tong_so_gio_thuc_te ? number_format((float) $ketQua->tong_so_gio_thuc_te, 1, '.', '') : '-',
                $record->ngay_hoan_thanh ? Carbon::parse($record->ngay_hoan_thanh)->format('d/m/Y') : '-',
                self::currencyOrDash($record->chi_phi_dao_tao),
                self::textOrDash($record->so_chung_nhan),
                $record->ngay_het_han_chung_nhan ? Carbon::parse($record->ngay_het_han_chung_nhan)->format('d/m/Y') : '-',
                $sessionSummary ?: '-',
                self::textOrDash($ketQua?->danh_gia_ren_luyen),
                $ketQua && $ketQua->ket_qua === 'hoan_thanh' ? 'Hoàn thành' : 'Không hoàn thành',
                self::textOrDash($record->ghi_chu),
            ];
        })->toArray();
    }

    public static function export(Collection $records, string $filename)
    {
        $rows = self::buildExportRows($records);

        $headings = [
            ['TRƯỜNG CAO ĐẲNG THACO'],
            [''],
            ['CHUYÊN CẦN & KẾT QUẢ HỌC VIÊN'],
            [''],
        ];

        $firstRecord = $records->first();
        $course = $firstRecord?->khoaHoc;
        $lecturerNames = $course?->lichHocs?->pluck('giangVien.ho_ten')->filter()->unique()->implode(', ');
        $dates = $course?->lichHocs?->pluck('ngay_hoc')->filter()->unique()->map(fn ($date) => Carbon::parse($date)->format('d/m/Y'))->implode("\n");
        $weeks = $course?->lichHocs?->pluck('tuan')->filter()->unique()->implode(', ');
        $status = $course?->trang_thai_hien_thi ?? '-';

        $headings[] = [
            'Tên khóa học: ' . ($course?->ten_khoa_hoc ?? '-'),
        ];

        $headings[] = [
            'Mã khóa: ' . ($course?->ma_khoa_hoc ?? '-'),
        ];

        $headings[] = [
            'Giảng viên: ' . ($lecturerNames ?: '-'),
        ];

        $headings[] = [
            'Ngày giờ đào tạo: ' . ($dates ?: '-'),
        ];

        $headings[] = [
            'Tuần: ' . ($weeks ?: '-'),
        ];

        $headings[] = [
            'Trạng thái: ' . $status,
        ];

        $headings[] = ['Danh sách học viên'];
        $headings[] = [''];
        $headings[] = [
            'TT',
            'Mã số',
            'Họ & Tên',
            'Ngày tháng năm sinh',
            'Giới tính',
            'Đơn vị',
            'Đơn vị trả lương',
            'Tên khóa học',
            'Mã khóa',
            'ĐTB',
            'Giờ thực học',
            'Ngày hoàn thành',
            'Chi phí đào tạo',
            'Số chứng nhận',
            'Ngày hết hạn chứng nhận',
            'Buổi học',
            'Đánh giá rèn luyện',
            'Kết quả',
            'Ghi chú hành động',
        ];

        return Excel::download(new SimpleArrayExport(array_merge($headings, $rows)), $filename);
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
