<?php

namespace App\Models;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TourSchedule extends Model
{
    use HasFactory;
    use LogsActivity;

    public function repeats() {
        return $this->hasMany(TourScheduleRepeats::class, 'tour_schedule_id');
    }

    public function tour() {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourImage')
            ->setDescriptionForEvent(fn(string $eventName) => "TourImage {$eventName}")
            ->logAll(); // 🔥 important
    }

}
