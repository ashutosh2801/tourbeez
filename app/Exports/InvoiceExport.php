<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class InvoiceExport implements FromArray
{
    protected $data;

     public function __construct($data)
     {
        // Transform data into required format
        $this->data = collect($data)->map(function ($item) {
            return [
                $item['no'],
                $item['order_number'],
                $item['customer_name'],
                $item['order_date'],
                $item['fulfilment_date'],
                number_format_with_currency($item['product_price'], 2, '.', ''),
                number_format_with_currency($item['extra_amount'], 2, '.', ''),
                number_format_with_currency($item['tax_amount'], 2, '.', ''),
                number_format_with_currency($item['booking_fee'], 2, '.', ''),
                number_format_with_currency($item['customer_total'], 2, '.', ''),
                number_format_with_currency($item['total_paid'], 2, '.', ''),
                $item['product_name'],
                $item['adult'],
                $item['child'],
                $item['infant'],
                $item['other'],
            ];
        })->toArray();
    }

    public function array(): array
    {
        return array_merge([
            [
                'No',
                'Order Number',
                'Customer Name',
                'Order Date',
                'Fulfilment Date',
                'Product Price (CAD)',
                'Extra Amount (CAD)',
                'Tax (CAD)',
                'Booking Fee (CAD)',
                'Customer Total (CAD)',
                'Paid',
                'Product Name',
                'Adults',
                'Childs',
                'Infants',
                'Senior Citizen',
            ]
        ], $this->data);
    }
}
