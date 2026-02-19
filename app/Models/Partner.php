<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;

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