<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Country extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'id', 'name', 'status','iso2','iso3','numcode','phonecode'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Country')
            ->setDescriptionForEvent(fn(string $eventName) => "Country {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasManyThrough
    {
        return $this->hasManyThrough(City::class, State::class);
    }
}
