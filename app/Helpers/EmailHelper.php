<?php

namespace App\Helpers;

use App\Models\EmailAccount;
use App\Models\EmailTemplate;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Mail\PlanNotificationMail; // Giả định bạn đã có Mailable này

class EmailHelper
{
    /**
     * Gửi email sử dụng mẫu và tài khoản được chỉ định.
     *
     * @param string $loaiThongBao Loại thông báo từ bảng email_templates (ví dụ: 'ban_hanh')
     * @param array $placeholders Mảng thay thế biến trong mẫu email, ví dụ: ['{ten_hoc_vien}' => 'Nguyễn Văn A', ...]
     * @param string $recipientEmail Địa chỉ email người nhận
     * @param string|null $recipientName Tên người nhận (tùy chọn)
     * @param EmailAccount|null $emailAccount Tài khoản gửi (nếu null sẽ dùng tài khoản mặc định)
     * @return bool
     */
    public static function guiEmailMau(string $loaiThongBao, array $placeholders, string $recipientEmail, string $recipientName = null, EmailAccount $emailAccount = null): bool
    {
        // 1. Tìm template
        $template = EmailTemplate::where('loai_thong_bao', $loaiThongBao)->first();
        if (!$template) {
            \Log::warning("Không tìm thấy mẫu email cho loại: $loaiThongBao");
            return false;
        }

        // 2. Tìm tài khoản gửi
        if (!$emailAccount) {
            $emailAccount = EmailAccount::where('is_default', 1)->where('active', 1)->first();
        }
        if (!$emailAccount) {
            \Log::warning("Không tìm thấy tài khoản email gửi (mặc định hoặc được chỉ định).");
            return false;
        }

        // 3. Thay thế biến trong tiêu đề và nội dung
        $tieuDe = $template->tieu_de;
        $noiDung = $template->noi_dung;

        foreach ($placeholders as $placeholder => $value) {
            $tieuDe = str_replace($placeholder, $value, $tieuDe);
            $noiDung = str_replace($placeholder, $value, $noiDung);
        }

        // 4. Cấu hình mailer động
        Config::set('mail.mailers.dynamic', [
            'transport' => 'smtp',
            'host' => $emailAccount->host,
            'port' => $emailAccount->port,
            'encryption' => $emailAccount->encryption_tls ? 'tls' : null,
            'username' => $emailAccount->username,
            'password' => $emailAccount->password,
        ]);
        Config::set('mail.from', [
            'address' => $emailAccount->email,
            'name' => $emailAccount->name,
        ]);

        // 5. Gửi email
        $success = true;
        $errorMessage = null;
        try {
            Mail::mailer('dynamic')->to($recipientEmail, $recipientName)->send(new PlanNotificationMail($tieuDe, $noiDung));
        } catch (\Throwable $e) {
            $success = false;
            $errorMessage = $e->getMessage();
            \Log::error("Lỗi gửi email mẫu '$loaiThongBao' tới $recipientEmail: " . $e->getMessage());
        }

        // 6. Lưu log
        EmailLog::create([
            'email_account_id' => $emailAccount->id,
            'recipient_email' => $recipientEmail,
            'subject' => $tieuDe,
            'content' => $noiDung,
            'status' => $success ? 'success' : 'failed',
            'error_message' => $errorMessage,
        ]);

        return $success;
    }
}
