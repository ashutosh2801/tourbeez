<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class City extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id', 'state_id', 'upload_id', 'name', 'status','latitude','longitude'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('City')
            ->setDescriptionForEvent(fn(string $eventName) => "City {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function tours() 
    {
        return $this->hasMany(TourLocation::class, 'city_id');
    }
}
