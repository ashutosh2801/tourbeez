<?php

namespace App\Models;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourSchedule extends Model
{
    use HasFactory;

    public function repeats() {
        return $this->hasMany(TourScheduleRepeats::class, 'tour_schedule_id');
    }

    public function tour() {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

}
