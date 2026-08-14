<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasFactory;
    use LogsActivity;
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Contact')
            ->setDescriptionForEvent(fn(string $eventName) => "Contact {$eventName}")
            ->logAll(); // 🔥 important
    }
}
