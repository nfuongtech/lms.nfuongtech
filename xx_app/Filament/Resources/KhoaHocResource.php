<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Filament\Resources\KhoaHocResource\RelationManagers\LichHocsRelationManager;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\MultiSelectFilter;
use Illuminate\Database\Eloquent\Builder;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Kế hoạch đào tạo';
    protected static ?string $modelLabel = 'Kế hoạch đào tạo';
    protected static ?string $pluralModelLabel = 'Kế hoạch đào tạo';

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        // GIỮ NGUYÊN: Form chi tiết đã nằm ở các Page Create/Edit của bạn
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $q) =>
                $q->with(['lichHocs.giangVien:id,ho_ten,name', 'lichHocs.diaDiemDaoTao:id,ten_phong,ma_phong'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('ma_khoa_hoc')->label('Mã khóa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('ten_khoa_hoc')->label('Tên khóa học')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('giang_vien_tonghop')
                    ->label('Giảng viên')
                    ->formatStateUsing(function (KhoaHoc $record) {
                        $names = $record->lichHocs
                            ->map(fn ($lh) => $lh->giangVien?->ho_ten ?? $lh->giangVien?->name)
                            ->filter()->unique()->values()->all();
                        return implode(', ', $names);
                    }),

                Tables\Columns\TextColumn::make('ngay_gio_text')
                    ->label('Ngày, Giờ đào tạo')
                    ->html()
                    ->formatStateUsing(function (KhoaHoc $record) {
                        $lines = $record->lichHocs
                            ->sortBy([['ngay_hoc','asc'],['gio_bat_dau','asc']])
                            ->map(function ($lh) {
                                $d = $lh->ngay_hoc ? $lh->ngay_hoc->format('d/m/Y') : '';
                                $s = $lh->gio_bat_dau ? substr($lh->gio_bat_dau,0,5) : '';
                                $e = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc,0,5) : '';
                                return trim($d . ', ' . ($s && $e ? "$s-$e" : $s));
                            })
                            ->filter()->values()->all();
                        return implode('<br>', $lines);
                    }),

                Tables\Columns\TextColumn::make('dia_diem_text')
                    ->label('Địa điểm')
                    ->formatStateUsing(function (KhoaHoc $record) {
                        $names = $record->lichHocs
                            ->map(fn ($lh) => $lh->diaDiemDaoTao?->ten_phong ?? $lh->diaDiemDaoTao?->ma_phong)
                            ->filter()->unique()->values()->all();
                        return implode(', ', $names);
                    }),

                Tables\Columns\TextColumn::make('tuan_text')
                    ->label('Tuần')
                    ->formatStateUsing(fn (KhoaHoc $r) => $r->lichHocs->pluck('tuan')->filter()->unique()->sort()->implode(', ')),

                Tables\Columns\BadgeColumn::make('trang_thai_hien_thi')
                    ->label('Trạng thái')
                    ->getStateUsing(fn (KhoaHoc $r) => $r->trang_thai_hien_thi)
                    ->color(fn (string $state) => match ($state) {
                        'Dự thảo'      => 'gray',
                        'Ban hành'     => 'info',
                        'Đang đào tạo' => 'warning',
                        'Kết thúc'     => 'success',
                        'Tạm hoãn'     => 'danger',
                        default        => 'gray',
                    })
                    ->description(fn (KhoaHoc $r) => $r->tam_hoan && $r->ly_do_tam_hoan ? ('Lý do: '.$r->ly_do_tam_hoan) : null),
            ])
            ->filters([
                SelectFilter::make('nam')->label('Năm')
                    ->options(fn () =>
                        KhoaHoc::query()->select('nam')->distinct()->orderBy('nam','desc')->pluck('nam','nam')->toArray()
                    )
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['value'] ?? null, fn ($qq, $v) => $qq->where('nam', $v))
                    ),

                SelectFilter::make('thang')->label('Tháng')
                    ->options(fn () =>
                        LichHoc::query()->select('thang')->whereNotNull('thang')->distinct()->orderBy('thang')->pluck('thang','thang')->toArray()
                    )
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['value'] ?? null, fn ($qq, $v) => $qq->whereHas('lichHocs', fn ($qh) => $qh->where('thang', (int)$v)))
                    ),

                SelectFilter::make('tuan')->label('Tuần')
                    ->options(fn () =>
                        LichHoc::query()->select('tuan')->whereNotNull('tuan')->distinct()->orderBy('tuan','desc')->pluck('tuan','tuan')->toArray()
                    )
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['value'] ?? null, fn ($qq, $v) => $qq->whereHas('lichHocs', fn ($qh) => $qh->where('tuan', (int)$v)))
                    ),

                Filter::make('ngay_thang')->label('Ngày/tháng')
                    ->form([ Forms\Components\DatePicker::make('ngay')->displayFormat('d/m/Y')->native(false) ])
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['ngay'] ?? null, fn ($qq, $ng) => $qq->whereHas('lichHocs', fn ($lh) => $lh->whereDate('ngay_hoc', $ng)))
                    ),

                MultiSelectFilter::make('trang_thai')->label('Trạng thái')
                    ->options([
                        'Dự thảo'      => 'Dự thảo',
                        'Ban hành'     => 'Ban hành',
                        'Đang đào tạo' => 'Đang đào tạo',
                        'Kết thúc'     => 'Kết thúc',
                        'Tạm hoãn'     => 'Tạm hoãn',
                    ])
                    ->query(function (Builder $q, array $data) {
                        $vals = $data['values'] ?? [];
                        if (empty($vals)) return;

                        $q->where(function ($sub) use ($vals) {
                            foreach ($vals as $st) {
                                $sub->orWhere(function ($qq) use ($st) {
                                    if ($st === 'Tạm hoãn') {
                                        $qq->where('tam_hoan', 1);
                                    } elseif ($st === 'Dự thảo') {
                                        $qq->whereDoesntHave('lichHocs');
                                    } else {
                                        $qq->where('tam_hoan', 0)->whereHas('lichHocs');
                                    }
                                });
                            }
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Xem'),
                Tables\Actions\EditAction::make()->label('Sửa'),
                Tables\Actions\DeleteAction::make()->label('Xóa'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Xóa mục lựa chọn'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            LichHocsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        // Trỏ trực tiếp FQCN, tuyệt đối KHÔNG dùng biến::class
        return [
            'index'  => Pages\ListKhoaHocs::route('/'),
            'create' => Pages\CreateKhoaHoc::route('/create'),
            'view'   => Pages\ViewKhoaHoc::route('/{record}'),
            'edit'   => Pages\EditKhoaHoc::route('/{record}/edit'),
        ];
    }
}
