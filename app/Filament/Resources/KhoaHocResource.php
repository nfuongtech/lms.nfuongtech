<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Models\KhoaHoc;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $modelLabel = 'Kế hoạch Đào tạo';
    protected static ?string $pluralModelLabel = 'Kế hoạch Đào tạo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('chuyen_de_id')->relationship('chuyenDe', 'ten_chuyen_de')->label('Chuyên đề')->required(),
                TextInput::make('ten_khoa_hoc')->label('Tên Khóa học / Lớp')->required(),
                TextInput::make('nam')->label('Năm')->numeric()->default(now()->year)->required(),
                Repeater::make('lichHocs')
                    ->label('Lịch học các buổi')
                    ->relationship()
                    ->schema([
                        DatePicker::make('ngay_hoc')->label('Ngày học')->required(),
                        TimePicker::make('gio_bat_dau')->label('Giờ bắt đầu')->seconds(false)->required(),
                        TimePicker::make('gio_ket_thuc')->label('Giờ kết thúc')->seconds(false)->required(),
                        Select::make('giang_vien_id')->label('Giảng viên')->relationship('giangVien', 'ho_ten')->searchable()->preload(),
                        TextInput::make('dia_diem')->label('Địa điểm'),
                    ])
                    ->columns(2)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ten_khoa_hoc')->label('Tên Khóa học / Lớp')->searchable(),
                TextColumn::make('chuyenDe.ten_chuyen_de')->label('Chuyên đề')->searchable(),
                TextColumn::make('trang_thai')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Soạn thảo' => 'gray',
                        'Đã ban hành' => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Nút "Ban hành Kế hoạch"
                Action::make('ban_hanh')
                    ->label('Ban hành')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation() // Yêu cầu xác nhận trước khi thực hiện
                    ->action(function (KhoaHoc $record) {
                        $record->update(['trang_thai' => 'Đã ban hành']);
                        // Gửi thông báo thành công
                        Notification::make()
                            ->title('Ban hành thành công')
                            ->body('Kế hoạch đào tạo đã được ban hành.')
                            ->success()
                            ->send();
                        // (Tùy chọn) Gửi email thông báo đến giảng viên và học viên tại đây
                    })
                    // Chỉ hiển thị nút này khi khóa học đang ở trạng thái "Soạn thảo"
                    ->visible(fn (KhoaHoc $record): bool => $record->trang_thai === 'Soạn thảo'),
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
            'index' => Pages\ListKhoaHocs::route('/'),
            'create' => Pages\CreateKhoaHoc::route('/create'),
            'edit' => Pages\EditKhoaHoc::route('/{record}/edit'),
        ];
    }
}
