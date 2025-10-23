<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewContactNotification extends Notification
{
    use Queueable;

    protected $contact;

    public function __construct(array $contact)
    {
        $this->contact = $contact;
    }

    public function via($notifiable)
    {
        return ['database']; // you can also add 'mail' if you want
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Contact Message',
            'message' => "{$this->contact['name']} has sent a new message.",
            'email' => $this->contact['email'],
            'phone' => $this->contact['phone'],
            'text' => $this->contact['message'],
            'url' => route('admin.contacts.show', $this->contact['id']), // adjust if you have a contact page
        ];
    }
}
