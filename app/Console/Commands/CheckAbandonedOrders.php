<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderEmailHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckAbandonedOrders extends Command
{
    protected $signature = 'orders:check-abandoned';
    protected $description = 'Check for abandoned orders and send reminder emails.';

    public function handle()
    {
        // \Log::info('Abandoned order check started.');
        // $this->info('Abandoned order check started.');

        $orders = Order::where('order_status', 1)
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->where('is_abandon_mail_sent', 0)
                       ->where('created_at', '<=', now()->subMinutes(20));
                })
                ->orWhere(function ($q2) {
                    $q2->where('is_abandon_mail_24_sent', 0)
                       ->where('created_at', '<=', now()->subHours(24));
                });
            })
            ->get();

        if ($orders->isEmpty()) {
            // $this->info('No abandoned orders found.');
            // \Log::info('No abandoned orders found.');
            return;
        }

        foreach ($orders as $order) {

            try {
                // ✅ Decide which reminder
                if (!$order->is_abandon_mail_sent && $order->created_at <= now()->subMinutes(20)) {
                    $type = '20_min';
                } elseif (!$order->is_abandon_mail_24_sent && $order->created_at <= now()->subHours(24)) {
                    $type = '24_hour';
                } else {
                    continue;
                }

                $emailTemplate = EmailTemplate::where('identifier', 'order_abandon')->first();

                if (!$emailTemplate) {
                    // \Log::warning('order_abandon email template not found.');
                    continue;
                }

                $customer = $order->customer ?: $order->orderUser;

                if (!$customer || !$customer->email) {
                    continue;
                }

                $pickup_address = '';
                if ($order->customer && $order->customer->pickup_name) {
                    $pickup_address = $order->customer->pickup_name;
                } elseif ($order->customer && $order->customer->pickup_id) {
                    $pickup_address = $order->customer?->pickup?->location . ' ( '.$order->customer?->pickup?->address.' )';
                }

                $token = encrypt($order->id);
                $checkoutUrl = "https://tourbeez.com/checkout/{$order->session_id}?token={$token}";

                $placeholders = [
                    '[[CUSTOMER_NAME]]' => $customer->name ?? '',
                    '[[CUSTOMER_EMAIL]]' => $customer->email ?? '',
                    '[[CUSTOMER_PHONE]]' => $customer->phone ?? '',
                    '[[ORDER_NUMBER]]'  => $order->order_number ?? '',
                    '[[TOUR_TITLE]]'    => $order->tour->title ?? '',
                    '[[ORDER_TOTAL]]'   => number_format($order->total_amount ?? 0, 2),
                    '[[ORDER_DATE]]'    => $order->created_at->format('d M Y'),
                    '[[CHECKOUT_URL]]' => $checkoutUrl,
                    "[[ORDER_CREATED_DATE]]" => date('M d, Y', strtotime($order->created_at)),
                    "[[YEAR]]" => date('Y'),
                    "[[ORDER_TOUR_DATE]]" => $order->order_tour->tour_date ? date('l, F j, Y', strtotime($order->order_tour->tour_date)) : '',
                    "[[ORDER_TOUR_TIME]]" => $order->order_tour->tour_time ? date('H:i A', strtotime($order->order_tour->tour_time)) : '',
                    "[[TOUR_MAP]]" => $pickup_address,
                    "[[ORDER_BALANCE]]"         => ($order->payment_status === 3) ? price_format_with_currency($order->balance_amount + $order->payments->where('status', 'uncaptured')->sum('amount'), $order->currency) : price_format_with_currency($order->balance_amount, $order->currency),
                    "[[ORDER_BALANCE_COLOR]]"   => (abs($order->payment_status === 3? $order->balance_amount + $order->payments->where('status', 'uncaptured')->sum('amount'): $order->balance_amount) < 0.01) ? '008000' : 'f64747',
                ];

                $subject = strtr($emailTemplate->subject ?? 'Reminder: Your Booking is Pending', $placeholders);
                $body = strtr($emailTemplate->body ?? '', $placeholders);

                Mail::to($customer->email)->send(
                    new CommonMail($subject, $body, null, null, null, true)
                );

                // ✅ Update flags
                if ($type === '20_min') {
                    $order->is_abandon_mail_sent = 1;
                }

                if ($type === '24_hour') {
                    $order->is_abandon_mail_24_sent = 1;
                }

                $order->save();

                OrderEmailHistory::create([
                    'order_id'  => $order->id,
                    'to_email'  => $customer->email,
                    'from_email'=> env('MAIL_FROM_ADDRESS'),
                    'subject'   => $subject,
                    'body'      => $body,
                    'message_id' => null
                ]);

                // \Log::info("Abandoned order email sent for order {$order->order_number} ({$type})");
                // $this->info("Email sent for order {$order->order_number} ({$type})");

            } catch (\Exception $e) {
                // \Log::error("Error for order {$order->order_number}: " . $e->getMessage());
            }
        }

        // \Log::info('Abandoned order check completed.');
        // $this->info('Abandoned order check completed.');
    }
}
