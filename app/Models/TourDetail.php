<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;


class TourDetail extends Model
{
    use LogsActivity;
    use HasFactory;

    public $timestamps  = false;

    protected $casts = [
        'videos' => 'array',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourDetail')
            ->setDescriptionForEvent(fn(string $eventName) => "Tour Detail {$eventName}")
            ->logAll(); // 🔥 important
    }

    


}
