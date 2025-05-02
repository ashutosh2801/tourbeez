<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourScheduleRepeats extends Model
{
    use HasFactory;

    protected $fillable = ['tour_schedule_id', 'day', 'start_time', 'end_time'];
}
