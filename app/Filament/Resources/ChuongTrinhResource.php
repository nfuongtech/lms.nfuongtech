<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChuongTrinhResource\Pages;
use App\Models\ChuongTrinh;
use App\Models\TuyChonKetQua;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChuongTrinhResource extends Resource
{
    protected static ?string $model = ChuongTrinh::class;

    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $modelLabel = 'Chương trình';
    protected static ?string $pluralModelLabel = 'Các Chương trình';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_chuong_trinh')
                    ->label('Tên chương trình')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('thoi_luong')
                    ->label('Thời lượng (giờ)')
                    ->numeric()
                    ->step(0.5)
                    ->required(),
                Forms\Components\Textarea::make('muc_tieu_dao_tao')
                    ->label('Mục tiêu đào tạo')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('loai_hinh_dao_tao')
                    ->label('Loại hình đào tạo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('tinh_trang')
                    ->label('Tình trạng')
                    ->options(fn () => TuyChonKetQua::where('loai', 'tinh_trang_chuong_trinh')->pluck('gia_tri', 'gia_tri'))
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('gia_tri')
                            ->label('Tên trạng thái mới')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data): string {
                        $newOption = TuyChonKetQua::create([
                            'loai' => 'tinh_trang_chuong_trinh',
                            'gia_tri' => $data['gia_tri']
                        ]);
                        return $newOption->gia_tri;
                    }),
                Forms\Components\Select::make('chuyenDes')
                    ->label('Các chuyên đề thuộc chương trình')
                    ->relationship('chuyenDes', 'ten_chuyen_de')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ma_chuong_trinh')
                    ->label('Mã Chương trình')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ten_chuong_trinh')
                    ->label('Tên chương trình')
                    ->searchable(),
                Tables\Columns\TextColumn::make('thoi_luong')
                    ->label('Thời lượng (giờ)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loai_hinh_dao_tao')
                    ->label('Loại hình đào tạo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tinh_trang')
                    ->label('Tình trạng')
                    ->badge(),
            ])
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
            'index' => Pages\ListChuongTrinhs::route('/'),
            'create' => Pages\CreateChuongTrinh::route('/create'),
            'edit' => Pages\EditChuongTrinh::route('/{record}/edit'),
        ];
    }    
}
