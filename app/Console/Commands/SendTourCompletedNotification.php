<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderEmailHistory;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTourCompletedNotification extends Command
{
    protected $signature = 'orders:send-tour-completed-notification';
    protected $description = 'Send a completed trip email to the customers for taking feedback.';

    public function handle()
    {
        //tourbeez.com+9768a17f10@invite.trustpilot.com
        $orders = Order::with(['order_tour'])
                ->where('order_status', 5)
                ->where('order_number', '!=', 'TVCBVVL') // Exclude customer order
                ->whereHas('order_tour', function ($query) {
                    $query->where('tour_date', '<', date('Y-m-d'))
                    ->where('tour_date', '>=', date('Y-m-d', strtotime('-1 day')));
                })
                ->get();

        // ✅ No data found
        if ($orders->count() === 0) {
            echo 'No orders found for processing.';
            return;
        }

        // ✅ Email template
        $emailTemplate = EmailTemplate::where('identifier', 'trip_completed')->first();
        $emailSubject = $emailTemplate->subject;
        $emailBody = $emailTemplate->body;

        $i=0;
        foreach($orders as $order) {

            $pickup_address = '';
            if( $order->customer->pickup_name ) {
                $pickup_address = $order->customer->pickup_name;
            }
            else if($order->customer->pickup_id) {
                $pickup_address = $order->customer?->pickup?->location . ' ( '.$order->customer?->pickup?->address.' )';
            }

            // Replace placeholders
            $placeholders = [
                "[[CUSTOMER_NAME]]"         => $order->customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $order->customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => $order->customer->phone ?? '',

                "[[TOUR_TITLE]]"            => $order->tour->title ?? '',
                "[[TOUR_SKU]]"              => $order->tour->unique_code ?? '',
                "[[TOUR_MAP]]"              => $pickup_address ?? '',
                "[[TOUR_ADDRESS]]"          => $order->tour->location->address ?? '',
                "[[TOUR_TERMS_CONDITIONS]]" => $order->tour->terms_and_conditions ?? '',
                "[[PICKUP_ADDRESS]]"        => $pickup_address ?? '',

                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
                "[[ORDER_TOUR_DATE]]"       => $order->order_tour->tour_date ?? '',
                "[[ORDER_TOUR_TIME]]"       => $order->order_tour->tour_time ?? '',
                "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
                "[[ORDER_TOTAL]]"           => price_format_with_currency($order->total_amount, $order->currency) ?? 0,
                "[[ORDER_BALANCE]]"         => price_format_with_currency($order->balance_amount, $order->currency) ?? 0,

                "[[APP_NAME]]"              => get_setting('site_name'),
                "[[COMPANY_NAME]]"          => get_setting('site_name'),
                "[[APP_URL]]"               => get_setting('app_url'),
                "[[APP_EMAIL]]"             => get_setting('app_email'),
                "[[APP_PHONE]]"             => get_setting('app_phone'),
                "[[APP_ADDRESS]]"           => get_setting('app_address'),
                "[[YEAR]]"                  => date('Y'),

            ];
            $subject = strtr($emailSubject, $placeholders);
            $body = strtr($emailBody, $placeholders);

            // Recipients
            $recipients = [
                'email' => $order->customer->email,
                'name'  => $order->customer->name
            ];

            // echo '<pre>'; 
            // print_r([
            //     'subject' => $subject,
            //     'body' => $body,
            //     'recipients' => $recipients
            // ]); 
            // echo '</pre>';
            
            // Explicitly use Mailgun mailer
            $mailer = Mail::mailer('mailgun');

            // Send email and capture message inf
            $sentMessage = $mailer->to([$recipients]);
            $sentMessage->bcc(['tourbeez.com+9768a17f10@invite.trustpilot.com']);            
            $sentMessage = $sentMessage->send(new CommonMail($subject, $body, null, null, null, true));


            // die('Email sent');
            $messageId = null;
            if ($sentMessage instanceof \Illuminate\Mail\SentMessage) {
                $symfonySent = $sentMessage->getSymfonySentMessage();
                if ($symfonySent && method_exists($symfonySent, 'getMessageId')) {
                    $messageId = $symfonySent->getMessageId();
                    $messageId = trim($messageId, '<>');
                }
            }

            // Save to email history table
            OrderEmailHistory::create([
                'order_id'   => $order->id,
                'to_email'   => $order->customer->email ?? '',
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'subject'    => $subject,
                'body'       => $body,
                'status'     => $messageId ? 'sent' : 'failed',
                'message_id' => $messageId, // ✅ store for webhook tracking
            ]);

            if($i++%3==0) sleep(1); // Wait for 1 seconds to ensure email is sent before script ends
        }

        command::info('Tour completed notifications sent successfully.');
    }
}