<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerTour extends Model
{
    protected $fillable = ['tour_id', 'partner_id', 'title', 'link'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}