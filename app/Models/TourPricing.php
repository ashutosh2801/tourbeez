<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TourPricing extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['tour_id', 'label', 'price', 'quantity_used'];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourPricing')
            ->setDescriptionForEvent(fn(string $eventName) => "TourPricing {$eventName}")
            ->logAll(); // 🔥 important
    }
}
