<?php

namespace App\Models;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourSpecialDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'use_deposit',
        'charge',
        'deposit_amount',
        'allow_full_payment',
        'use_minimum_notice',
        'notice_days',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }
}
