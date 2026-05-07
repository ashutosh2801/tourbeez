<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TourUpload extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'tour_upload';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('TourUpload')
        ->setDescriptionForEvent(fn(string $eventName) => "TourUpload has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    public function upload()
    {
        return $this->belongsTo(\App\Upload::class);
    }
}