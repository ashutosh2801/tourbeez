<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OrderPayment extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'order_id',
        'payment_intent_id',
        'transaction_id',
        'payment_method',
        'payment_type',
        'collection_type',
        'collection_date',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('OrderPayment')
        ->setDescriptionForEvent(fn(string $eventName) => "OrderPayment has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    public function order() { return $this->belongsTo(Order::class); }

}
