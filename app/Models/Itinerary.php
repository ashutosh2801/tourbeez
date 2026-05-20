<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Itinerary extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'tour_id', 'created_by', 'title', 'description', 'address', 'datetime', 'latitude', 'longitude', 'status', 'order'
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Itinerary')
            ->setDescriptionForEvent(fn(string $eventName) => "Itinerary {$eventName}")
            ->logAll(); // 🔥 important
    }
}
