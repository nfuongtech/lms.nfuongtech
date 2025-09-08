<?php

namespace App\Filament\Widgets;

use App\Models\KhoaHoc;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ApprovedCoursesWidget extends BaseWidget
{
    protected static ?int $sort = 2; // Sắp xếp widget này ở vị trí thứ 2 trên dashboard

    protected int|string|array $columnSpan = 'full'; // Cho phép widget chiếm toàn bộ chiều rộng

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KhoaHoc::query()->where('trang_thai', 'Đã ban hành')
            )
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('date_range')
                    ->label('Phạm vi thời gian')
                    ->options([
                        'week' => 'Tuần này',
                        'month' => 'Tháng này',
                    ])
                    ->default('month')
                    ->query(function ($query, array $data) {
                        $range = $data['value'];

                        if ($range === 'week') {
                            return $query->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        }

                        return $query->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    })
            ])
            ->columns([
                TextColumn::make('chuyenDe.ten_chuyen_de')->label('Tên Chuyên đề'),
                TextColumn::make('ten_khoa_hoc')->label('Khóa học / Lớp'),
                TextColumn::make('updated_at')->label('Ngày ban hành')->date('d/m/Y'),
            ]);
    }
}
