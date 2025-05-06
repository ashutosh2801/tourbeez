<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourMeta extends Model
{
    use HasFactory;

    protected $fillable = ['tour_id', 'meta_key', 'meta_value'];
    public $timestamps = false;

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
