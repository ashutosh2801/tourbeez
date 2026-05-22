<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Exclusion extends Model
{
    use HasFactory;
    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Exclusion')
            ->setDescriptionForEvent(fn(string $eventName) => "Exclusion {$eventName}")
            ->logAll(); // 🔥 important
    }
}
