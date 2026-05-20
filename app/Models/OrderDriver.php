<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDriver extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'driver_id',
        'assigned_date',
        'pickup_location',
        'drop_location',
        'driver_amount',
        'notes',
    ];
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');

    }
}
