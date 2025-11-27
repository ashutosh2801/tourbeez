<?php

namespace App\Imports;

use App\Models\Tour;
use App\Models\TourPricing;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class ToursImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Ensure required fields exist
        if (empty($data['sku']) || empty($data['price'])) {
            
            return;
        }

        $tour = Tour::where('unique_code', $data['sku'])->first();

        if ($tour) {

            $tour->price = $data['price'];
            $tour->save();

            $tourPrice = TourPricing::where('tour_id', $tour->id)->first();

            if ($tourPrice) {
                $tourPrice->price = $data['price'];
                $tourPrice->save();
            }
        }


    }
}
