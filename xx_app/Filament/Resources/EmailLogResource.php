<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailLogResource\Pages;
use App\Models\EmailLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Hệ thống';
    protected static ?string $label = 'Email Log';
    protected static ?string $pluralLabel = 'Email Logs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Form này thường không cần thiết nếu chỉ xem log, nhưng có thể giữ lại để tạo log thủ công nếu cần
            Forms\Components\TextInput::make('recipient_email') // Sửa tên cột
                ->label('Người nhận')
                ->required(),

            Forms\Components\TextInput::make('subject')
                ->label('Tiêu đề')
                ->required(),

            Forms\Components\Textarea::make('content') // Sửa tên cột
                ->label('Nội dung')
                ->rows(6),

            Forms\Components\Select::make('status')
                ->label('Trạng thái')
                ->options([
                    'success' => 'Thành công',
                    'failed'  => 'Thất bại',
                    'pending' => 'Đang chờ',
                ])
                ->default('pending'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient_email') // Sửa tên cột
                    ->label('Người nhận')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Tiêu đề')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('emailAccount.name') // Quan hệ với EmailAccount
                    ->label('Tài khoản gửi')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'success',
                        'danger' => 'danger',
                        'warning' => 'warning',
                        'gray' => fn ($state) => ! in_array($state, ['success','failed','pending']),
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'success' => 'Thành công',
                        'failed'  => 'Thất bại',
                        'pending' => 'Đang chờ',
                        default   => 'Không xác định',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian gửi')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailLogs::route('/'),
            'view'  => Pages\ViewEmailLog::route('/{record}'),
        ];
    }
}
