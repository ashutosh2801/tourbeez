<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourLastMinuteBooking extends Model
{
    use HasFactory;

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
