<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Models\EmailTemplate;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTourExpiryNotification extends Command
{
    protected $signature = 'tour:send-tour-expiry-notification';
    protected $description = 'Send a single notification email to admin listing tours whose schedules expire within 5 days.';

    public function handle()
    {
        \Log::info('Checking for expiring tour schedules...');
        $this->info('Checking for expiring tour schedules...');

        $today = Carbon::now()->startOfDay();
        $fiveDaysLater = $today->clone()->addDays(5)->endOfDay();

        // ✅ Find all tours with schedules expiring in next 5 days
        $tours = Tour::whereHas('schedules', function ($q) use ($today, $fiveDaysLater) {
            $q->whereBetween('until_date', [$today->toDateString(), $fiveDaysLater->toDateString()]);
        })->with(['schedules' => function ($q) use ($fiveDaysLater) {
            $q->whereBetween('until_date', [now()->toDateString(), $fiveDaysLater->toDateString()]);
        }])->get();

        if ($tours->isEmpty()) {
            $this->info('No expiring tour schedules found.');
            \Log::info('No expiring tour schedules found.');
            return;
        }

        // ✅ Prepare HTML list for the email body
        $tableRows = '';
        foreach ($tours as $tour) {
            foreach ($tour->schedules as $schedule) {
                $daysLeft = now()->diffInDays(Carbon::parse($schedule->until_date), false);
                $id = encrypt($tour->id);

                $tableRows .= "
                    <tr>
                        <td>{$tour->title}</td>
                        <td>{$tour->unique_code}</td>
                        <td>{$schedule->until_date}</td>
                        <td>{$daysLeft} days</td>
                        <td><a href='https://tourbeez.com/admin/tour/{$id}/edit'>View Tour</a></td>
                    </tr>
                ";
            }
        }

        $tableHtml = "
            <table border='1' cellspacing='0' cellpadding='8' style='border-collapse: collapse; width:100%;'>
                <thead>
                    <tr style='background-color: #f2f2f2;'>
                        <th>Tour Title</th>
                        <th>Unique Code</th>
                        <th>Expiry Date</th>
                        <th>Days Left</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {$tableRows}
                </tbody>
            </table>
        ";

        // ✅ Get the email template
        $emailTemplate = EmailTemplate::where('identifier', 'schedule_expiry')->first();
        $subject = $emailTemplate->subject ?? 'Upcoming Tour Schedule Expiry Report';
        $body = $emailTemplate->body ?? '';

        // ✅ Replace placeholders
        $placeholders = [
            '[[EXPIRY_TABLE]]' => $tableHtml,
            '[[TOTAL_TOURS]]' => $tours->count(),
            '[[YEAR]]' => date('Y'),
        ];
        $body = strtr($body, $placeholders);

        // ✅ Recipients (admin emails)
        $recipients = [
            env('MAIL_FROM_ADMIN_ADDRESS'),
            env('MAIL_FROM_ADDRESS'),
        ];

        // ✅ Send single summary email
        Mail::to($recipients)->send(
            new CommonMail($subject, $body, null, null, null, true)
        );

        $this->info("Tour expiry summary email sent to admin with {$tours->count()} tours listed.");
        \Log::info("Tour expiry summary email sent to admin.");
    }
}
