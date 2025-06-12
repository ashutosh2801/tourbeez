<?php
namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function sendSms($to, $message)
    {
        return $this->twilio->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
    }

    public function lookupNumber($number, $type = 'carrier')
    {
        return $this->twilio
            ->lookups
            ->v1
            ->phoneNumbers($number)
            ->fetch([
                'type' => $type // optional: "carrier" or "caller-name"
            ]);
    }
}
