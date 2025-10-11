<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        // You can add 'mail', 'broadcast', etc.
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $tour = $this->order->orderTours->first()->tour ?? null;

        return [
            'title' => '🛒 New Order Received',
            'message' => 'A new order has been placed for ' . ($tour->title ?? 'a tour'),
            'order_id' => $this->order->id,
            'tour_id' => $tour->id ?? null,
            'customer_name' => $this->order->customer->first_name ?? 'Unknown',
            'url' => route('admin.orders.edit', encrypt($this->order->id)), // 👈 link to open directly
            
        ];
    }
}
