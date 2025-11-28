<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'payment_intent_id',
        'transaction_id',
        'payment_method',
        'amount',
        'currency',
        'status',
        'action',
        'reason',
        'response_payload',
        'card_last4',
        'card_brand',
        'card_exp_month',
        'card_exp_year',
        'refund_id',
        'refund_amount',
        'refund_reason',
        'refunded_at',
    ];
}
