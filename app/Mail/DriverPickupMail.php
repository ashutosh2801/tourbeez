<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DriverPickupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $driver;
    public $orders;
    public $date;
    public $customMessage;

    public function __construct($driver, $orders, $date, $customMessage)
    {
        $this->driver = $driver;
        $this->orders = $orders;
        $this->date = $date;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        return $this
            ->subject(
                'Pickup List - ' .
                \Carbon\Carbon::parse($this->date)->format('M d, Y')
            )
            ->view('emails.driver-pickup-mail');
    }
}