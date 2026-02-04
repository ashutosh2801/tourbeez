<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourScheduleMeta extends Model
{
    protected $table = 'tour_schedule_meta';

    protected $casts = [
        'disabled_dates' => 'array',
    ];
}