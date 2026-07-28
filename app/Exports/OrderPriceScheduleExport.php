<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Events\AfterSheet;

class OrderPriceScheduleExport implements FromArray, WithEvents, WithCustomChunkSize
{
    protected $rows;
    protected $addonKeys = [];

    public function __construct($rows, $totals)
    {
        // Treat rows as an array safely
        $this->rows = is_array($rows) ? $rows : $rows->toArray();
        $this->totals = is_array($totals) ? $totals : $totals->toArray();



        // 🔥 detect addons dynamically
       if (!empty($this->rows)) {

            // Step 1: get all possible addon keys from all rows
            $allAddonKeys = collect($this->rows)
                ->flatMap(fn($row) => array_keys($row))
                ->filter(fn($k) => str_ends_with($k, '_desc'))
                ->map(fn($k) => str_replace('_desc', '', $k))
                ->unique()
                ->values();

            // Step 2: keep only addons that have at least one non-empty value
            $this->addonKeys = $allAddonKeys
                ->filter(function ($key) {

                    return collect($this->rows)->contains(function ($row) use ($key) {

                        $desc  = trim((string)($row[$key.'_desc']  ?? ''));
                        $quant = (float)($row[$key.'_quant'] ?? 0);
                        $price = (float)($row[$key.'_price'] ?? 0);
                        $tax   = (float)($row[$key.'_tax'] ?? 0);
                        $fee   = (float)($row[$key.'_fee'] ?? 0);
                        $total = (float)($row[$key.'_total'] ?? 0);

                        return $desc !== ''
                            || $quant != 0
                            || $price != 0
                            || $tax != 0
                            || $fee != 0
                            || $total != 0;
                    });
                })
                ->values()
                ->toArray();
        }
    }

    /**
     * Force a massive chunk size so Laravel Excel processes 
     * all 3k+ records in a single internal batch without resetting the sheet pointer.
     */
    public function chunkSize(): int
    {
        return 10000; 
    }

    public function array(): array
    {
        ini_set('memory_limit', '2048M');

        logger('Rows received: ' . count($this->rows));
        $data = [];

        // 🔥 ROW 1: Give cell A1 a solid string value so the package reads it as a valid row.
        $totalColumns = 23 + (count($this->addonKeys) * 6);
        $row1 = array_fill(0, $totalColumns, '');
        $row1[0] = 'No.'; // Overwritten cleanly in AfterSheet later anyway
        $data[] = $row1;

        // 🔥 ROW 2 (sub headers)
        $headers = [
            'No.', 'Order #', 'Customer', 'Order Date', 'Fulfilment',
            'Quantity', 'Adult', 'Child', 'Infant', 'Senior',
            'Product Price', 'Extra Amount', 'Tax Amount', 'Discount',
            'Customer Total', 'Order Balance',
            'Supplier Price',
            'Extra Included', 'Extra Excluded', 'Supplier Tax',
            'Supplier Total', 'Profit', 'Product'
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

                (($r['adult'] ?? 0) + ($r['child'] ?? 0) + ($r['infant'] ?? 0) + ($r['other'] ?? 0) + ($r['senior'] ?? 0)),
                $r['adult'] ?? 0,
                $r['child'] ?? 0,
                $r['infant'] ?? 0,
                $r['senior'] ?? 0,

                number_format($r['product_price'] ?? 0, 2, '.', ''),
                number_format($r['extra_amount'] ?? 0, 2, '.', ''),
                number_format($r['tax_amount'] ?? 0, 2, '.', ''),
                number_format($r['discount_amount'] ?? 0, 2, '.', ''),
                number_format($r['customer_total'] ?? 0, 2, '.', ''),
                number_format($r['balance_amount'] ?? 0, 2, '.', ''),

                number_format($r['tour_selling_price'] ?? 0,2,'.',''),

                number_format($r['tour_extra_included_price'] ?? 0,2,'.',''),

                number_format($r['tour_extra_excluded_price'] ?? 0,2,'.',''),

                number_format($r['tour_selling_tax'] ?? 0,2,'.',''),

                number_format($r['tour_selling_total'] ?? 0,2,'.',''),
                number_format(($r['customer_total'] - $r['balance_amount'] - $r['tour_selling_total']) ?? 0,2,'.',''),

                $r['product_name'] ?? '',
            ];

            // 🔥 ADDONS
            foreach ($this->addonKeys as $key) {
                $row[] = $r[$key.'_desc'] ?? '';
                $row[] = $r[$key.'_quant'] ?? 0;
                $row[] = number_format($r[$key.'_price'] ?? 0, 2, '.', '');
                $row[] = number_format($r[$key.'_tax'] ?? 0, 2, '.', '');
                $row[] = number_format($r[$key.'_fee'] ?? 0, 2, '.', '');
                $row[] = number_format($r[$key.'_total'] ?? 0, 2, '.', '');
            }

            $data[] = $row;
        }
        
        $grandTotalRow = [
                            '',
                            '',
                            '',
                            'Grand Total',
                            '-',
                            '-',
                            '-',
                            '-',
                            '-',
                            '-',

                            number_format($this->totals['product_price'] ?? 0, 2, '.', ''),
                            number_format($this->totals['extra_amount'] ?? 0, 2, '.', ''),
                            number_format($this->totals['tax_amount'] ?? 0, 2, '.', ''),
                            number_format($this->totals['discount_amount'] ?? 0, 2, '.', ''),
                            number_format($this->totals['customer_total'] ?? 0, 2, '.', ''),
                            number_format($this->totals['balance_amount'] ?? 0, 2, '.', ''),

                            number_format($this->totals['tour_selling_price'] ?? 0, 2, '.', ''),
                            number_format($this->totals['tour_extra_included_price'] ?? 0, 2, '.', ''),
                            number_format($this->totals['tour_extra_excluded_price'] ?? 0, 2, '.', ''),
                            number_format($this->totals['tour_selling_tax'] ?? 0, 2, '.', ''),
                            number_format($this->totals['net_total'] ?? 0, 2, '.', ''),
                            number_format(($this->totals['profit'] ?? 0) - ($this->totals['balance_amount'] ?? 0), 2, '.', ''),

                            '',
                        ];

                        // Add empty cells for every visible addon (6 columns each)
                        foreach ($this->addonKeys as $key) {
                            $grandTotalRow = array_merge($grandTotalRow, array_fill(0, 6, ''));
                        }

                        $data[] = $grandTotalRow;
        
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
                    'Supplier Price',
                    'Extra Included', 'Extra Excluded', 'Supplier Tax',
                    'Supplier Total', 'Profit', 'Product'
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