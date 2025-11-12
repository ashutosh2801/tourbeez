<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $bodyHtml;
    public ?string $replyToEmail;
    public ?string $replyToName;

    /**
     * Create a new message instance.
     */
    public function __construct($subjectText, $bodyHtml, $replyToEmail = null, $replyToName = null)
    {
        $this->subjectText = $subjectText;
        $this->bodyHtml = $bodyHtml;
        $this->replyToEmail = $replyToEmail;
        $this->replyToName = $replyToName;
    }

    public function build()
    {
        $mail =  $this->subject($this->subjectText)
                    ->html($this->bodyHtml);
        if (!empty($this->replyToEmail)) {
            $mail->replyTo($this->replyToEmail, $this->replyToName ?? null);
        }
        return $mail;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
