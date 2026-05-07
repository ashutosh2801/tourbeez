<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Feature extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id', 'name', 'type'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Feature')
            ->setDescriptionForEvent(fn(string $eventName) => "Feature {$eventName}")
            ->logAll(); // 🔥 important
    }
}
