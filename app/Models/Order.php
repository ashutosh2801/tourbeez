<?php

namespace App\Models;

use App\Models\OrderCustomer;
use App\Models\OrderMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('Orders')
        ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'tour_id', 'user_id', 'session_id', 'order_number', 'tour_date', 'tour_time', 'number_of_guests', 'payment_status', 
        'payment_method', 'total_amount', 'currency', 'order_status'
    ];

    public function tour_detail($id, $label='all') {
        $tour = Tour::where('id', $id)->first();
        if($label == 'all')
        return $tour;

        return $tour->$label;
    }

    public function orderTours()
    {
        return $this->hasMany(OrderTour::class);
    }

    public function orderMetas()
    {
        return $this->hasMany(OrderMeta::class);
    }

    public function order_tour()
    {
        return $this->hasOne(OrderTour::class);
    }

    public function tour() {
        return $this->belongsTo(Tour::class);
    }

    public function user() {
        return $this->belongsTo(user::class);
    }


    public function orderCustomer()
    {
        return $this->hasOne(OrderCustomer::class);
    }
    public function customer() {
        return $this->hasOne(OrderCustomer::class);
    }

    public function orderUser()
    {
        return $this->hasOneThrough(
            User::class,
            OrderCustomer::class,
            'order_id',   // Foreign key on order_customers table...
            'id',         // Foreign key on users table...
            'id',         // Local key on orders table...
            'user_id'     // Local key on order_customers table...
        );
    }


    public function getStatusAttribute()
    {
        return match ($this->order_status) {
            1 => 'New',
            2 => 'On Hold',
            3 => 'Pending supplier',
            4 => 'Pending customer',
            5 => 'Confirmed',
            6 => 'Cancelled',
            7 => 'Abandoned cart',
            default => 'Cancelled',
        };
    }

    public function setOrderStatusAttribute($value)
    {
        $map = [
            'New' => 1,
            'On Hold' => 2,
            'Pending supplier' => 3,
            'Pending customer' => 4,
            'Confirmed' => 5,
            'Cancelled' => 6,
            'Abandoned cart' => 7,
        ];

        // optional fallback if string doesn't match
        $this->attributes['order_status'] = $map[$value] ?? 6;
    }

    public function meta()
    {
        return $this->hasMany(OrderMeta::class);
    }

    public function bookingFee()
    {
        return $this->meta()->where('name', 'booking_fee');
    }

}
