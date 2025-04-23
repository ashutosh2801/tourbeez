<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPricing extends Model
{
    use HasFactory;

    protected $fillable = ['tour_id', 'label', 'price', 'quantity_used'];
}
