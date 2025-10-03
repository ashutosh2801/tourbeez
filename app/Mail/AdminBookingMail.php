<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    public function build()
    { 
        $mail = $this->view($this->array['view'])
            ->from($this->array['from'], config('app.name'))
            ->subject("New Order Received");

        if (!empty($this->array['event'])) {
            $icsContent = $this->generateICS($this->array['event']);

            $mail->attachData($icsContent, 'event.ics', [
                'mime' => 'text/calendar',
            ]);
        }

        return $mail;
    }

    protected function generateICS($event)
    {
        $start = date('Ymd\THis', strtotime($event['start']));
        $end   = date('Ymd\THis', strtotime($event['end']));
        $dtstamp = date('Ymd\THis');
        $organiser = config('app.name');

        return <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Your App//EN
BEGIN:VEVENT
UID:{$event['uid']}
DTSTAMP:{$dtstamp}
DTSTART:{$start}
SUMMARY:{$event['title']}
DESCRIPTION:{$event['description']}
LOCATION:{$event['location']}
ORGANIZER:{$organiser}
END:VEVENT
END:VCALENDAR
ICS;
    }
}
