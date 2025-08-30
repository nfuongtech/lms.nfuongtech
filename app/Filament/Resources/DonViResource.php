<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonViResource\Pages;
use App\Models\DonVi;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DonViResource extends Resource
{
    protected static ?string $model = DonVi::class;

    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $modelLabel = 'Đơn vị';
    protected static ?string $pluralModelLabel = 'Các đơn vị';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('thaco_tdtv')
                    ->label('THACO/TĐTV')
                    ->required()
                    ->datalist(fn () => DonVi::query()->pluck('thaco_tdtv')->unique()),
                TextInput::make('cong_ty_ban_nvqt')
                    ->label('Công ty/Ban NVQT')
                    ->datalist(fn () => DonVi::query()->pluck('cong_ty_ban_nvqt')->unique()),
                TextInput::make('phong_bo_phan')
                    ->label('Phòng/Bộ phận')
                    ->datalist(fn () => DonVi::query()->pluck('phong_bo_phan')->unique()),
                TextInput::make('noi_lam_viec_chi_tiet')
                    ->label('Nơi làm việc (Xã, Tỉnh/TP)') // Sửa lại tiêu đề
                    ->datalist(fn () => DonVi::query()->pluck('noi_lam_viec_chi_tiet')->unique()), // Thêm gợi ý
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ma_don_vi')->label('Mã đơn vị')->searchable()->sortable(),
                TextColumn::make('ten_hien_thi')->label('Tên hiển thị')->searchable(),
                TextColumn::make('noi_lam_viec_chi_tiet')->label('Nơi làm việc (Xã, Tỉnh/TP)')->searchable(), // Sửa lại tiêu đề
                TextColumn::make('hoc_viens_count')->counts('hocViens')->label('Số lượng HV')->sortable(),
            ])
            ->filters([
                SelectFilter::make('thaco_tdtv')
                    ->label('THACO/TĐTV')
                    ->options(fn () => DonVi::query()->pluck('thaco_tdtv', 'thaco_tdtv')->unique())
                    ->searchable(),
                SelectFilter::make('cong_ty_ban_nvqt')
                    ->label('Công ty/Ban NVQT')
                    ->options(fn () => DonVi::query()->whereNotNull('cong_ty_ban_nvqt')->pluck('cong_ty_ban_nvqt', 'cong_ty_ban_nvqt')->unique())
                    ->searchable(),
                SelectFilter::make('phong_bo_phan')
                    ->label('Phòng/Bộ phận')
                    ->options(fn () => DonVi::query()->whereNotNull('phong_bo_phan')->pluck('phong_bo_phan', 'phong_bo_phan')->unique())
                    ->searchable(),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonVis::route('/'),
            'create' => Pages\CreateDonVi::route('/create'),
            'edit' => Pages\EditDonVi::route('/{record}/edit'),
        ];
    }
}
