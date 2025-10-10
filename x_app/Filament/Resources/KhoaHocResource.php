<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Filament\Resources\KhoaHocResource\RelationManagers\LichHocsRelationManager;
use App\Models\KhoaHoc;
use App\Models\ChuongTrinh;
use App\Models\QuyTacMaKhoa;
use App\Models\GiangVien;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;

    protected static ?string $navigationLabel  = 'Kế hoạch đào tạo';
    protected static ?string $pluralLabel      = 'Kế hoạch đào tạo';
    protected static ?string $modelLabel       = 'Kế hoạch đào tạo';
    protected static ?string $navigationIcon   = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup  = 'Đào tạo';

    public static function getEloquentQuery(): Builder
    {
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
                    \Filament\Forms\Components\Actions\Action::make('sua')
                        ->label('Sửa')
                        ->visible(fn () => request()->routeIs('*.edit'))
                        ->action(fn (Set $set) => $set('edit_mode', true)),
                ])
                ->schema([
                    Forms\Components\Select::make('chuong_trinh_id')
                        ->label('Chương trình')
                        ->options(fn () =>
                            ChuongTrinh::query()->orderBy('ten_chuong_trinh')->pluck('ten_chuong_trinh', 'id')->toArray()
                        )
                        ->searchable()->preload()->required()
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
                        ->maxLength(255)->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\Radio::make('che_do_ma_khoa')
                        ->label('Quy tắc mã khóa')
                        ->options([
                            'auto'   => 'Chọn tự động (theo Quy tắc mã khóa)',
                            'manual' => 'Tự nhập thủ công',
                        ])
                        ->inline()->default('auto')->reactive()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            if ($state === 'manual') return;
                            $ctId = $get('chuong_trinh_id');
                            $ct   = $ctId ? ChuongTrinh::find($ctId) : null;
                            if ($ct?->loai_hinh_dao_tao) {
                                $set('ma_khoa_hoc', (string) QuyTacMaKhoa::taoMaKhoaHoc($ct->loai_hinh_dao_tao));
                            }
                        })
                        ->dehydrated(false)
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('ma_khoa_hoc')
                        ->label('Mã khóa học')->maxLength(64)->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('nam')
                        ->label('Năm')->numeric()->minValue(2000)->maxValue(2099)
                        ->default((int) now()->format('Y'))->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('yeu_cau_phan_tram_gio')
                        ->label('Yêu cầu % giờ học (>=)')
                        ->numeric()->step(1)->integer()->minValue(1)->maxValue(100)
                        ->default(80)->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),

                    Forms\Components\TextInput::make('yeu_cau_diem_tb')
                        ->label('Yêu cầu điểm trung bình (>=)')
                        ->numeric()->minValue(0)->maxValue(10)->step(0.1)
                        ->default(5.0)->required()
                        ->disabled(fn (Get $get) => request()->routeIs('*.edit') && !$get('edit_mode')),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rowIndex')->label('TT')->rowIndex(),

                Tables\Columns\TextColumn::make('ma_khoa_hoc')
                    ->label('Mã khóa')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ten_khoa_hoc')
                    ->label('Tên khóa học')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('giang_vien_text')
                    ->label('Giảng viên')
                    ->getStateUsing(function (KhoaHoc $record) {
                        $ids = $record->lichHocs()->pluck('giang_vien_id')->filter()->unique()->values();
                        if ($ids->isEmpty()) return '—';
                        return GiangVien::query()->whereIn('id', $ids)->pluck('ho_ten')->filter()->implode(', ');
                    })
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tong_gio')
                    ->label('Tổng giờ')
                    ->getStateUsing(fn (KhoaHoc $record) => number_format((float) ($record->lichHocs()->sum('so_gio_giang') ?? 0), 1))
                    ->formatStateUsing(fn ($state) => (string) $state)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ngay_gio')
                    ->label('Ngày, Giờ đào tạo')
                    ->getStateUsing(function (KhoaHoc $record) {
                        return $record->lichHocs()->orderBy('ngay_hoc')
                            ->get()
                            ->map(function ($lh) {
                                $date  = Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                                $start = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                                $end   = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                                return "{$date}, {$start}-{$end}";
                            })
                            ->implode("\n");
                    })
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dia_diem')
                    ->label('Địa điểm')
                    ->getStateUsing(function (KhoaHoc $record) {
                        $vals = $record->lichHocs()->with('diaDiem')->get()
                            ->map(fn ($lh) => $lh->dia_diem ?: optional($lh->diaDiem)->ten_phong)
                            ->filter()->unique()->values();
                        return $vals->isNotEmpty() ? $vals->implode(', ') : '—';
                    })
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tuan')
                    ->label('Tuần')
                    ->getStateUsing(fn (KhoaHoc $record) =>
                        $record->lichHocs()->pluck('tuan')->filter()->unique()->sortDesc()->implode(', ')
                    )
                    ->toggleable(),

                // Badge trạng thái + lý do tạm hoãn ở dòng dưới
                Tables\Columns\BadgeColumn::make('trang_thai')
                    ->label('Trạng thái')
                    ->getStateUsing(function (KhoaHoc $record) {
                        if ($record->tam_hoan) return 'Tạm hoãn';
                        $qs = $record->lichHocs()->select('ngay_hoc','gio_bat_dau','gio_ket_thuc');
                        if (!$qs->exists()) return 'Dự thảo';

                        $all = $qs->get()->map(function ($lh) {
                            $day = $lh->ngay_hoc instanceof \DateTimeInterface
                                ? Carbon::instance($lh->ngay_hoc)->startOfDay()
                                : Carbon::parse($lh->ngay_hoc)->startOfDay();
                            $start = (clone $day)->setTimeFromTimeString($lh->gio_bat_dau ?: '00:00:00');
                            $end   = (clone $day)->setTimeFromTimeString($lh->gio_ket_thuc ?: '23:59:59');
                            return compact('start','end');
                        });

                        $minStart = $all->min('start');
                        $maxEnd   = $all->max('end');
                        $now = now();

                        if ($now->lt($minStart)) return 'Ban hành';
                        if ($now->between($minStart, $maxEnd)) return 'Đang đào tạo';
                        return 'Kết thúc';
                    })
                    ->description(fn (KhoaHoc $record) =>
                        $record->tam_hoan && filled($record->ly_do_tam_hoan)
                            ? (string) $record->ly_do_tam_hoan
                            : null
                    )
                    ->color(function (?KhoaHoc $record = null, ?string $state = null) {
                        if (!$record) return 'gray';
                        if ($record->tam_hoan) return 'danger';

                        $state = $state ?? (string) ($record->computeTrangThai() ?? 'Ban hành');

                        if ($state === 'Đang đào tạo') {
                            $hasToday = $record->lichHocs()
                                ->whereDate('ngay_hoc', now()->toDateString())
                                ->exists();
                            return $hasToday ? 'success' : 'warning';
                        }

                        return match ($state) {
                            'Dự thảo'  => 'gray',
                            'Ban hành' => 'info',
                            'Kết thúc' => 'gray',
                            default    => 'gray',
                        };
                    })
                    ->sortable(false)
                    ->toggleable(),

                // Giữ cột lý do tạm hoãn (ẩn) để không coi là lược bỏ
                Tables\Columns\TextColumn::make('ly_do_tam_hoan')
                    ->label('Lý do tạm hoãn (ẩn)')
                    ->visible(false)
                    ->wrap()
                    ->toggleable(),
            ])
            // Filters của trang List định nghĩa trong ListKhoaHocs
            ->filters([])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make()->label('Xem'),
                \Filament\Tables\Actions\EditAction::make()->label('Sửa'),
                \Filament\Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->modalHeading('Xóa Kế hoạch đào tạo')
                    ->modalSubheading('Bạn chắc chắn xóa Kế hoạch này, việc xóa sẽ không phục hồi lại được?')
                    ->modalSubmitActionLabel('Xóa')
                    ->successNotificationTitle('Đã xóa Kế hoạch đào tạo'),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make()->label('Xóa đã chọn'),
                ]),
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
