<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookLog extends Model
{
    protected $fillable = [
        'order_id',
        'event_id',
        'event_type',
        'payment_intent_id',
        'payload',
        'status',
        'message',
    ];
}
