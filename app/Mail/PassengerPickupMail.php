<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PassengerPickupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $date;

    public function __construct($order, $date)
    {
        $this->order = $order;
        $this->date = $date;
    }

    public function build()
    {
        return $this
            ->subject(
                'Pickup - ' .
                \Carbon\Carbon::parse($this->date)->format('M d, Y')
            )
            ->view('emails.driver-pickup-mail');
    }
}