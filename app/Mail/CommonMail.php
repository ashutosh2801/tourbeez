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


    /**
     * Create a new message instance.
     */
    public function __construct($subjectHtml, $bodyHtml, UploadedFile $attachment = null)
    {
        $this->subjectHtml = $subjectHtml;
        $this->bodyHtml = $bodyHtml;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subjectHtml)->html($this->bodyHtml);
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
