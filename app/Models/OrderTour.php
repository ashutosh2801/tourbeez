<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderTour extends Model
{
    use HasFactory;
    use LogsActivity;

    public $timestamps = false;
    protected $fillable = [
        'tour_id', 'order_id ', 'tour_date', 'tour_time', 'tour_pricing', 'tour_extra', 'number_of_guests', 'total_amount'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('OrderTour')
        ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    public function tour_detail($id, $label='all') {
        $tour = Tour::where('id', $id)->first();
        if($label == 'all')
        return $tour;

        return $tour->$label;
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }
}