<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_amount',
        'status',
        'start_date',
        'end_date',
        'max_uses',
        'used_count',
        'description',   // Added
        'is_global',     // Added
        'per_user_limit' // Added
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_global'  => 'boolean',
    ];
}
