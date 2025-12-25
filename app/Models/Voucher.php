<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'create_mode',
        'codes_list',
        'quantity',

        'issue_date',
        'expiry_date',

        'travel_from_date',
        'travel_to_date',

        'valid_redemption_days',

        'agent',
        'internal_reference',

        'min_amount',

        'include_taxes_fees',
        'include_extras',

        'value_type',
        'voucher_value',

        'reusable',
        'remaining_value',

        'product_id',
        'category_id',

        'internal_notes',
    ];

    protected $casts = [

        'valid_redemption_days' => 'array',

        'include_taxes_fees' => 'boolean',
        'include_extras' => 'boolean',
        'reusable' => 'boolean',
    ];
}
