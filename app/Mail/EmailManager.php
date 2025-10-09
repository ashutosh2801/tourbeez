<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailManager extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $array;

    public function __construct($array)
    {
        $this->array = $array;
    }
    
    /**
     * Build the message.
     *
     * @return $this
     */
    // public function build()
    // {
    //     return $this->view($this->array['view'])
    //                 ->from($this->array['from'])
    //                 ->subject($this->array['subject']);
    // }

    public function build()
    { 
        $mail = $this->view($this->array['view'])
            ->from($this->array['from'], config('app.name'))
            ->subject($this->array['subject']);

        // If event details are provided, generate ICS on the fly
        if (!empty($this->array['event'])) {
            $icsContent = $this->generateICS($this->array['event']);

            $mail->attachData($icsContent, 'event.ics', [
                'mime' => 'text/calendar',
            ]);
        }

        $mail->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('X-Mailgun-Track', 'yes');
            $headers->addTextHeader('X-Mailgun-Track-Opens', 'yes');
            $headers->addTextHeader('X-Mailgun-Track-Clicks', 'yes');
        });

        return $mail;
    }
    

    protected function generateICS($event)
    {
        $start = date('Ymd\THis', strtotime($event['start']));
        $end   = date('Ymd\THis', strtotime($event['end']));
        $dtstamp = date('Ymd\THis');
        $organiser = config('app.name');

        // No indentation in ICS body
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
