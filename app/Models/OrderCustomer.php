<?php

namespace App\Models;


use App\Models\Order;
use App\Models\User;
use App\Models\PickupLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;


class OrderCustomer extends Model
{
    use HasFactory;
    use LogsActivity;

    
       protected $fillable = [
        'order_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'instructions',
        'pickup_id',
        'pickup_name',
        'promo_code',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('OrderCustomer')
        ->setDescriptionForEvent(fn(string $eventName) => "OrderCustomer has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    public function getNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function pickup()
    {
        return $this->belongsTo(PickupLocation::class, 'pickup_id');
    }

}
