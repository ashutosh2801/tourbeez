<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = ['name', 'slug', 'upload_id', 'logo_url'];

    public function tours()
    {
        return $this->hasMany(PartnerTour::class);
    }

    public function tour()
    {
        return $this->hasOne(PartnerTour::class);
    }
}