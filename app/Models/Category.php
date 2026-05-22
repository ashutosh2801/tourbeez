<?php

namespace App\Models;

use App\Models\CategoryFaq;
use App\Models\Scopes\SupplierScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory;
    use LogsActivity;


    protected $fillable = [
        'name', 'slug', 'user_id','description', 'meta_description', 'canonical_url', 'meta_title', 'meta_keywords'
    ];

    protected $casts = [
        'meta_keywords' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Category')
            ->setDescriptionForEvent(fn(string $eventName) => "Category {$eventName}")
            ->logAll(); // 🔥 important
    }

    protected static function booted()
    {
        static::addGlobalScope(new SupplierScope('user_id'));
    }



    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class);
    }

    public function faqs()
    {
        return $this->hasMany(CategoryFaq::class);
    }
}
