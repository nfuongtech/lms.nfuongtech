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
                TextInput::make('thaco_tdtv')->label('THACO/TĐTV')->required(),
                TextInput::make('cong_ty_ban_nvqt')->label('Công ty/Ban NVQT'),
                TextInput::make('phong_bo_phan')->label('Phòng/Bộ phận'),
                TextInput::make('noi_lam_viec_chi_tiet')->label('Nơi làm việc (Xã, Tỉnh/TP)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ma_don_vi')->label('Mã đơn vị')->sortable()->searchable(),
                TextColumn::make('ten_hien_thi')->label('Tên hiển thị')->sortable()->searchable(),
                TextColumn::make('noi_lam_viec_chi_tiet')->label('Nơi làm việc'),
                TextColumn::make('hoc_viens_count')->counts('hocViens')->label('Số HV'),
            ])
            ->filters([
                SelectFilter::make('thaco_tdtv')
                    ->options(fn () => DonVi::query()->pluck('thaco_tdtv', 'thaco_tdtv')->unique())
                    ->label('THACO/TĐTV'),
                SelectFilter::make('cong_ty_ban_nvqt')
                    ->options(fn () => DonVi::query()->pluck('cong_ty_ban_nvqt', 'cong_ty_ban_nvqt')->unique())
                    ->label('Công ty/Ban NVQT'),
            ], layout: FiltersLayout::AboveContent)
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
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
