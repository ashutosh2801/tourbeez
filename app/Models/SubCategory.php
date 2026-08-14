<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SubCategory extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'sub_categories';

    protected $fillable = [
        'name','slug','category_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('SubCategory')
            ->setDescriptionForEvent(fn(string $eventName) => "SubCategory {$eventName}")
            ->logAll(); // 🔥 important
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
