<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckAbandonedOrders extends Command
{
    protected $signature = 'orders:check-abandoned';
    protected $description = 'Check for abandoned orders and send reminder emails.';

    public function handle()
    {
        \Log::info('Abandoned order check started.');
        $this->info('Abandoned order check started.');

        $orders = Order::where('order_status', 1)
        ->where(function ($q) {
                $q->whereNull('is_abandon_mail_sent')
                  ->orWhere('is_abandon_mail_sent', 0);
            })
        ->where('created_at', '>=', now()->subHour()) // ✅ only last 1 hour
        ->get();
        
        
        if ($orders->isEmpty()) {
            $this->info('No abandoned orders found.');
            \Log::info('No abandoned orders found.');
            return;
        }

        foreach ($orders as $order) {
            try {
                $emailTemplate = EmailTemplate::where('identifier', 'order_abandon')->first();

                if (!$emailTemplate) {
                    \Log::warning('order_abandon email template not found.');
                    continue;
                }
                 $customer = $order->customer;
                // dd($customer );
                if(!$customer){
                    $customer = $order->orderUser;
                }

                if($customer && !$customer->email){
                    continue;
                }
                $pickup_address = '';
                if( $order->customer->pickup_name ) {
                    $pickup_address = $order->customer->pickup_name;
                }
                else if($order->customer->pickup_id) {
                    $pickup_address = $order->customer?->pickup?->location . ' ( '.$order->customer?->pickup?->address.' )';
                }

                    $token = encrypt($order->id);
                    $checkoutUrl = "https://tourbeez.com/checkout/{$order->session_id}?token={$token}";


                // ✅ Prepare placeholders
                $placeholders = [
                    '[[CUSTOMER_NAME]]' => $customer->name ?? '',
                    '[[CUSTOMER_EMAIL]]' => $customer->email ?? '',
                    '[[CUSTOMER_PHONE]]' => $customer->phone ?? '',
                    '[[ORDER_NUMBER]]'  => $order->order_number ?? '',
                    '[[TOUR_TITLE]]'    => $order->tour->title ?? '',
                    '[[ORDER_TOTAL]]'  => number_format($order->total_amount ?? 0, 2),
                    '[[ORDER_DATE]]'    => $order->created_at->format('d M Y'),
                    '[[CHECKOUT_URL]]'      => $checkoutUrl,
                    "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
                    "[[YEAR]]"                  => date('Y'),
                    "[[ORDER_TOUR_DATE]]"       => $order->order_tour->tour_date ? date('l, F j, Y', strtotime($order->order_tour->tour_date)) : '',
                    "[[ORDER_TOUR_TIME]]"       => $order->order_tour->tour_time ? date('H:i A', strtotime($order->order_tour->tour_time)) : '',
                    "[[TOUR_MAP]]"              => $pickup_address,
                ];

                $subject = strtr($emailTemplate->subject ?? 'Reminder: Your Booking is Pending', $placeholders);
                $body = strtr($emailTemplate->body ?? '', $placeholders);

                // ✅ Send email
                // Mail::to('vikas1rnv@gmail.com')->send(
                //     new CommonMail($subject, $body, null, null, null, true)
                // );

                Mail::to($customer->email)->send(
                    new CommonMail($subject, $body, null, null, null, true)
                );

                $order->is_abandon_mail_sent = 1;
                $order->save();

                // ✅ Log email history (if you use OrderEmailHistory)
                // $order->emailHistory()->create([
                //     'subject' => $subject,
                //     'body' => $body,
                //     'recipient' => $customer->email,
                //     'email_type' => 'order_abandon',
                // ]);
                

                \Log::info("Abandoned order email sent for order {$order->order_number}");
                $this->info("Email sent for order {$order->order_number}");

            } catch (\Exception $e) {
                \Log::error("Error sending abandoned order email for order {$order->order_number}: " . $e->getMessage());
            }
        }

        \Log::info('Abandoned order check completed.');
        $this->info('Abandoned order check completed.');
    }
}
