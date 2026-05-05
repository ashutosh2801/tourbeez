<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class InvoiceWithDetailsExport implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return array_merge(
            $this->headers(),
            $this->rows()
        );
    }

    private function headers()
    {
        return [
            [
                'No.', 'Order #', 'Customer', 'Order Date', 'Fulfilment',
                'Customer Total (CAD)', 'Paid', 'Product',

                'Boat Desc','Boat Price','Boat Tax','Boat Fee','Boat Total',
                'Helicopter Desc','Helicopter Price','Helicopter Tax','Helicopter Fee','Helicopter Total',
                'Journey Desc','Journey Price','Journey Tax','Journey Fee','Journey Total',
                'Sheraton Desc','Sheraton Price','Sheraton Tax','Sheraton Fee','Sheraton Total',
                'Skylon Desc','Skylon Price','Skylon Tax','Skylon Fee','Skylon Total',
                'Airport Desc','Airport Price','Airport Tax','Airport Fee','Airport Total',
                'Wine Desc','Wine Price','Wine Tax','Wine Fee','Wine Total',
                'Jet Desc','Jet Price','Jet Tax','Jet Fee','Jet Total',
                'Guide Desc','Guide Price','Guide Tax','Guide Fee','Guide Total',
                'Zipline Desc','Zipline Price','Zipline Tax','Zipline Fee','Zipline Total',
            ]
        ];
    }

    private function rows()
    {
        $rows = [];

        foreach ($this->data as $r) {

            $rows[] = [
                $r['no'],
                $r['order_number'],
                $r['customer_name'],
                $r['order_date'],
                $r['fulfilment_date'],
                $r['customer_total'],
                $r['payment_status'],
                $r['product_name'],

                // BOAT
                $r['boat_cruise_desc'], $r['boat_cruise_price'], $r['boat_cruise_tax'], $r['boat_cruise_fee'], $r['boat_cruise_total'],

                // HELI
                $r['helicopter_desc'], $r['helicopter_price'], $r['helicopter_tax'], $r['helicopter_fee'], $r['helicopter_total'],

                // JOURNEY
                $r['journey_falls_desc'], $r['journey_falls_price'], $r['journey_falls_tax'], $r['journey_falls_fee'], $r['journey_falls_total'],

                // SHERATON
                $r['sheraton_desc'], $r['sheraton_price'], $r['sheraton_tax'], $r['sheraton_fee'], $r['sheraton_total'],

                // SKYLON
                $r['skylon_desc'], $r['skylon_price'], $r['skylon_tax'], $r['skylon_fee'], $r['skylon_total'],

                // AIRPORT
                $r['airport_desc'], $r['airport_price'], $r['airport_tax'], $r['airport_fee'], $r['airport_total'],

                // WINE
                $r['wine_desc'], $r['wine_price'], $r['wine_tax'], $r['wine_fee'], $r['wine_total'],

                // JET
                $r['jet_desc'], $r['jet_price'], $r['jet_tax'], $r['jet_fee'], $r['jet_total'],

                // GUIDE
                $r['guide_desc'], $r['guide_price'], $r['guide_tax'], $r['guide_fee'], $r['guide_total'],

                // ZIPLINE
                $r['zipline_desc'], $r['zipline_price'], $r['zipline_tax'], $r['zipline_fee'], $r['zipline_total'],
            ];
        }

        return $rows;
    }
}