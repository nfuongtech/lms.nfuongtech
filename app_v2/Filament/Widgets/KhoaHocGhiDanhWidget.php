<?php

namespace App\Filament\Widgets;

use App\Models\KhoaHoc;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class KhoaHocGhiDanhWidget extends BaseWidget
{
    protected static ?string $heading = 'Block 2: Các khóa học có học viên ghi danh';
    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['filtersChanged' => 'updateFilters'];

    public ?array $filters = [];

    public function updateFilters($filters): void
    {
        $this->filters = $filters;
        $this->dispatch('$refresh'); // refresh lại widget
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KhoaHoc::query()
                    ->whereHas('dangKys')
                    ->when($this->filters, function ($query) {
                        $query->whereHas('lichHocs', function (Builder $q) {
                            if (!empty($this->filters['tuan']['value'])) {
                                $q->where('tuan', $this->filters['tuan']['value']);
                            }
                            if (!empty($this->filters['thang']['value'])) {
                                $q->where('thang', $this->filters['thang']['value']);
                            }
                            if (!empty($this->filters['nam']['value'])) {
                                $q->where('nam', $this->filters['nam']['value']);
                            }
                        });
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('chuongTrinh.ma_chuong_trinh')->label('Mã chương trình'),
                Tables\Columns\TextColumn::make('chuongTrinh.ten_chuong_trinh')->label('Tên chương trình'),
                Tables\Columns\TextColumn::make('chuongTrinh.thoi_luong')->label('Thời lượng (giờ)'),

                Tables\Columns\TextColumn::make('lichHocs')
                    ->label('Ngày, giờ & Địa điểm')
                    ->formatStateUsing(function ($state, $record) {
                        $lichHocs = $record->lichHocs instanceof Collection ? $record->lichHocs : collect();
                        return $lichHocs->map(fn ($lh) => sprintf(
                            "%s<br>%s - %s, %s",
                            Carbon::parse($lh->ngay_hoc)->format('d/m/Y'),
                            $lh->gio_bat_dau,
                            $lh->gio_ket_thuc,
                            $lh->dia_diem ?? 'N/A'
                        ))->implode('<br>');
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('dang_kys_count')
                    ->counts('dangKys')
                    ->label('Tổng số HV'),
            ]);
    }
}
