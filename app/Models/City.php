<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id', 'state_id', 'upload_id', 'name', 'status'
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function tours() 
    {
        return $this->hasMany(TourLocation::class, 'city_id');
    }
}
