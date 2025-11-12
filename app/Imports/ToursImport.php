<?php

namespace App\Imports;

use App\Models\Tour;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ToursImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Ensure required fields exist
        if (empty($data['sku']) || empty($data['price'])) {
            
            return;
        }
        // dd($data['sku'], $data['price']);
        // Update price where unique_code matches
        Tour::where('unique_code', $data['sku'])
            ->update(['price' => $data['price']]);
    }
}
