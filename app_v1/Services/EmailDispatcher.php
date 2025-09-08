<?php

namespace App\Services;

use App\Mail\TrainingMail;
use App\Models\EmailLog;
use App\Models\EmailProfile;
use App\Models\EmailTemplate;
use App\Models\KhoaHoc;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;

class EmailDispatcher
{
    public function pickProfile(?int $profileId = null): EmailProfile
    {
        if ($profileId) {
            $p = EmailProfile::find($profileId);
            if ($p) return $p;
        }
        return EmailProfile::macDinh()->firstOrFail();
    }

    /**
     * $suKien: ban_hanh|thay_doi|tam_hoan|nhac_nho...
     * $doiTuong: giang_vien|hoc_vien
     * $recipients: array of ['email' => 'a@b.com', 'name' => '...'] (tự lọc null)
     * $vars: biến để render nội dung
     */
    public function sendBatch(
        KhoaHoc $khoaHoc,
        string $suKien,
        string $doiTuong,
        array $recipients,
        array $vars = [],
        ?int $profileId = null,
    ): array {
        $recipients = array_values(array_filter($recipients, fn($r) => !empty($r['email'])));

        // Không có email => ghi log skipped 1 dòng, trả về thống kê
        if (empty($recipients)) {
            EmailLog::create([
                'email_profile_id' => null,
                'khoa_hoc_id'      => $khoaHoc->id,
                'su_kien'          => $suKien,
                'doi_tuong'        => $doiTuong,
                'to_email'         => '',
                'status'           => 'skipped',
                'error_message'    => 'Không có email hợp lệ.',
            ]);
            return ['total' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 1];
        }

        $profile   = $this->pickProfile($profileId);
        $template  = EmailTemplate::for($suKien, $doiTuong) ?? EmailTemplate::for($suKien, 'both');

        if (!$template) {
            // Không có template => fail chung
            $failAll = 0;
            foreach ($recipients as $r) {
                EmailLog::create([
                    'email_profile_id' => $profile->id,
                    'khoa_hoc_id'      => $khoaHoc->id,
                    'su_kien'          => $suKien,
                    'doi_tuong'        => $doiTuong,
                    'to_email'         => $r['email'],
                    'status'           => 'failed',
                    'error_message'    => 'Không tìm thấy mẫu email.',
                ]);
                $failAll++;
            }
            return ['total' => count($recipients), 'sent' => 0, 'failed' => $failAll, 'skipped' => 0];
        }

        // Chuẩn bị mailer "dynamic" từ profile
        config()->set('mail.mailers.dynamic', [
            'transport'  => 'smtp',
            'host'       => $profile->host,
            'port'       => $profile->port,
            'encryption' => $profile->encryption ?: null,
            'username'   => $profile->username,
            'password'   => $profile->password,
            'timeout'    => null,
            'auth_mode'  => null,
        ]);

        $mailer = app('mail.manager')->mailer('dynamic');

        $varsBase = array_merge($vars, [
            'khoa_hoc'    => $khoaHoc,
            'lan_thay_doi'=> $khoaHoc->lan_thay_doi,
            'ly_do_tam_hoan' => $khoaHoc->ly_do_tam_hoan,
        ]);

        $html = Blade::render($template->body_markdown, $varsBase);
        $subject = Blade::render($template->subject, $varsBase);

        $sent = 0; $failed = 0;
        foreach ($recipients as $r) {
            $to = Arr::get($r, 'email');
            $name = Arr::get($r, 'name');

            $log = EmailLog::create([
                'email_profile_id' => $profile->id,
                'khoa_hoc_id'      => $khoaHoc->id,
                'su_kien'          => $suKien,
                'doi_tuong'        => $doiTuong,
                'to_email'         => $to,
                'status'           => 'queued',
            ]);

            try {
                $message = (new TrainingMail($subject, $html))
                    ->from($profile->from_email, $profile->ten_hien_thi);

                if (!empty($profile->reply_to)) {
                    $message->replyTo($profile->reply_to, $profile->ten_hien_thi);
                }

                $mailer->to([$to => $name])->send($message);

                $log->update(['status' => 'sent', 'sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $failed++;
            }
        }

        return ['total' => count($recipients), 'sent' => $sent, 'failed' => $failed, 'skipped' => 0];
    }
}
