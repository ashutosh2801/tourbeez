<?php

namespace App\Imports;

use App\Models\Addon;
use App\Models\Tour;
use App\Models\TourPricing;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class ToursImport implements OnEachRow, WithHeadingRow
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Required fields
        if (empty($data['id']) && empty($data['sku'])) {
            return;
        }

        if ($this->type === 'tour_pricing') {
            $this->handleTourPricing($data);
        }

        if ($this->type === 'addon') {
            $this->handleAddon($data);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TOUR PRICING UPDATE
    |--------------------------------------------------------------------------
    */
    private function handleTourPricing($data)
    {
        if (empty($data['id'])) return;

        $pricing = TourPricing::find($data['id']);

        if ($pricing) {
            // $pricing->price = $data['price'] ?? $pricing->price;
            $pricing->selling_price = $data['selling_price'] ?? $pricing->selling_price;
            $pricing->save();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADDON UPDATE
    |--------------------------------------------------------------------------
    */
    private function handleAddon($data)
    {
        if (empty($data['id'])) return;

        $addon = Addon::find($data['id']);

        if ($addon) {
            // $addon->price = $data['price'] ?? $addon->price;
            $addon->selling_price = $data['selling_price'] ?? $addon->selling_price;
            $addon->save();
        }
    }
}
