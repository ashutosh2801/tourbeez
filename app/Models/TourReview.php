<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourReview extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $casts = [
        'recommended' => 'array',
        'badges' => 'array',
        'banners' => 'array',
        'tag' => 'array',
    ];
}
