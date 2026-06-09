<?php

namespace App\Models;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TourPricing extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = ['tour_id', 'label', 'price', 'quantity_used', 'selling_price'];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourPricing')
            ->setDescriptionForEvent(fn(string $eventName) => "TourPricing {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class)->withTrashed();
    }
}
