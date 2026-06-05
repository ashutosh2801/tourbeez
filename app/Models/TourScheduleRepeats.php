<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourScheduleRepeats extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = ['tour_schedule_id', 'day', 'start_time', 'end_time'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourScheduleRepeats')
            ->setDescriptionForEvent(fn(string $eventName) => "TourScheduleRepeats {$eventName}")
            ->logAll(); // 🔥 important
    }
}
