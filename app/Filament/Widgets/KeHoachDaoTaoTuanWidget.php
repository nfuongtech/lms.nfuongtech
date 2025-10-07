<?php

namespace App\Filament\Widgets;

use App\Models\KhoaHoc;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class KeHoachDaoTaoTuanWidget extends BaseWidget
{
    protected static ?string $heading = 'Kế hoạch đào tạo - Tuần hiện hành';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            // ✅ Không đụng tới $this->table trước khi init; base query không phụ thuộc filter state
            ->query(function (): Builder {
                return KhoaHoc::query()
                    ->with(['lichHocs.giangVien' => fn ($q) => $q->select(['id','ho_ten'])])
                    ->withSum('lichHocs as tong_gio', 'so_gio_giang')
                    ->orderBy('id', 'desc');
            })
            ->columns([
                Tables\Columns\TextColumn::make('rowIndex')
                    ->label('TT')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('ma_khoa_hoc')
                    ->label('Mã khóa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('ten_khoa_hoc')
                    ->label('Tên khóa học')
                    ->wrap()
                    ->searchable(),

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
                    ->wrap(),

                Tables\Columns\TextColumn::make('tong_gio')
                    ->label('Tổng giờ')
                    ->alignRight()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => (string) (int) ($state ?? 0)),

                Tables\Columns\TextColumn::make('ngay_gio_list')
                    ->label('Ngày, Giờ đào tạo')
                    ->getStateUsing(function (KhoaHoc $record) {
                        $lich = $record->lichHocs()
                            ->orderBy('ngay_hoc')
                            ->orderBy('gio_bat_dau')
                            ->get(['ngay_hoc','gio_bat_dau','gio_ket_thuc']);

                        if ($lich->isEmpty()) {
                            return '—';
                        }

                        $lines = $lich->map(function ($lh) {
                            $d = Carbon::parse($lh->ngay_hoc)->format('d/m/Y');
                            $s = $lh->gio_bat_dau ? substr($lh->gio_bat_dau, 0, 5) : '';
                            $e = $lh->gio_ket_thuc ? substr($lh->gio_ket_thuc, 0, 5) : '';
                            return "{$d}, {$s}-{$e}";
                        })->all();

                        return implode("\n", $lines);
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('tuan')
                    ->label('Tuần')
                    ->getStateUsing(fn (KhoaHoc $record) =>
                        $record->lichHocs()->pluck('tuan')->filter()->unique()->sortDesc()
                            ->map(fn ($w) => (string) $w)->implode(', ')
                    ),

                Tables\Columns\BadgeColumn::make('trang_thai')
                    ->label('Trạng thái')
                    ->getStateUsing(function (KhoaHoc $record) {
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
                    ->color(fn (string $state) => match ($state) {
                        'Dự thảo'      => 'gray',
                        'Ban hành'     => 'info',
                        'Đang đào tạo' => 'warning',
                        'Kết thúc'     => 'success',
                        default        => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->label('Năm')
                    ->options(function () {
                        $years = KhoaHoc::query()
                            ->select('nam')
                            ->distinct()
                            ->orderBy('nam','desc')
                            ->pluck('nam')
                            ->all();
                        if (empty($years)) $years = [(int) now()->format('Y')];
                        return collect($years)->mapWithKeys(fn ($y) => [$y => (string) $y])->toArray();
                    })
                    ->default((int) now()->format('Y'))
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        return filled($value) ? $query->where('nam', (int) $value) : $query;
                    }),

                Tables\Filters\SelectFilter::make('week')
                    ->label('Tuần đào tạo')
                    ->options(function () {
                        $opts = [];
                        for ($i = 1; $i <= 53; $i++) $opts[$i] = (string) $i;
                        return $opts;
                    })
                    ->default((int) now()->isoWeek())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        return filled($value)
                            ? $query->whereHas('lichHocs', fn ($q) => $q->where('tuan', (int) $value))
                            : $query;
                    }),
            ]);
    }
}
