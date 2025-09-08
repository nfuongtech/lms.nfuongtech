<?php
namespace App\Filament\Resources\DangKyResource\Pages;
use App\Filament\Resources\DangKyResource;
use App\Filament\Widgets\KhoaHocGhiDanhWidget;
use App\Models\DangKy;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListDangKies extends ListRecords
{
    protected static string $resource = DangKyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KhoaHocGhiDanhWidget::class,
        ];
    }
    
    protected function getHeaderWidgetsData(): array
    {
        return [
            'filters' => $this->tableFilters,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(DangKy::query())
            ->heading('Block 3: Danh sách học viên đã ghi danh')
            ->columns([
                Tables\Columns\TextColumn::make('hocVien.msnv')->label('MSNV')->searchable(),
                Tables\Columns\TextColumn::make('hocVien.ho_ten')->label('Họ tên')->searchable(),
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Mã Khóa học')->searchable(),
                Tables\Columns\TextColumn::make('khoaHoc.chuongTrinh.ten_chuong_trinh')->label('Tên chương trình'),
            ])
            ->filters([
                Select::make('tuan')->label('Tuần')->options(array_combine(range(1, 52), range(1, 52)))->placeholder('Tất cả')->live(),
                Select::make('thang')->label('Tháng')->options(array_combine(range(1, 12), range(1, 12)))->placeholder('Tất cả')->live(),
                Select::make('nam')->label('Năm')->options(array_combine(range(2024, 2030), range(2024, 2030)))->default(now()->year)->live(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->afterFiltersUpdated(function () {
                $this->dispatch('filtersChanged', $this->tableFilters);
            });
    }
}
