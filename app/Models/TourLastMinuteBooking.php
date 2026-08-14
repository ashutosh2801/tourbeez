<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourLastMinuteBooking extends Model
{
    use HasFactory;
    use SoftDeletes;

     protected $fillable = [
        'id',
        'tour_id',
        'from_date',
        'to_date',
        'last_minute_hours',
        'amount_type',
        'amount'
    ];
}
