<?php

namespace App\Models;
use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TourLocation extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('TourLocation')
            ->setDescriptionForEvent(fn(string $eventName) => "TourLocation {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class)->withTrashed();
    }

    public function country()
    {
        return $this->belongsTo(Country::class)->withTrashed();
    }

    public function state()
    {
        return $this->belongsTo(State::class)->withTrashed();
    }

    public function city()
    {
        return $this->belongsTo(City::class)->withTrashed();
    }
}
