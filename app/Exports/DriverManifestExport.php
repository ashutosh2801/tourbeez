<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderDriver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class DriverManifestExport implements FromCollection, WithColumnWidths
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function collection()
    {
        $startOfWeek = Carbon::parse($this->date)->startOfWeek();
        $endOfWeek   = Carbon::parse($this->date)->endOfWeek();

        $orders = Order::with(['customer', 'orderTours.tour'])
            ->where('order_status', 5)
            ->whereHas('orderTours', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('tour_date', [$startOfWeek, $endOfWeek]);
            })
            ->get();

        $grid = [];
        $tourTimes = [];

        foreach ($orders as $order) {
            foreach ($order->orderTours as $ot) {

                $tourDate  = $ot->tour_date;
                $tourTitle = $ot->tour->title ?? 'Unknown Tour';
                $slotTime  = $ot->tour_time ?? '00:00 AM';

                if (!$tourDate) continue;

                // 👥 total pax
                $guestCount = 0;
                $pricingItems = json_decode($ot->tour_pricing, true);
                if (is_array($pricingItems)) {
                    foreach ($pricingItems as $p) {
                        $guestCount += $p['quantity'] ?? 0;
                    }
                }

                // 🚗 drivers
                $orderDrivers = OrderDriver::with('driver')
                    ->where('order_id', $order->id)
                    ->whereDate('assigned_date', $tourDate)
                    ->get();

                $driverPax = [];

                foreach ($orderDrivers as $od) {
                    $name = $od->driver?->name ?? 'Unknown';

                    if (!isset($driverPax[$name])) {
                        $driverPax[$name] = 0;
                    }

                    // ✅ FULL pax (no division)
                    $driverPax[$name] += $guestCount;
                }

                $grid[$tourTitle][$tourDate][] = [
                    'guest_count' => $guestCount,
                    'driver_pax'  => $driverPax,
                ];

                $tourTimes[$tourTitle] = $slotTime;
            }
        }

        // sort by time
        $sortedGrid = collect($grid)->sortBy(function ($dates, $tour) use ($tourTimes) {
            try {
                return Carbon::parse($tourTimes[$tour])->format('Hi');
            } catch (\Exception $e) {
                return 0;
            }
        });

        // date range
        $dateRange = [];
        $d = $startOfWeek->copy();
        while ($d->lte($endOfWeek)) {
            $dateRange[] = $d->copy();
            $d->addDay();
        }

        // 🧾 build rows
        $rows = [];

        // HEADER ROW
        $header = ['Tours'];
        foreach ($dateRange as $d) {
            $header[] = $d->format('j-M-Y');
        }
        $rows[] = $header;

        // DAY ROW
        $dayRow = [''];
        foreach ($dateRange as $d) {
            $dayRow[] = $d->format('l');
        }
        $rows[] = $dayRow;

        // DATA
        foreach ($sortedGrid as $tourTitle => $dates) {

            $row = [$tourTitle];

            foreach ($dateRange as $d) {

                $dateKey = $d->toDateString();
                $cellOrders = $dates[$dateKey] ?? [];

                $totalGuests = collect($cellOrders)->sum('guest_count');

                $driverSummary = [];

                foreach ($cellOrders as $o) {
                    foreach ($o['driver_pax'] as $driver => $pax) {
                        if (!isset($driverSummary[$driver])) {
                            $driverSummary[$driver] = 0;
                        }
                        $driverSummary[$driver] += $pax;
                    }
                }

                // 📦 build cell string
                $cellText = '';

                if ($totalGuests > 0) {
                    $cellText .= $totalGuests;

                    if (count($driverSummary)) {
                        $cellText .= "\n";
                        foreach ($driverSummary as $driver => $pax) {
                            $cellText .= $driver . ' - ' . $pax . "\n";
                        }
                    }
                }

                $row[] = trim($cellText);
            }

            $rows[] = $row;
        }

        return new Collection($rows);
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