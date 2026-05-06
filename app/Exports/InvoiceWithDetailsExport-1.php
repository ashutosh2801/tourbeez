<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class InvoiceWithDetailsExport1 implements FromArray
{
    protected $data;
    protected $addonKeys;

    public function __construct($data)
    {
        $this->data = $data;

        // extract dynamic addon keys from first row
        $this->addonKeys = collect($data[0] ?? [])
            ->keys()
            ->filter(fn($key) => str_ends_with($key, '_desc'))
            ->map(fn($key) => str_replace('_desc', '', $key))
            ->values()
            ->toArray();
    }

    public function array(): array
    {
        return array_merge(
            [$this->headers()],
            $this->rows()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HEADERS (DYNAMIC - SAME AS BLADE)
    |--------------------------------------------------------------------------
    */
    private function headers()
    {
        $headers = [
            'No.',
            'Order #',
            'Customer',
            'Order Date',
            'Fulfilment',
            'Customer Total (CAD)',
            'Paid',
            'Product',
        ];

        foreach ($this->addonKeys as $key) {
            $label = ucwords(str_replace('_', ' ', $key));

            $headers[] = $label . ' Desc';
            $headers[] = $label . ' Price';
            $headers[] = $label . ' Tax';
            $headers[] = $label . ' Fee';
            $headers[] = $label . ' Total';
        }

        return $headers;
    }

    /*
    |--------------------------------------------------------------------------
    | ROWS (DYNAMIC SAFE)
    |--------------------------------------------------------------------------
    */
    private function rows()
    {
        $rows = [];

        foreach ($this->data as $r) {

            $row = [
                $r['no'] ?? '',
                $r['order_number'] ?? '',
                $r['customer_name'] ?? '',
                $r['order_date'] ?? '',
                $r['fulfilment_date'] ?? '',
                $r['customer_total'] ?? 0,
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
}