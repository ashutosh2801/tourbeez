<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderTour;
use App\Models\OrderCustomer;
use App\Models\Tour;
use Illuminate\Support\Facades\DB;

class OrderImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {

            $tour = Tour::where('title', trim($row['product_name']))->first();

            if (!$tour) {
                return null; // skip invalid rows
            }

            // -------- ORDER --------
            $order = Order::create([
                'tour_id'          => $tour->id,
                'user_id'          => auth()->id(),
                'order_number'     => $row['order_number'],
                'redzy_order_id'   => $row['redzy_order_id'] ?? null,
                'number_of_guests' => (int)$row['quantities'],
                'payment_status'   => 1,
                'payment_method'   => 'import',
                'currency'         => 'USD',
                'total_amount'     => $row['order_total_amount'],
                'balance_amount'   => $row['order_balance'],
                'booked_amount'    => $row['order_total_paid'],
                'order_status'     => 1,
                'additional_info'  => $row['order_special_requirements'],
                'internal_notes'   => trim(
                    ($row['order_internal_notes'] ?? '') .
                    ' | Agent: ' . ($row['agent_code'] ?? '') .
                    ' | Notes: ' . ($row['agent_notes'] ?? '')
                ),
                'created_at'       => Carbon::parse($row['date']),
            ]);

            // -------- ORDER TOUR --------
            OrderTour::create([
                'tour_id'          => $tour->id,
                'order_id'         => $order->id,
                'tour_date'        => $row['check-in'],
                'tour_time'        => $row['pick-up_time'],
                'tour_extra'       => $row['extras'],
                'number_of_guests' => $row['quantities'],
                'total_amount'     => $row['order_total_amount'],
                'tour_pricing'     => json_encode([
                    'base_price' => $tour->price,
                    'guests'     => $row['quantities']
                ])
            ]);

            // -------- CUSTOMER --------
            [$first, $last] = array_pad(
                explode(' ', $row['customer_full_name'], 2),
                2,
                ''
            );

            OrderCustomer::create([
                'order_id'    => $order->id,
                'first_name'  => $first,
                'last_name'   => $last,
                'email'       => 'order_'.$row['order_number'].'@import.local',
                'phone'       => $row['customer_phone'],
                'instructions'=> $row['order_special_requirements'],
                'pickup_name' => $row['pick-up_location'],
                'pickup_id'   => $row['pickup_address'],
            ]);

            return $order;
        });
    }
}
