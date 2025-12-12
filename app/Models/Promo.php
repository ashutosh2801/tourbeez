<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'code',
        'status',
        'quantity_rule',
        'issue_date',
        'expiry_date',
        'travel_from_date',
        'travel_to_date',
        'redemption_limit',
        'max_uses',
        'min_amount',
        'include_taxes_and_fees',
        'include_extras',
        'internal',
        'value_type',
        'voucher_value',
        'value_percent',
        'internal_notes',
        'valid_days',
        'product_id',
        'category_id',
        'used_count',
    ];

    protected $dates = [
        'issue_date',
        'expiry_date',
        'travel_from_date',
        'travel_to_date',
    ];

    protected $casts = [


        'valid_days' => 'array',
        'include_taxes_and_fees' => 'boolean',
        'include_extras' => 'boolean',
        'internal' => 'boolean',
    ];
}

