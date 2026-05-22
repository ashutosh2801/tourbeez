<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ScheduleDeleteSlot extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'tour_id','slot_date','slot_start_time','slot_end_time', 'delete_type'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ScheduleDeleteSlot')
            ->setDescriptionForEvent(fn(string $eventName) => "ScheduleDeleteSlot {$eventName}")
            ->logAll(); // 🔥 important
    }

    

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TourSchedule::class);
    }
}

