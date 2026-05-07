<?php

namespace App\Models;

use App\Models\Scopes\SupplierScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tourtype extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'name', 'slug'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Tourtype')
            ->setDescriptionForEvent(fn(string $eventName) => "Tourtype {$eventName}")
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
}
