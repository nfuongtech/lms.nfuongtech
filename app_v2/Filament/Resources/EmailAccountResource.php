<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailAccountResource\Pages;
use App\Models\EmailAccount;
use App\Models\EmailLog;
use Filament\Forms;
use Filament\Forms\Form as FormsForm;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as TablesTable;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class EmailAccountResource extends Resource
{
    protected static ?string $model = EmailAccount::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Cấu hình hệ thống';
    protected static ?string $label = 'Tài khoản Email';
    protected static ?string $pluralLabel = 'Danh sách Email Accounts';

    public static function form(FormsForm $form): FormsForm
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Tên hiển thị')->required(),
            Forms\Components\TextInput::make('email')->label('Địa chỉ Email')->email()->required(),
            Forms\Components\TextInput::make('host')->label('SMTP Host')->required(),
            Forms\Components\TextInput::make('port')->label('Cổng')->numeric()->default(587)->required(),
            Forms\Components\TextInput::make('username')->label('Username')->nullable(),
            Forms\Components\TextInput::make('password')
                ->label('Password / SMTP secret')
                ->password()
                ->revealable()
                ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
            Forms\Components\Toggle::make('encryption_tls')->label('TLS Encryption')->default(true),
            Forms\Components\Toggle::make('active')->label('Kích hoạt')->default(true),
            Forms\Components\Toggle::make('is_default')->label('Mặc định')->default(false)
                ->helperText('Chỉ một tài khoản được đặt mặc định cho hệ thống'),
        ]);
    }

    public static function table(TablesTable $table): TablesTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Tên hiển thị')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email'),
                Tables\Columns\TextColumn::make('host')->label('SMTP Host'),
                Tables\Columns\IconColumn::make('encryption_tls')->label('TLS')->boolean(),
                Tables\Columns\IconColumn::make('active')->label('Kích hoạt')->boolean(),
                Tables\Columns\IconColumn::make('is_default')->label('Mặc định')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Tạo')->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('test')
                    ->label('Test gửi')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        Forms\Components\TextInput::make('test_email')
                            ->label('Gửi tới (Email)')
                            ->email()
                            ->required(),
                    ])
                    ->action(function (array $data, EmailAccount $record) {
                        $to = $data['test_email'];
                        $subject = "Test Email từ {$record->name}";
                        $content = "<p>Đây là email test từ tài khoản: <strong>{$record->email}</strong>.</p>";

                        try {
                            config([
                                'mail.mailers.dynamic' => [
                                    'transport' => 'smtp',
                                    'host' => $record->host,
                                    'port' => $record->port,
                                    'encryption' => $record->encryption_tls ? 'tls' : null,
                                    'username' => $record->username,
                                    'password' => $record->password,
                                ],
                                'mail.from' => [
                                    'address' => $record->email,
                                    'name' => $record->name,
                                ],
                            ]);

                            Mail::mailer('dynamic')->to($to)->send(new \App\Mail\PlanNotificationMail($subject, $content));

                            EmailLog::create([
                                'email_account_id' => $record->id,
                                'recipient_email' => $to,
                                'subject' => $subject,
                                'content' => $content,
                                'status' => 'success',
                            ]);

                            Notification::make()->title('Gửi email test thành công!')->success()->send();
                        } catch (\Throwable $e) {
                            EmailLog::create([
                                'email_account_id' => $record->id,
                                'recipient_email' => $to,
                                'subject' => $subject,
                                'content' => $content,
                                'status' => 'failed',
                                'error_message' => $e->getMessage(),
                            ]);

                            Notification::make()->title('Lỗi khi gửi email test')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->color('primary'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailAccounts::route('/'),
            'create' => Pages\CreateEmailAccount::route('/create'),
            'edit' => Pages\EditEmailAccount::route('/{record}/edit'),
        ];
    }
}
