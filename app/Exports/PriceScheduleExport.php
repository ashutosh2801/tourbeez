<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PriceScheduleExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows)->map(function ($row) {
            return [
                'Tour' => $row['tour_name'],
                'Label' => $row['label'],

                'Revenue Price (CAD)' => number_format_with_currency($row['revenue_price']),
                'Revenue Tax (CAD)' => number_format_with_currency($row['revenue_tax']),
                'Revenue Total (CAD)' => number_format_with_currency($row['revenue_total']),

                'Cost Price (CAD)' => number_format_with_currency($row['cost_price']),
                'Cost Tax (CAD)' => number_format_with_currency($row['cost_tax']),
                'Cost Total (CAD)' => number_format_with_currency($row['cost_total']),

                'Profit (CAD)' => number_format_with_currency($row['profit']),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tour',
            'Label',

            'Revenue Price (CAD)',
            'Revenue Tax (CAD)',
            'Revenue Total (CAD)',

            'Cost Price (CAD)',
            'Cost Tax (CAD)',
            'Cost Total (CAD)',

            'Profit (CAD)',
        ];
    }
}