<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Filament\Resources\KhoaHocResource\RelationManagers\LichHocsRelationManager;
use App\Models\KhoaHoc;
use App\Models\ChuongTrinh;
use App\Models\QuyTacMaKhoa;
use App\Models\LichHoc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;

    protected static ?string $navigationLabel = 'Kế hoạch đào tạo';
    protected static ?string $pluralLabel = 'Kế hoạch đào tạo';
    protected static ?string $modelLabel = 'Kế hoạch đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Đào tạo';

    public static function getEloquentQuery(): Builder
    {
        // ❗ BỎ where('nam', năm hiện tại) để filters có thể thay đổi năm.
        // Mặc định vẫn sắp xếp theo tuần mới nhất và tính tổng giờ.
        return parent::getEloquentQuery()
            ->withMax('lichHocs as max_tuan', 'tuan')
            ->withSum('lichHocs as tong_gio', 'so_gio_giang')
            ->orderByDesc('max_tuan')
            ->orderBy('id', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('edit_mode')
                ->default(fn () => request()->routeIs('*.edit') ? false : true)
                ->dehydrated(false),

            Forms\Components\Section::make('Thông tin chung')
                ->headerActions([
                    FormAction::make('sua')
                        ->label('Sửa')
                        ->visible(fn () => request()->routeIs('*.edit'))
                        ->action(fn (Set $set) => $set('edit_mode', true)),
                ])
                ->schema([
                    Forms\Components\Select::make('chuong_trinh_id')
                        ->label('Chương trình')
                        ->options(fn () =>
                            ChuongTrinh::query()
                                ->where('tinh_trang', 'Đang áp dụng')
                                ->orderBy('ten_chuong_trinh')
                                ->pluck('ten_chuong_trinh', 'id')->toArray()
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            $ct = $state ? ChuongTrinh::find($state) : null;

                            if ($ct?->ten_chuong_trinh) {
                                $set('ten_khoa_hoc', (string) $ct->ten_chuong_trinh);
                            }
                            if (($get('che_do_ma_khoa') ?? 'auto') === 'auto' && $ct?->loai_hinh_dao_tao) {
                                $set('ma_khoa_hoc', (string) QuyTacMaKhoa::taoMaKhoaHoc($ct->loai_hinh_dao_tao));
                            }
                        })
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('ten_khoa_hoc')
                        ->label('Tên khóa học')
                        ->helperText('Mặc định theo tên Chương trình; bạn có thể sửa.')
                        ->maxLength(255)
                        ->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\Radio::make('che_do_ma_khoa')
                        ->label('Quy tắc mã khóa')
                        ->options([
                            'auto'   => 'Chọn tự động (lấy Quy tắc từ trang Quy tắc mã khóa)',
                            'manual' => 'Chọn nhập thủ công',
                        ])
                        ->default('auto')
                        ->inline()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            if ($state === 'auto') {
                                $ct = $get('chuong_trinh_id') ? ChuongTrinh::find($get('chuong_trinh_id')) : null;
                                if ($ct?->loai_hinh_dao_tao) {
                                    $set('ma_khoa_hoc', (string) QuyTacMaKhoa::taoMaKhoaHoc($ct->loai_hinh_dao_tao));
                                }
                            }
                        })
                        ->dehydrated(false)
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('ma_khoa_hoc')
                        ->label('Mã khóa')
                        ->helperText('Nếu ở chế độ Tự động, hệ thống sẽ điền sẵn để bạn nhìn thấy & có thể chỉnh.')
                        ->maxLength(100)
                        ->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('nam')
                        ->label('Năm')
                        ->numeric()->minValue(2000)->maxValue(2100)
                        ->default((int) now()->format('Y'))
                        ->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('yeu_cau_phan_tram_gio')
                        ->label('Yêu cầu % giờ học (>=)')
                        ->numeric()->step(1)->integer()->minValue(1)->maxValue(100)
                        ->default(80)->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('yeu_cau_diem_tb')
                        ->label('Yêu cầu điểm trung bình (>=)')
                        ->rule('regex:/^\d+([.,]\d)?$/')
                        ->default('5,0')->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rowIndex')
                    ->label('TT')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('ma_khoa_hoc')
                    ->label('Mã khóa')
                    ->searchable()
                    ->alignLeft()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ten_khoa_hoc')
                    ->label('Tên khóa học')
                    ->wrap()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('giang_viens_ten')
                    ->label('Giảng viên')
                    ->getStateUsing(function (KhoaHoc $record) {
                        $record->loadMissing(['lichHocs.giangVien' => fn ($q) => $q->select(['id','ho_ten'])]);
                        $names = $record->lichHocs
                            ->map(fn ($lh) => $lh->giangVien?->ho_ten)
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                        return !empty($names) ? implode(', ', $names) : '—';
                    })
                    ->wrap()
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tong_gio')
                    ->label('Tổng giờ')
                    ->alignCenter()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        $value = (float) ($state ?? 0);
                        return number_format($value, 1, '.', '');
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ngay_gio_list')
                    ->label('Ngày, Giờ đào tạo')
                    ->getStateUsing(function (KhoaHoc $record) {
                        $lich = $record->lichHocs()
                            ->orderBy('ngay_hoc')->orderBy('gio_bat_dau')
                            ->get(['ngay_hoc','gio_bat_dau','gio_ket_thuc']);

                        if ($lich->isEmpty()) return '—';

                        $lines = $lich->map(function ($lh) {
                            $d = Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                            $s = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                            $e = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                            return "{$d}, {$s}-{$e}";
                        })->all();

                        return implode("\n", $lines);
                    })
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tuan')
                    ->label('Tuần')
                    ->getStateUsing(fn (KhoaHoc $record) =>
                        $record->lichHocs()->pluck('tuan')->filter()->unique()->sortDesc()
                            ->map(fn ($w) => (string) $w)->implode(', ')
                    )
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('trang_thai_hien_thi')
                    ->label('Trạng thái')
                    ->badge()
                    ->getStateUsing(fn (KhoaHoc $record) => $record->trang_thai_hien_thi)
                    ->description(
                        fn (KhoaHoc $record) => $record->trang_thai_hien_thi === 'Tạm hoãn' && filled($record->ly_do_tam_hoan ?? null)
                            ? Str::limit(strip_tags((string) $record->ly_do_tam_hoan), 120)
                            : null,
                        'below'
                    )
                    ->color(fn (string $state) => match ($state) {
                        'Dự thảo'      => 'gray',
                        'Ban hành'     => 'info',
                        'Đang đào tạo' => 'warning',
                        'Kết thúc'     => 'success',
                        'Tạm hoãn'     => 'danger',
                        default        => 'gray',
                    })
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('thoi_gian')
                    ->label('Năm / Tháng')
                    ->form([
                        Forms\Components\Grid::make(12)->schema([
                            Forms\Components\Select::make('nam')
                                ->label('Năm')
                                ->options(fn () =>
                                    KhoaHoc::query()
                                        ->select('nam')->distinct()->orderBy('nam','desc')
                                        ->pluck('nam')->mapWithKeys(fn ($y) => [$y => (string) $y])->toArray()
                                )
                                ->native(false)
                                ->searchable()
                                ->placeholder('')
                                ->columnSpan(6),
                            Forms\Components\Select::make('thang')
                                ->label('Tháng')
                                ->options(fn () => collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => (string) $m])->toArray())
                                ->native(false)
                                ->searchable()
                                ->placeholder('')
                                ->columnSpan(6),
                        ]),
                    ])
                    ->default([
                        'nam'   => (int) now()->format('Y'),
                        'thang' => (int) now()->format('n'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $year  = $data['nam'] ?? null;
                        $month = $data['thang'] ?? null;

                        if (filled($year)) {
                            $query->where('nam', (int) $year);
                        }

                        if (filled($month)) {
                            $query->whereHas('lichHocs', fn ($r) => $r->where('thang', (int) $month));
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (filled($data['nam'] ?? null)) {
                            $indicators[] = Indicator::make('Năm: '.(string) $data['nam']);
                        }
                        if (filled($data['thang'] ?? null)) {
                            $indicators[] = Indicator::make('Tháng: '.(string) $data['thang']);
                        }
                        return $indicators;
                    }),

                Filter::make('ngay_thang')
                    ->label('Ngày/tháng')
                    ->form([
                        Forms\Components\Grid::make(12)->schema([
                            Forms\Components\DatePicker::make('tu_ngay')
                                ->label('Từ ngày')
                                ->displayFormat('d/m/Y')
                                ->native(false)
                                ->columnSpan(6),
                            Forms\Components\DatePicker::make('den_ngay')
                                ->label('Đến ngày')
                                ->displayFormat('d/m/Y')
                                ->native(false)
                                ->columnSpan(6),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['tu_ngay'] ?? null;
                        $to   = $data['den_ngay'] ?? null;

                        if (filled($from)) {
                            $query->whereHas('lichHocs', fn ($r) => $r->whereDate('ngay_hoc', '>=', $from));
                        }

                        if (filled($to)) {
                            $query->whereHas('lichHocs', fn ($r) => $r->whereDate('ngay_hoc', '<=', $to));
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $labels = [];
                        if (filled($data['tu_ngay'] ?? null)) {
                            $labels[] = Indicator::make('Từ: '.Carbon::parse($data['tu_ngay'])->format('d/m/Y'));
                        }
                        if (filled($data['den_ngay'] ?? null)) {
                            $labels[] = Indicator::make('Đến: '.Carbon::parse($data['den_ngay'])->format('d/m/Y'));
                        }
                        return $labels;
                    }),

                Tables\Filters\SelectFilter::make('tuan')
                    ->label('Tuần')
                    ->options(function () {
                        $filters = request()->input('tableFilters', []);
                        $year  = (int) (data_get($filters, 'thoi_gian.data.nam')   ?? now()->year);
                        $month = data_get($filters, 'thoi_gian.data.thang');

                        $q = LichHoc::query()
                            ->whereHas('khoaHoc', fn ($kh) => $kh->where('nam', $year));

                        if ($month) {
                            $q->where('thang', (int) $month);
                        }

                        return $q->select('tuan')->distinct()->orderBy('tuan','desc')->pluck('tuan')
                            ->filter()->unique()->values()
                            ->mapWithKeys(fn ($w) => [$w => (string) $w])->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        return filled($value)
                            ? $query->whereHas('lichHocs', fn ($r) => $r->where('tuan', (int) $value))
                            : $query;
                    }),

                Filter::make('trang_thai')
                    ->label('Trạng thái')
                    ->form([
                        Forms\Components\Select::make('gia_tri')
                            ->label('Trạng thái')
                            ->multiple()
                            ->options(KhoaHoc::trangThaiHienThiOptions())
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $states = $data['gia_tri'] ?? [];
                        if (empty($states)) {
                            return $query;
                        }

                        return $query->whereTrangThaiHienThi((array) $states);
                    })
                    ->indicateUsing(function (array $data): array {
                        $states = collect($data['gia_tri'] ?? [])
                            ->map(fn ($s) => KhoaHoc::trangThaiHienThiOptions()[$s] ?? null)
                            ->filter()
                            ->values();

                        if ($states->isEmpty()) {
                            return [];
                        }

                        return [Indicator::make('Trạng thái: '.implode(', ', $states->all()))];
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Xem'),
                Tables\Actions\EditAction::make()->label('Sửa'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Xóa mục lựa chọn'),
            ]);
    }

    public static function getRelations(): array
    {
        return [ LichHocsRelationManager::class ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListKhoaHocs::route('/'),
            'create' => Pages\CreateKhoaHoc::route('/create'),
            'edit'   => Pages\EditKhoaHoc::route('/{record}/edit'),
            'view'   => Pages\ViewKhoaHoc::route('/{record}'),
        ];
    }
}

