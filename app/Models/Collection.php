<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Collection extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'collections';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'pdf',
        'category_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Collection')
            ->setDescriptionForEvent(fn(string $eventName) => "Collection {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
