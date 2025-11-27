<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Http\UploadedFile;

class CommonMail extends Mailable
{
    use Queueable, SerializesModels;
    public $subjectHtml;
    public $bodyHtml;

    public ?UploadedFile $attachment;
    public ?string $replyToEmail;
    public ?string $replyToName;
    public bool $disableTracking;


    /**
     * Create a new message instance.
     */
    public function __construct($subjectHtml, $bodyHtml, UploadedFile $attachment = null, $replyToEmail = null, $replyToName = null, bool $disableTracking = false
)
    {
        $this->subjectHtml = $subjectHtml;
        $this->bodyHtml = $bodyHtml;
        $this->attachment = $attachment;
        $this->replyToEmail = $replyToEmail;
        $this->replyToName = $replyToName;
        $this->disableTracking = $disableTracking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail =  $this->subject($this->subjectHtml)->html($this->bodyHtml);

        if ($this->replyToEmail) {
            $mail->replyTo($this->replyToEmail, $this->replyToName);
        }
        if ($this->disableTracking) {
            $mail->withSymfonyMessage(function ($message) {
                $headers = $message->getHeaders();
                $headers->addTextHeader('X-Mailgun-Track', 'no');
                $headers->addTextHeader('X-Mailgun-Track-Clicks', 'no');
                $headers->addTextHeader('X-Mailgun-Track-Opens', 'no');
            });
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
            if ($this->attachment) {
                return [
                    Attachment::fromPath($this->attachment->getRealPath())
                        ->as('attachment.' . $this->attachment->getClientOriginalExtension())
                        ->withMime($this->attachment->getMimeType())
                ];
            }

            return [];
        }
}
