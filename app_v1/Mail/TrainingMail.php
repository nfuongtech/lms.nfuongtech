<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrainingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $markdownHtml // đã render từ Blade::render()
    ) {}

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->html($this->markdownHtml);
    }
}
