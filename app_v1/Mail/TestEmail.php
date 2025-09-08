<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('Test Email Connection')
            ->view('emails.test')
            ->with(['message' => 'Kết nối SMTP thành công!']);
    }
}
