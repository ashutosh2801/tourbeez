<?php

namespace App\Models;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourSpecialDeposit extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'tour_id',
        'use_deposit',
        'charge',
        'deposit_amount',
        'allow_full_payment',
        'use_minimum_notice',
        'notice_days',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourSpecialDeposit')
            ->setDescriptionForEvent(fn(string $eventName) => "TourSpecialDeposit {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }
}
