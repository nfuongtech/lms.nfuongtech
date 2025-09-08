<?php

namespace App\Filament\Resources\EmailAccountResource\Pages;

use App\Filament\Resources\EmailAccountResource;
use App\Mail\TestEmail;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class EditEmailAccount extends EditRecord
{
    protected static string $resource = EmailAccountResource::class;

    protected function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('testConnection')
                ->label('Test Connection')
                ->action('testConnection'),
        ];
    }

    public function testConnection(): void
    {
        $email = $this->record->email;

        try {
            Mail::to($email)->send(new TestEmail());

            Notification::make()
                ->title("✅ Đã gửi test email đến {$email}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title("❌ Lỗi gửi email: " . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
