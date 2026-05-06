<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class InvoiceWithDetailsExport implements FromArray, WithEvents
{
    protected $data;
    protected $addonKeys;

    public function __construct($data)
    {
        $this->data = $data;

        $this->addonKeys = collect($data[0] ?? [])
            ->keys()
            ->filter(fn($k) => str_ends_with($k, '_desc'))
            ->map(fn($k) => str_replace('_desc', '', $k))
            ->values()
            ->toArray();
    }

    public function array(): array
    {
        $rows = [];

        // Row 2 (sub headers)
        $subHeader = [
            'No.', 'Order #', 'Customer', 'Order Date', 'Fulfilment',
            'Total', 'Paid', 'Product'
        ];

        foreach ($this->addonKeys as $key) {
            $subHeader = array_merge($subHeader, [
                'Desc', 'Price', 'Tax', 'Fee', 'Total'
            ]);
        }

        // Empty row 1 (we'll fill via event)
        $rows[] = array_fill(0, count($subHeader), '');
        $rows[] = $subHeader;

        // DATA
        foreach ($this->data as $r) {

            $row = [
                $r['no'] ?? '',
                $r['order_number'] ?? '',
                $r['customer_name'] ?? '',
                $r['order_date'] ?? '',
                $r['fulfilment_date'] ?? '',
                number_format((float) ($r['customer_total'] ?? 0), 2, '.', ''),
                $r['payment_status'] ?? '',
                $r['product_name'] ?? '',
            ];

            foreach ($this->addonKeys as $key) {
                $row[] = $r[$key.'_desc'] ?? '';
                $row[] = number_format((float) ($r[$key.'_price'] ?? 0), 2, '.', '');
                $row[] = number_format((float) ($r[$key.'_tax'] ?? 0), 2, '.', '');
                $row[] = number_format((float) ($r[$key.'_fee'] ?? 0), 2, '.', '');
                $row[] = number_format((float) ($r[$key.'_total'] ?? 0), 2, '.', '');
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet->getDelegate();

                $col = 1;

                // static columns (merge vertically)
                $staticHeaders = [
                    'No.', 'Order #', 'Customer', 'Order Date',
                    'Fulfilment', 'Total', 'Paid', 'Product'
                ];

                foreach ($staticHeaders as $header) {
                    $sheet->setCellValueByColumnAndRow($col, 1, $header);
                    $sheet->mergeCellsByColumnAndRow($col, 1, $col, 2);
                    $col++;
                }

                // addon headers (merge horizontally)
                foreach ($this->addonKeys as $key) {

                    $label = ucwords(str_replace('_', ' ', $key));

                    $sheet->setCellValueByColumnAndRow($col, 1, $label);

                    // merge across 5 columns ONLY
                    $sheet->mergeCellsByColumnAndRow($col, 1, $col + 4, 1);

                    $col += 5;
                }

                // styling
                $sheet->getStyle('1:2')->getFont()->setBold(true);
                $sheet->getStyle('1:2')->getAlignment()->setHorizontal('center');
            }
        ];
    }
}