<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderDriver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class VehicleExport implements FromCollection, WithColumnWidths
{
    protected $date;
    protected $vehicleId;

    public function __construct($date, $vehicleId = null)
    {
        $this->date = $date;
        $this->vehicleId = $vehicleId;
    }


    public function collection()
    {
        $startOfWeek = Carbon::parse($this->date);
        $endOfWeek   = Carbon::parse($this->date)->copy()->addDays(6);

        $assignments = OrderDriver::with([
            'vehicle',
            'driver',
            'order.customer',
            'order.tour',
            'order.subTour',
            'order.orderTours.tour.detail'
        ])
        ->whereBetween('assigned_date', [
            $startOfWeek->toDateString(),
            $endOfWeek->toDateString()
        ])
        ->when($this->vehicleId, function ($q) {
            $q->where('vehicle_id', $this->vehicleId);
        })
        ->get();

        $grid = [];
        $vehicleTotals = [];
        $dateRange = [];

        $d = $startOfWeek->copy();

        while ($d->lte($endOfWeek)) {

            $day = $d->toDateString();

            $dateRange[] = $d->copy();

            $vehicleTotals[$day] = [];

            $d->addDay();
        }

        foreach ($assignments as $assignment) {

            $order = $assignment->order;

            if (!$order) {
                continue;
            }

            foreach ($order->orderTours as $tour) {

                if ($tour->tour_date != $assignment->assigned_date) {
                    continue;
                }

                $guestCount = collect(
                    json_decode($tour->tour_pricing, true) ?? []
                )->sum('quantity');

                $vehicleName = $assignment->vehicle?->name ?? 'NA';

                if ($order->sub_tour_id && $order->subTour) {

                    $tourTitle =
                        $order->tour?->title .
                        ' - ' .
                        $tour->tour->title;

                } else {

                    $tourTitle = $tour->tour->title ?? 'Unknown Tour';
                }

                $grid[$vehicleName][$assignment->assigned_date][] = [

                    'order_number'     => $order->order_number,
                    'customer'         => $order->customer?->name,
                    'guest_count'      => $guestCount,
                    'driver_name'      => $assignment->driver?->name ?? 'NA',
                    'vehicle_name'     => $vehicleName,
                    'pickup_time'      => $assignment->pickup_time,
                    'pickup_location'  => $assignment->pickup_location,
                    'assignment_type'  => $assignment->assignment_type,
                    'tour_title'       => $tourTitle,
                    'internal_notes'   => $order->internal_notes,
                ];

                $vehicleTotals[$assignment->assigned_date][$vehicleName] =
                    ($vehicleTotals[$assignment->assigned_date][$vehicleName] ?? 0)
                    + $guestCount;
            }
        }

        ksort($grid);

        /*
        |--------------------------------------------------------------------------
        | Excel Rows
        |--------------------------------------------------------------------------
        */

        $rows = [];

        $header = ['Vehicle'];

        foreach ($dateRange as $date) {
            $header[] = $date->format('j-M-Y');
        }

        $rows[] = $header;

        $dayRow = [''];

        foreach ($dateRange as $date) {
            $dayRow[] = $date->format('l');
        }

        $rows[] = $dayRow;

        foreach ($grid as $vehicle => $dates) {

            $row = [$vehicle];

            foreach ($dateRange as $date) {

                $day = $date->toDateString();

                $orders = $dates[$day] ?? [];

                if (empty($orders)) {
                    $row[] = '';
                    continue;
                }

                $text = '';

                $text .= 'Total - '.collect($orders)->sum('guest_count');

                foreach ($orders as $order) {

                    $text .= "\n";
                    $text .= $order['order_number'];
                    $text .= ' | ';
                    $text .= $order['guest_count'].' Pax';
                    $text .= ' | ';
                    $text .= $order['driver_name'] ?? 'NA';
                    $text .= ' | ';
                    $text .= $order['tour_title'];

                }

                $row[] = trim($text);
            }

            $rows[] = $row;
        }

        return collect($rows);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Tours column
            'B' => 25,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 25,
        ];
    }
}
