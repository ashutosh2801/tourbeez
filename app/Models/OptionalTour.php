<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OptionalTour extends Model
{
    use HasFactory;
    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('OptionalTour')
            ->setDescriptionForEvent(fn(string $eventName) => "OptionalTour {$eventName}")
            ->logAll(); // 🔥 important
    }
}
