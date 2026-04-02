<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $request = $this->request;

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $data = DB::table('orders')
            ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
            ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
            ->where('orders.order_status', '!=', 1)
            ->whereBetween('orders.created_at', [$startDate, $endDate])

            ->get();

        return $data->map(function ($c) {
            return [
                $c->order_number,
                $c->created_at,
                $c->tour_date,
                '-',
                $c->first_name,
                '-',
                $c->last_name,
                '-',
                '-',
                $c->email,
                $c->phone,
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                $c->instructions,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Booking Date',
            'Fulfilment Date',
            'How Heard',
            'First Name',
            'Middle Name',
            'Last Name',
            'Gender',
            'DOB',
            'Email',
            'Phone',
            'Mobile',
            'Fax',
            'Skype',
            'Address',
            'City',
            'Postcode',
            'State',
            'Country',
            'Language',
            'Company',
            'Marketing Consent',
            'Special Requirement',
        ];
    }
}
