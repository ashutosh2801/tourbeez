<?php

namespace App\Models;

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

    public function tour() {
        return $this->belongsTo(Tour::class);
    }

    public function user() {
        return $this->belongsTo(user::class);
    }

    public function customer() {
        return $this->hasOne(OrderCustomer::class);
    }
}
