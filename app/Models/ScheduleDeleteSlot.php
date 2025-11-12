<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleDeleteSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id','slot_date','slot_start_time','slot_end_time', 'delete_type'
    ];

    

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TourSchedule::class);
    }
}

