<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChuyenDeResource\Pages;
use App\Models\ChuyenDe;
use App\Models\TuyChonKetQua;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class ChuyenDeResource extends Resource
{
    protected static ?string $model = ChuyenDe::class;

    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $modelLabel = 'Chuyên đề/Học phần';
    protected static ?string $pluralModelLabel = 'Chuyên đề/Học phần';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ma_so')
                    ->label('Mã số')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('ten_chuyen_de')
                    ->label('Tên Chuyên đề/Học phần')
                    ->required(),
                TextInput::make('thoi_luong')
                    ->label('Thời lượng (giờ)')
                    ->numeric()
                    ->required(),
                TextInput::make('doi_tuong_dao_tao')
                    ->label('Đối tượng đào tạo')
                    ->required(),

                // Giảng viên nhiều-nhiều
                Select::make('giangViens')
                    ->label('Giảng viên')
                    ->relationship('giangViens', 'ho_ten')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Select::make('trang_thai_tai_lieu')
                    ->label('Trạng thái tài liệu')
                    ->options(fn () => TuyChonKetQua::where('loai', 'trang_thai_tai_lieu')->pluck('gia_tri', 'gia_tri'))
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('gia_tri')
                            ->label('Giá trị tùy chọn mới')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data): string {
                        $newOption = TuyChonKetQua::create([
                            'loai' => 'trang_thai_tai_lieu',
                            'gia_tri' => $data['gia_tri']
                        ]);
                        return $newOption->gia_tri;
                    }),

                FileUpload::make('bai_giang_path')
                    ->label('Bài giảng kèm theo')
                    ->directory('bai-giang')
                    ->multiple()
                    ->reorderable()
                    ->appendFiles(),

                Textarea::make('muc_tieu')
                    ->label('Mục tiêu')
                    ->columnSpanFull(),
                Textarea::make('noi_dung')
                    ->label('Nội dung')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ma_so')->label('Mã số')->searchable()->sortable(),
                TextColumn::make('ten_chuyen_de')->label('Tên Chuyên đề/Học phần')->searchable(),
                TextColumn::make('thoi_luong')->label('Thời lượng (giờ)'),
                TextColumn::make('giangViens.ho_ten')->label('Giảng viên')->badge(),
                TextColumn::make('trang_thai_tai_lieu')
                    ->label('Trạng thái tài liệu')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Đã ban hành' => 'success',
                        'Đang biên soạn' => 'warning',
                        'Đã thẩm định' => 'info',
                        default => 'gray',
                    }),
                ViewColumn::make('bai_giang_path')
                    ->label('Tài liệu')
                    ->view('tables.columns.document-links'),
            ])
            ->filters([])
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
            'index' => Pages\ListChuyenDes::route('/'),
            'create' => Pages\CreateChuyenDe::route('/create'),
            'edit' => Pages\EditChuyenDe::route('/{record}/edit'),
        ];
    }
}
