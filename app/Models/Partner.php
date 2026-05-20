<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Partner extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = ['name', 'slug', 'upload_id', 'logo_url'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Partner')
            ->setDescriptionForEvent(fn(string $eventName) => "Partner {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function tours()
    {
        return $this->hasMany(PartnerTour::class);
    }

    public function tour()
    {
        return $this->hasOne(PartnerTour::class);
    }
}