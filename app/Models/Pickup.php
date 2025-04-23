<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pickup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'location', 'address', 'time', 'additional_information'
    ];

    public function locations()
    {
        return $this->hasMany(PickupLocation::class);
    }
    
}
