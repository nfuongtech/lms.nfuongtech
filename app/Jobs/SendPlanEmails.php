<?php

namespace App\Jobs;

use App\Models\EmailAccount;
use App\Models\EmailLog;
use App\Models\KhoaHoc;
use App\Mail\PlanNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPlanEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public KhoaHoc $khoaHoc,
        public string $emailSubject,
        public string $emailContentTemplate,
        public ?string $reason = null,
        public ?int $emailAccountId = null
    ) {}

    public function handle(): void
    {
        $account = null;
        if ($this->emailAccountId) {
            $account = EmailAccount::find($this->emailAccountId);
        }
        if (!$account) {
            $account = EmailAccount::getDefault();
        }

        $useDynamicMailer = false;
        if ($account && $account->active) {
            config([
                'mail.mailers.dynamic' => [
                    'transport' => 'smtp',
                    'host' => $account->host,
                    'port' => $account->port,
                    'encryption' => $account->encryption_tls ? 'tls' : null,
                    'username' => $account->username,
                    'password' => $account->password,
                ],
                'mail.from' => [
                    'address' => $account->email,
                    'name' => $account->name,
                ],
            ]);
            $useDynamicMailer = true;
        }

        $recipients = collect();

        foreach ($this->khoaHoc->lichHocs as $lichHoc) {
            if ($lichHoc->giangVien?->user?->email) {
                $recipients->push(['email' => $lichHoc->giangVien->user->email, 'name' => $lichHoc->giangVien->ho_ten ?? null]);
            }
        }

        foreach ($this->khoaHoc->dangKys as $dangKy) {
            if ($dangKy->hocVien?->user?->email) {
                $recipients->push(['email' => $dangKy->hocVien->user->email, 'name' => $dangKy->hocVien->ho_ten ?? null]);
            }
        }

        $recipients = $recipients->unique('email');

        foreach ($recipients as $recipient) {
            $to = $recipient['email'];
            $name = $recipient['name'] ?? $to;

            $content = str_replace(
                ['{ten_nguoi_nhan}', '{ma_khoa_hoc}', '{ten_chuong_trinh}', '{ly_do_thay_doi}'],
                [$name, $this->khoaHoc->ma_khoa_hoc, $this->khoaHoc->chuongTrinh->ten_chuong_trinh ?? '', $this->reason ?? 'N/A'],
                $this->emailContentTemplate
            );

            try {
                if ($useDynamicMailer) {
                    Mail::mailer('dynamic')->to($to)->send(new PlanNotificationMail($this->emailSubject, $content));
                } else {
                    Mail::to($to)->send(new PlanNotificationMail($this->emailSubject, $content));
                }

                EmailLog::create([
                    'khoa_hoc_id' => $this->khoaHoc->id,
                    'email_account_id' => $account?->id,
                    'recipient_email' => $to,
                    'subject' => $this->emailSubject,
                    'content' => $content,
                    'status' => 'success',
                ]);
            } catch (\Throwable $e) {
                EmailLog::create([
                    'khoa_hoc_id' => $this->khoaHoc->id,
                    'email_account_id' => $account?->id,
                    'recipient_email' => $to,
                    'subject' => $this->emailSubject,
                    'content' => $content,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }
}
