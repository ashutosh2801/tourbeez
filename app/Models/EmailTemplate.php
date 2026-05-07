<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmailTemplate extends Model
{
   use SoftDeletes;
   use LogsActivity;


   public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('EmailTemplate')
            ->setDescriptionForEvent(fn(string $eventName) => "EmailTemplate {$eventName}")
            ->logAll(); // 🔥 important
    }
}
