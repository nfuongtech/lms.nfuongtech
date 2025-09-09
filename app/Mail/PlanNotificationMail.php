<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tieude;
    public $noidung;

    public function __construct($tieude, $noidung)
    {
        $this->tieude = $tieude;
        $this->noidung = $noidung;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->tieude,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->noidung,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
