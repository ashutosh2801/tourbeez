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
        $excludedStatuses = [1, 2, 6, 7];
        $request = $this->request;

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $query = DB::table('orders')
            ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
            ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
            ->whereNotIn('orders.order_status', $excludedStatuses)
            ->whereBetween('orders.created_at', [$startDate, $endDate])->groupBy('orders.id');

            
        if ($request->filled('payment_status')) {
            $query->where('orders.payment_status', $request->payment_status);
        }

        if ($request->action_type === 'pay_now') {
            $query->where('orders.action_name', 'book');
        } elseif ($request->action_type === 'pay_later') {
            $query->where(function ($q) {
                $q->where('orders.action_name', '!=', 'book')
                  ->orWhereNull('orders.action_name');
            });
        }
        if ($request->filled('partner')) {
            $query->where('orders.source', $request->partner);
        }

        if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
            $query->whereBetween('order_tours.tour_date', [
                $request->tour_start_date,
                $request->tour_end_date,
            ]);
        }
        $data = $query->get();

        return $data->map(function ($c) {
            return [
                $c->order_number,
                $c->created_at,
                $c->tour_date,
                
                $c->first_name,
                
                $c->last_name,
                
                $c->email,
                $c->phone,
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
            
            'First Name',
            
            'Last Name',
            
            'Email',
            'Phone',
            'Gender',
            'DOB',
            
            'Special Requirement',
        ];
    }
}
