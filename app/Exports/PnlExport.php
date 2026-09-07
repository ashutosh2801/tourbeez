<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PnlExport implements FromArray, WithHeadings
{
    public function __construct(private array $rows) {}

    public function headings(): array
    {
        return [
            'Travel Date', 'Revenue Without HST', 'Commission-Stripe Revenue', 'Direct Cost Without HST',
            'Transport Cost For Reference', 'Total Direct Cost', 'Gross Profit/Loss',
            'Ad Expenses', 'Net Profit/Loss',
        ];
    }

    public function array(): array
    {
        $data = array_map(fn (array $row) => array_values($row), $this->rows);
        if (!$this->rows) {
            return $data;
        }

        $totals = [];
        foreach (array_keys($this->rows[0]) as $key) {
            $totals[$key] = is_numeric($this->rows[0][$key])
                ? array_sum(array_column($this->rows, $key))
                : ($key === 'travel_date' ? 'Grand Total' : '');
        }

        return array_merge($data, [array_values($totals)]);
    }
}
