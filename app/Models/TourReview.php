<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourReview extends Model
{
    use HasFactory;
    
    protected $casts = [
        'recommended' => 'array',
        'badges' => 'array',
        'banners' => 'array',
    ];
}
