<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericTemplateMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $bodyHtml;

    /**
     * GenericTemplateMailable constructor.
     *
     * @param string $subject
     * @param string $bodyHtml
     */
    public function __construct(string $subject, string $bodyHtml)
    {
        $this->subjectText = $subject;
        $this->bodyHtml = $bodyHtml;
        $this->subject($subject);
    }

    public function build()
    {
        return $this->view('emails.raw_template')
            ->with([
                'bodyHtml' => $this->bodyHtml,
            ]);
    }
}
