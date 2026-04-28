<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class InvoiceExport implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
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
                'Payment Status',
                'Product Name'
            ]
        ], $this->data);
    }
}
