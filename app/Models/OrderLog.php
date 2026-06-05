<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $fillable = [
        'order_id',
        'stage',
        'step',
        'status',
        'payment_status',
        'message',
        'context',
        'event_id',
        'payment_intent_id'
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
