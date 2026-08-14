<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookReceipt extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
