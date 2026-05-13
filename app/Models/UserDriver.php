<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDriver extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',

        'license_number',
        'license_expiry',
        'govt_id',

        'vehicle_type',
        'vehicle_number',
        'vehicle_model',
        'vehicle_capacity',

        'license_file',
        'rc_file',
        'insurance_file',

        'per_day_rate',
        'is_available',

        'city',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
