<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourImage extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'tour_id',
        'image',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourImage')
            ->setDescriptionForEvent(fn(string $eventName) => "TourImage {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class,'tour_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourImage')
            ->setDescriptionForEvent(fn(string $eventName) => "TourImage {$eventName}")
            ->logAll(); // 🔥 important
    }

}
