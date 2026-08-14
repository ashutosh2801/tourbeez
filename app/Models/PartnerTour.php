<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PartnerTour extends Model
{
    use LogsActivity;
    protected $fillable = ['tour_id', 'partner_id', 'title', 'link'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('PartnerTour')
            ->setDescriptionForEvent(fn(string $eventName) => "PartnerTour {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}