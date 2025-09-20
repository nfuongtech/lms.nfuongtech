<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Models\ChuongTrinh;
use App\Models\ChuyenDe;
use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\QuyTacMaKhoa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Lập Kế hoạch';
    protected static ?string $navigationGroup = 'Đào tạo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin chung')
                    ->schema([
                        Forms\Components\Placeholder::make('ten_va_ma_placeholder')
                            ->label('Tên Khóa học')
                            ->content(fn ($record) =>
                                $record
                                    ? (($record->chuongTrinh?->ten_chuong_trinh ?? 'N/A') . ', ' . $record->ma_khoa_hoc)
                                    : '-'
                            ),

                        Forms\Components\Select::make('chuong_trinh_id')
                            ->label('Chương trình')
                            ->options(fn () =>
                                ChuongTrinh::where('tinh_trang', 'Đang áp dụng')
                                    ->pluck('ten_chuong_trinh', 'id')->toArray()
                            )
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $ct = ChuongTrinh::find($state);
                                    if ($ct) {
                                        try {
                                            if (class_exists(QuyTacMaKhoa::class)) {
                                                $loaiHinhDaoTao = $ct->loai_hinh_dao_tao ?? null;
                                                if ($loaiHinhDaoTao) {
                                                    $set('ma_khoa_hoc', QuyTacMaKhoa::taoMaKhoaHoc($loaiHinhDaoTao));
                                                } else {
                                                    $set('ma_khoa_hoc', 'Tự động tạo khi lưu');
                                                }
                                            }
                                        } catch (\Throwable $e) {
                                            \Log::error('Lỗi tạo mã khóa học: ' . $e->getMessage());
                                            $set('ma_khoa_hoc', 'Tự động tạo khi lưu');
                                        }
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('ma_khoa_hoc')
                            ->label('Mã khóa học')
                            ->disabled()
                            ->dehydrated(true)
                            ->required(),

                        Forms\Components\TextInput::make('nam')
                            ->label('Năm')
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2030)
                            ->default(date('Y'))
                            ->required(),

                        Forms\Components\Select::make('trang_thai')
                            ->label('Trạng thái')
                            ->options([
                                'Dự thảo' => 'Dự thảo',
                                'Ban hành' => 'Ban hành',
                                'Đang đào tạo' => 'Đang đào tạo',
                                'Kết thúc' => 'Kết thúc',
                            ])
                            ->default('Dự thảo')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Lịch học')
                    ->schema([
                        Forms\Components\Repeater::make('lichHocs')
                            ->relationship('lichHocs')
                            ->label('Buổi học')
                            ->collapsible()
                            ->cloneable()
                            ->reorderable()
                            ->grid(2)
                            ->addActionLabel('Thêm buổi học')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('tuan')->label('Tuần')->numeric()->readOnly(),
                                    Forms\Components\TextInput::make('thang')->label('Tháng')->numeric()->readOnly(),
                                    Forms\Components\TextInput::make('nam')->label('Năm')->numeric()->readOnly(),
                                ]),
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\DatePicker::make('ngay_hoc')
                                        ->label('Ngày học')
                                        ->required()
                                        ->minDate(now())
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $date = Carbon::parse($state);
                                                $set('tuan', $date->weekOfYear);
                                                $set('thang', $date->month);
                                                $set('nam', $date->year);
                                            }
                                        }),
                                    Forms\Components\TextInput::make('gio_bat_dau')
                                        ->label('Giờ bắt đầu')
                                        ->required()
                                        ->regex('/^([01]\d|2[0-3]):([0-5]\d)$/')
                                        ->placeholder('HH:MM'),
                                    Forms\Components\TextInput::make('gio_ket_thuc')
                                        ->label('Giờ kết thúc')
                                        ->required()
                                        ->regex('/^([01]\d|2[0-3]):([0-5]\d)$/')
                                        ->placeholder('HH:MM'),
                                ]),
                                Forms\Components\Select::make('chuyen_de_id')
                                    ->label('Chuyên đề')
                                    ->options(fn (callable $get) => $get('../../chuong_trinh_id')
                                        ? ChuyenDe::whereIn('id',
                                            DB::table('chuong_trinh_chuyen_de')->where('chuong_trinh_id', $get('../../chuong_trinh_id'))->pluck('chuyen_de_id')
                                        )->pluck('ten_chuyen_de', 'id')
                                        : []
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('giang_vien_id')
                                    ->label('Giảng viên')
                                    ->options(fn (callable $get) => $get('chuyen_de_id')
                                        ? GiangVien::whereIn('id',
                                            DB::table('chuyen_de_giang_vien')->where('chuyen_de_id', $get('chuyen_de_id'))->pluck('giang_vien_id')
                                        )->where('tinh_trang', 'Đang giảng dạy')->pluck('ho_ten', 'id')
                                        : []
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->defaultItems(1)
                            ->minItems(1)
                            ->maxItems(50)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['lichHocs']) && is_array($data['lichHocs'])) {
            foreach ($data['lichHocs'] as $i => $l) {
                foreach (['gio_bat_dau','gio_ket_thuc'] as $field) {
                    if (!empty($l[$field]) && preg_match('/^\d{2}:\d{2}$/', $l[$field])) {
                        $data['lichHocs'][$i][$field] .= ':00';
                    }
                }
            }
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return static::mutateFormDataBeforeCreate($data);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ma_khoa_hoc')
                    ->label('Mã KH')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ten_hien_thi')
                    ->label('Tên khóa học')
                    ->getStateUsing(fn ($record) =>
                        ($record->ten_khoa_hoc ?? '') ?: (($record->chuongTrinh?->ten_chuong_trinh ?? 'N/A') . ', ' . $record->ma_khoa_hoc)
                    )
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy('ma_khoa_hoc', $direction)
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('trang_thai')
                    ->label('Trạng thái')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $lichs = $record->lichHocs()->orderBy('ngay_hoc')->get();
                        if ($lichs->isEmpty()) {
                            return $record->trang_thai;
                        }
                        $first = Carbon::parse($lichs->first()->ngay_hoc);
                        $last = Carbon::parse($lichs->last()->ngay_hoc);
                        $today = Carbon::today();
                        $effective = $record->trang_thai;
                        if ($last->lt($today)) {
                            $effective = 'Kết thúc';
                        } elseif ($first->lte($today) && $last->gte($today)) {
                            $effective = 'Đang đào tạo';
                        }
                        if ($effective !== $record->trang_thai) {
                            $record->updateQuietly(['trang_thai' => $effective]);
                        }
                        return $effective;
                    })
                    ->badge()
                    ->colors([
                        'primary' => 'Dự thảo',
                        'warning' => 'Ban hành',
                        'success' => 'Đang đào tạo',
                        'danger'  => 'Kết thúc',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('so_buoi')
                    ->label('Số buổi')
                    ->getStateUsing(fn ($record) => $record->lichHocs()->count())
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy(
                            LichHoc::selectRaw('COUNT(*)')
                                ->whereColumn('khoa_hocs.id', 'lich_hocs.khoa_hoc_id'),
                            $direction
                        )
                    )
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tuan')
                    ->label('Tuần')
                    ->getStateUsing(fn ($record) => $record->lichHocs->pluck('tuan')->unique()->join(', '))
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy(
                            LichHoc::select('tuan')->whereColumn('khoa_hocs.id', 'lich_hocs.khoa_hoc_id')->limit(1),
                            $direction
                        )
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('thang_nam')
                    ->label('Tháng/Năm')
                    ->getStateUsing(fn ($record) => $record->lichHocs->isNotEmpty()
                        ? $record->lichHocs->first()->thang . '/' . $record->lichHocs->first()->nam
                        : ''
                    )
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy(
                            LichHoc::select('thang')->whereColumn('khoa_hocs.id', 'lich_hocs.khoa_hoc_id')->limit(1),
                            $direction
                        )
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lich_hoc')
                    ->label('Lịch học')
                    ->getStateUsing(fn ($record) => $record->lichHocs->sortByDesc('ngay_hoc')->map(function ($l) {
                        $ng = $l->ngay_hoc ? Carbon::parse($l->ngay_hoc)->format('d/m/Y') : '';
                        $gbd = substr($l->gio_bat_dau, 0, 5);
                        $gkt = substr($l->gio_ket_thuc, 0, 5);
                        return "{$ng} ({$gbd}-{$gkt})";
                    })->filter()->implode('<br>'))
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy(
                            LichHoc::select('ngay_hoc')->whereColumn('khoa_hocs.id', 'lich_hocs.khoa_hoc_id')->orderBy('ngay_hoc', $direction)->limit(1),
                            $direction
                        )
                    )
                    ->wrap()
                    ->html()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('giang_vien')
                    ->label('Giảng viên')
                    ->getStateUsing(fn ($record) =>
                        implode('<br>', $record->lichHocs->map(fn ($l) => $l->giangVien?->ho_ten)->unique()->filter()->toArray())
                    )
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy(
                            GiangVien::select('ho_ten')
                                ->join('lich_hocs', 'lich_hocs.giang_vien_id', '=', 'giang_viens.id')
                                ->whereColumn('khoa_hocs.id', 'lich_hocs.khoa_hoc_id')
                                ->limit(1),
                            $direction
                        )
                    )
                    ->html()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trang_thai')->label('Trạng thái')
                    ->options([
                        'Dự thảo' => 'Dự thảo',
                        'Ban hành' => 'Ban hành',
                        'Đang đào tạo' => 'Đang đào tạo',
                        'Kết thúc' => 'Kết thúc',
                    ]),
                Tables\Filters\SelectFilter::make('tuan_nam')->label('Tuần/Năm')
                    ->options(fn () => LichHoc::select(DB::raw("CONCAT(tuan,'/',nam) as tn"))->distinct()->pluck('tn', 'tn'))
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['value'] ?? null, function ($q, $value) {
                            [$t, $n] = explode('/', $value);
                            $q->whereHas('lichHocs', fn ($qh) => $qh->where('tuan', $t)->where('nam', $n));
                        })
                    ),
                Tables\Filters\Filter::make('ten_hien_thi')
                    ->label('Tên hoặc mã KH')
                    ->form([
                        Forms\Components\TextInput::make('search')->label('Nhập tên hoặc mã'),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['search'] ?? null, fn ($q, $search) =>
                            $q->where('ma_khoa_hoc', 'like', "%{$search}%")
                              ->orWhere('ten_khoa_hoc', 'like', "%{$search}%")
                              ->orWhereHas('chuongTrinh', fn ($q2) => $q2->where('ten_chuong_trinh', 'like', "%{$search}%"))
                        )
                    ),
                Tables\Filters\SelectFilter::make('nam')->label('Năm')
                    ->options(fn () => LichHoc::select('nam')->distinct()->pluck('nam', 'nam'))
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['value'] ?? null,
                            fn ($q, $value) => $q->whereHas('lichHocs', fn ($qh) => $qh->where('nam', $value))
                        )
                    ),
                Tables\Filters\SelectFilter::make('thang')->label('Tháng')
                    ->options(fn () => LichHoc::select('thang')->distinct()->pluck('thang', 'thang'))
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['value'] ?? null,
                            fn ($q, $value) => $q->whereHas('lichHocs', fn ($qh) => $qh->where('thang', $value))
                        )
                    ),
                Tables\Filters\SelectFilter::make('tuan')->label('Tuần')
                    ->options(fn () => LichHoc::select('tuan')->distinct()->pluck('tuan', 'tuan'))
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['value'] ?? null,
                            fn ($q, $value) => $q->whereHas('lichHocs', fn ($qh) => $qh->where('tuan', $value))
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('toBanHanh')
                    ->label('Ban hành')
                    ->visible(fn ($record) => $record->trang_thai === 'Dự thảo')
                    ->action(fn (KhoaHoc $record) => $record->update(['trang_thai' => 'Ban hành']))
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->color('danger'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListKhoaHocs::route('/'),
            'create' => Pages\CreateKhoaHoc::route('/create'),
            'view'   => Pages\ViewKhoaHoc::route('/{record}'),
            'edit'   => Pages\EditKhoaHoc::route('/{record}/edit'),
        ];
    }
}
