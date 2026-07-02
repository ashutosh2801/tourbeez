<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderDriver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class DriverManifestExport22 implements FromCollection, WithColumnWidths
{
    protected $date;

    public function __construct($date, $driverId = null)
    {
        $this->date = $date;
        $this->driverId = $driverId;
    }


    public function collection()
    {
        // ✅ SAME LOGIC AS CONTROLLER (IMPORTANT)
        $startOfWeek = Carbon::parse($this->date);
        $endOfWeek   = Carbon::parse($this->date)->copy()->addDays(6);

        $orders = Order::with(['customer', 'orderTours.tour'])
            ->where('order_status', 5)
            ->whereHas('orderTours', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('tour_date', [
                    $startOfWeek->toDateString(),
                    $endOfWeek->toDateString()
                ]);
            })
            ->get();

        $grid = [];
        $tourTimes = [];

        // ✅ TOTAL ARRAYS
        $totalPaxPerDay = [];
        $assignedPaxPerDay = [];

        // ✅ DATE RANGE INIT
        $dateRange = [];
        $d = $startOfWeek->copy();

        while ($d->lte($endOfWeek)) {
            $key = $d->toDateString();

            $dateRange[] = $d->copy();
            $totalPaxPerDay[$key] = 0;
            $assignedPaxPerDay[$key] = 0;

            $d->addDay();
        }

        foreach ($orders as $order) {
            foreach ($order->orderTours as $ot) {

                $tourDate  = $ot->tour_date;
                $tourTitle = $ot->tour->title ?? 'Unknown Tour';
                $slotTime  = $ot->tour_time ?? '00:00 AM';

                if (!$tourDate) continue;

                // 👥 TOTAL PAX
                $guestCount = 0;
                $pricingItems = json_decode($ot->tour_pricing, true);
                if (is_array($pricingItems)) {
                    foreach ($pricingItems as $p) {
                        $guestCount += $p['quantity'] ?? 0;
                    }
                }

                // 🚗 DRIVERS
                $orderDrivers = OrderDriver::with('driver')
                    ->where('order_id', $order->id)
                    ->whereDate('assigned_date', $tourDate)
                    ->get();

                $driverIds = $orderDrivers->pluck('driver_id')->toArray();

                // ✅ APPLY FILTER
                if ($this->driverId) {

                    // ❌ skip orders not matching driver
                    if (!in_array($this->driverId, $driverIds)) {
                        continue;
                    }

                    // ✅ keep only selected driver
                    $orderDrivers = $orderDrivers->where('driver_id', $this->driverId);
                }

                $driverPax = [];
                $driverIds = $orderDrivers->pluck('driver_id')->toArray();

                foreach ($orderDrivers as $od) {
                    $name = $od->driver?->name ?? 'Unknown';

                    if (!isset($driverPax[$name])) {
                        $driverPax[$name] = 0;
                    }

                    $driverPax[$name] += $guestCount;
                }

                // ✅ TOTAL PAX PER DAY
                // TOTAL (after filter)
                // if (isset($totalPaxPerDay[$tourDate])) {
                //     $totalPaxPerDay[$tourDate] += $guestCount;
                // }

                // // ASSIGNED (only if driver exists after filter)
                // if ($orderDrivers->count() && isset($assignedPaxPerDay[$tourDate])) {
                //     $assignedPaxPerDay[$tourDate] += $guestCount;
                // }

                // ==========================
                // TOTAL PAX (ALWAYS ALL ORDERS)
                // ==========================
                $totalPaxPerDay[$tourDate] =
                    ($totalPaxPerDay[$tourDate] ?? 0) + $guestCount;


                // ==========================
                // ASSIGNED PAX (RESPECT FILTER)
                // ==========================
                if ($this->driverId) {

                    // only count if this driver exists in assignment
                    if (in_array($this->driverId, $driverIds)) {
                        $assignedPaxPerDay[$tourDate] =
                            ($assignedPaxPerDay[$tourDate] ?? 0) + $guestCount;
                    }

                } else {

                    // no filter → original behavior
                    if (!empty($driverIds)) {
                        $assignedPaxPerDay[$tourDate] =
                            ($assignedPaxPerDay[$tourDate] ?? 0) + $guestCount;
                    }
                }

                $grid[$tourTitle][$tourDate][] = [
                    'guest_count' => $guestCount,
                    'driver_pax'  => $driverPax,
                ];

                $tourTimes[$tourTitle] = $slotTime;
            }
        }

        // ✅ SORT BY TIME
        $sortedGrid = collect($grid)->sortBy(function ($dates, $tour) use ($tourTimes) {
            try {
                return Carbon::parse($tourTimes[$tour])->format('Hi');
            } catch (\Exception $e) {
                return 0;
            }
        });

        // 🧾 BUILD EXCEL ROWS
        $rows = [];

        // HEADER
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

        // DATA ROWS
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

                $cellText = '';

                if ($totalGuests > 0) {
                    $cellText .= 'Total - ' . $totalGuests;

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

        // ✅ TOTAL PAX ROW
        $totalRow = ['Total Pax'];
        foreach ($dateRange as $d) {
            $totalRow[] = $totalPaxPerDay[$d->toDateString()] ?? 0;
        }
        $rows[] = $totalRow;

        // ✅ ASSIGNED PAX ROW
        $assignedRow = ['Assigned Pax'];
        foreach ($dateRange as $d) {
            $assignedRow[] = $assignedPaxPerDay[$d->toDateString()] ?? 0;
        }
        $rows[] = $assignedRow;

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