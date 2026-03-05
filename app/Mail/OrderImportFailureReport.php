<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderImportFailureReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public array $failedRows)
    {
        //
    }

    /**
     * Get the message envelope.
     */
   public function build()
    {
        return $this->subject('Order Import Failure Report')
            ->view('emails.order-import-failure')
            ->with([
                'failedRows' => $this->failedRows
            ]);
    }
}
