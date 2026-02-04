<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommonMail;

class CustomResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        // Reset password URL
        $url = $this->resetUrl($notifiable);

        // Load your dynamic template (already stored in DB)
        $template = fetch_email_template('password_reset_email');

        // Prepare placeholders
        $placeholders = [
            'name' => $notifiable->name ?? '',
            'email' => $notifiable->email,
            'reset_link' => $url,
            'app_name' => config('app.name'),
            'year' => date('Y'),
        ];

        // Parse body & subject using your helper
        $parsedBody = parseTemplate($template->body, $placeholders);
        $parsedSubject = parseTemplate($template->subject, $placeholders);

        // Send using your existing mail flow

        return (new CommonMail($parsedSubject, $parsedBody, null, null, null, true))
        ->to($notifiable->email);

    //     Mail::to($notifiable->email)
    // ->send(new CommonMail($parsedSubject, $parsedBody, null, null, null, true));
    }
}
