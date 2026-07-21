<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PassengerPickupMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $date;
    public ?string $pickupTime;
    public ?string $customMessage;
    public Collection $drivers;
    public ?Vehicle $vehicle;
    
    public $galleryUploadUrl;
    public $galleryQrUrl;

    public function __construct(
        $order,
        $date,
        $drivers,
        $vehicle,
        $pickupTime,
        $customMessage,
        $galleryUploadUrl,
        $galleryQrUrl
    ) {
        $this->order = $order;
        $this->date = $date;
        $this->drivers = $drivers;
        $this->vehicle = $vehicle;
        $this->pickupTime = $pickupTime;
        $this->customMessage = $customMessage;
        $this->galleryUploadUrl = $galleryUploadUrl;
        $this->galleryQrUrl = $galleryQrUrl;
    }

    public function build()
    {
        return $this
            ->subject(
                'Pickup Reminder - ' .
                Carbon::parse($this->date)->format('M d, Y')
            )
            ->view('emails.passenger-pickup-mail');
    }
}