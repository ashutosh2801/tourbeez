<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'title',
        'code',
        'type',
        'value',
        'min_amount',
        'status',
        'start_date',
        'end_date',
        'max_uses',
        'used_count',
        'agent',
        'category_id',
        'last_text_input',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
