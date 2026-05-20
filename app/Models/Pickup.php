<?php

namespace App\Models;

use App\Models\Scopes\SupplierScope;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pickup extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'name', 'location', 'address', 'time', 'additional_information', 'user_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Pickup')
            ->setDescriptionForEvent(fn(string $eventName) => "Pickup {$eventName}")
            ->logAll(); // 🔥 important
    }
    protected static function booted()
    {
        static::addGlobalScope(new SupplierScope('user_id'));
    }


    public function locations()
    {
        return $this->hasMany(PickupLocation::class);
    }

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'pickup_tour', 'pickup_id', 'tour_id');
    }
    
}
