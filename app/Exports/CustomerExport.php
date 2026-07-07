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

       
        $hasFilter = $request->filled('booking_date')
            || $request->filled('tour_date')
            || $request->filled('product')
            || $request->filled('order_status')
            || $request->filled('payment_status')
            || $request->filled('partner')
            || $request->filled('action_type');

            /*
            |--------------------------------------------------------------------------
            | DEFAULT BOOKING DATE (LAST 7 DAYS)
            |--------------------------------------------------------------------------
            */
            if (!$hasFilter) {
                return collect();
            }

            /*
            |--------------------------------------------------------------------------
            | PARSE BOOKING DATE
            |--------------------------------------------------------------------------
            */
            $startDate = null;
            $endDate = null;

            if ($request->filled('booking_date')) {
                try {
                    [$start, $end] = explode(' - ', $request->booking_date);

                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();
                } catch (\Exception $e) {}
            }

        $query = DB::table('orders')
            ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
            ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
            ->whereNull('orders.deleted_at')
            ->whereNotIn('orders.order_status', $excludedStatuses)->groupBy('orders.id');




        if ($startDate && $endDate) {
            $query->whereBetween('orders.created_at', [$startDate, $endDate]);
        }

            // }
      if ($products = $request->input('product')) {

            $products = array_filter((array)$products);

            if (!empty($products)) {

                $query->whereIn('orders.id', function ($q) use ($products) {

                    $q->select('order_id')
                      ->from('order_tours')
                      ->whereNull('deleted_at')
                      ->whereIn('tour_id', $products);

                });

            }
        }

        if ($excludeProducts = $request->input('exclude_product')) {

            $excludeProducts = array_filter((array)$excludeProducts);

            if (!empty($excludeProducts)) {

                $query->whereNotIn('orders.id', function ($q) use ($excludeProducts) {

                    $q->select('order_id')
                      ->from('order_tours')
                      ->whereNull('deleted_at')
                      ->whereIn('tour_id', $excludeProducts);

                });

            }
        }

            
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
        $data = $query->orderByDesc('orders.id')->get();

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
