<?php
namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;
    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $modelLabel = 'Mẫu Email';
    protected static ?string $pluralModelLabel = 'Email Templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_mau')->label('Tên mẫu')->required()->columnSpanFull(),
                Forms\Components\Select::make('loai_thong_bao')
                    ->label('Loại thông báo')
                    ->options([
                        'tao_khoa_hoc' => 'Tạo Khóa học', // Thêm mới
                        'ban_hanh' => 'Ban hành Kế hoạch',
                        'thay_doi' => 'Thay đổi Kế hoạch',
                        'tam_hoan' => 'Tạm hoãn Kế hoạch',
                        'them_hoc_vien' => 'Thêm Học viên vào Khóa học', // Thêm mới
                        'ket_thuc_khoa_hoc' => 'Kết thúc Khóa học', // Thêm mới
                    ])
                    ->required(),
                Forms\Components\TextInput::make('tieu_de')->label('Tiêu đề Email')->required(),
                Forms\Components\RichEditor::make('noi_dung')->label('Nội dung Email')
                    ->helperText('Sử dụng các biến sau: {ten_hoc_vien}, {msnv}, {ten_giang_vien}, {ma_khoa_hoc}, {ten_chuong_trinh}, {lich_hoc_chi_tiet}, {ly_do_thay_doi}')
                    ->required()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ten_mau')->label('Tên mẫu')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('loai_thong_bao')->label('Loại thông báo')->badge()->searchable(),
                Tables\Columns\TextColumn::make('tieu_de')->label('Tiêu đề')->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
