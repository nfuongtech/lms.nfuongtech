<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailLogResource\Pages;
use App\Models\EmailLog;
use Filament\Resources\Resource;
use Filament\Tables;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;
    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $modelLabel = 'Nhật ký Email';
    protected static ?string $pluralModelLabel = 'Nhật ký Email';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Khóa học'),
            Tables\Columns\TextColumn::make('su_kien')->label('Sự kiện'),
            Tables\Columns\TextColumn::make('doi_tuong')->label('Đối tượng'),
            Tables\Columns\TextColumn::make('to_email')->label('Người nhận'),
            Tables\Columns\BadgeColumn::make('status')->colors([
                'warning' => 'queued',
                'success' => 'sent',
                'danger'  => 'failed',
                'gray'    => 'skipped',
            ])->label('Trạng thái'),
            Tables\Columns\TextColumn::make('error_message')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('sent_at')->dateTime('d/m/Y H:i')->label('Thời gian gửi'),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i'),
        ])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
        'index' => Pages\ListEmailLogs::route('/'),
    ];
    }
}
