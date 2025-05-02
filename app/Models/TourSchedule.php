<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourSchedule extends Model
{
    use HasFactory;

    public function repeats() {
        return $this->hasMany(TourScheduleRepeats::class);
    }

}
