<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;


    protected $fillable = [
        'location_id', 'heading', 'sub_heading', 'image', 'videos'
    ];

    protected $casts = [
        'videos' => 'array', // automatically cast JSON to array
    ];

    public function location()
    {
        // return $this->belongsTo(Location::class);
    }
}
