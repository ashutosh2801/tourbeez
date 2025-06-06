<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourUpload extends Model
{
    use HasFactory;

    protected $table = 'tour_upload';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('TourUpload')
        ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    public function upload()
    {
        return $this->belongsTo(\App\Upload::class);
    }
}