<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class OrderPriceScheduleExport implements FromArray, WithEvents
{
    protected $rows;
    protected $addonKeys = [];

    public function __construct($rows)
    {
        $this->rows = $rows;

        // 🔥 detect addons dynamically
        if (!empty($rows)) {
            $this->addonKeys = collect($rows[0])
                ->keys()
                ->filter(fn($k) => str_ends_with($k, '_desc'))
                ->map(fn($k) => str_replace('_desc', '', $k))
                ->values()
                ->toArray();
        }
    }

    public function array(): array
    {
        $data = [];

        // 🔥 ROW 1 (empty → merged later)
        $totalColumns = 23 + (count($this->addonKeys) * 6);
        $data[] = array_fill(0, $totalColumns, '');

        // 🔥 ROW 2 (sub headers)
        $headers = [
            'No.', 'Order #', 'Customer', 'Order Date', 'Fulfilment',
            'Quantity', 'Adult', 'Child', 'Infant', 'Senior',
            'Product Price', 'Extra Amount', 'Tax Amount', 'Discount',
            'Customer Total', 'Order Balance',
            'Transport Cost',
            'Product Price (Supplier Cost)', 'Tax', 'Other Fee',
            'Net Total', 'Profit', 'Product'
        ];

        foreach ($this->addonKeys as $key) {
            $headers = array_merge($headers, [
                'Desc', 'Quantity', 'Price', 'Tax', 'Fee', 'Total'
            ]);
        }

        $data[] = $headers;

        // 🔥 DATA ROWS
        foreach ($this->rows as $r) {

            $row = [
                $r['no'] ?? '',
                $r['order_number'] ?? '',
                $r['customer_name'] ?? '',
                $r['order_date'] ?? '',
                $r['fulfilment_date'] ?? '',

                ($r['adult'] + $r['child'] + $r['infant'] + $r['other'] + $r['senior']),
                $r['adult'] ?? 0,
                $r['child'] ?? 0,
                $r['infant'] ?? 0,
                $r['senior'] ?? 0,

                number_format_with_currency($r['product_price'] ?? 0, 2, '.', ''),
                number_format_with_currency($r['extra_amount'] ?? 0, 2, '.', ''),
                number_format_with_currency($r['tax_amount'] ?? 0, 2, '.', ''),
                number_format_with_currency($r['discount_amount'] ?? 0, 2, '.', ''),
                number_format_with_currency($r['customer_total'] ?? 0, 2, '.', ''),
                number_format_with_currency($r['balance_amount'] ?? 0, 2, '.', ''),

                number_format_with_currency($r['transport_cost'] ?? 0, 2, '.', ''),

                number_format_with_currency($r['tour_selling_price'] ?? 0, 2, '.', ''),
                number_format_with_currency($r['tour_selling_tax'] ?? 0, 2, '.', ''),
                0,
                number_format_with_currency(($r['tour_selling_total'] + $r['transport_cost']), 2, '.', ''),
                number_format_with_currency(($r['customer_total'] - $r['tour_selling_total'] - $r['transport_cost']), 2, '.', ''),

                $r['product_name'] ?? '',
            ];

            // 🔥 ADDONS
            foreach ($this->addonKeys as $key) {
                $row[] = $r[$key.'_desc'] ?? '';
                $row[] = $r[$key.'_quant'] ?? 0;
                $row[] = number_format_with_currency($r[$key.'_price'] ?? 0, 2, '.', '');
                $row[] = number_format_with_currency($r[$key.'_tax'] ?? 0, 2, '.', '');
                $row[] = number_format_with_currency($r[$key.'_fee'] ?? 0, 2, '.', '');
                $row[] = number_format_with_currency($r[$key.'_total'] ?? 0, 2, '.', '');
            }

            $data[] = $row;
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet->getDelegate();

                $col = 1;

                // 🔥 STATIC HEADERS (VERTICAL MERGE)
                $staticHeaders = [
                    'No.', 'Order #', 'Customer', 'Order Date', 'Fulfilment',
                    'Quantity', 'Adult', 'Child', 'Infant', 'Senior',
                    'Product Price', 'Extra Amount', 'Tax Amount', 'Discount',
                    'Customer Total', 'Order Balance',
                    'Transport Cost',
                    'Product Price (Supplier Cost)', 'Tax', 'Other Fee',
                    'Net Total', 'Profit', 'Product'
                ];

                foreach ($staticHeaders as $header) {
                    $sheet->setCellValueByColumnAndRow($col, 1, $header);
                    $sheet->mergeCellsByColumnAndRow($col, 1, $col, 2);
                    $col++;
                }

                // 🔥 ADDON GROUP HEADERS
                foreach ($this->addonKeys as $key) {

                    $label = ucwords(str_replace('_', ' ', $key));

                    $sheet->setCellValueByColumnAndRow($col, 1, $label);

                    // 🔥 6 columns per addon
                    $sheet->mergeCellsByColumnAndRow($col, 1, $col + 5, 1);

                    $col += 6;
                }

                // 🔥 STYLE
                $sheet->getStyle('1:2')->getFont()->setBold(true);
                $sheet->getStyle('1:2')->getAlignment()->setHorizontal('center');
            }
        ];
    }
}