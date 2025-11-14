<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ToursSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                '229',
                'werew',
                'https://tourbeez.com/tour/werew',
                'TB-202507629',
                'Niagara Falls Day Tours',
                'Baranavichy, Brest, Belarus',
                '300',
            ],
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Title', 'URL', 'SKU', 'Category', 'Location', 'Price'];
    }
}
