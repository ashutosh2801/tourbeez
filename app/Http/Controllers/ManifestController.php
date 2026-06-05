<?php

namespace App\Http\Controllers;

use App\Exports\DriverManifestExport;
use App\Models\Order;
use App\Models\OrderDriver;
use App\Models\TourPricing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;


class ManifestController extends Controller
{
    //

    public function driverManifest342(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();



        // $startOfWeek = Carbon::parse($date)->startOfWeek();
        // $endOfWeek   = Carbon::parse($date)->endOfWeek();

        $startOfWeek = Carbon::parse($date);
        $endOfWeek   = Carbon::parse($date)->copy()->addDays(6);

        $pricingLabels = TourPricing::pluck('label', 'id')->toArray();

        $orders = Order::with(['customer', 'orderTours.tour', 'driver'])
            ->where('order_status', 5)
            ->whereHas('orderTours', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('tour_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
            })
            ->get();

        $grid = [];
        $tourTimes = [];
        $tourAssignableMap = [];
        $tourPaxMap = [];

        

        foreach ($orders as $order) {
            foreach ($order->orderTours as $ot) {
                $tourDate = $ot->tour_date;
                $slotTime = $ot->tour_time ?? '00:00 AM'; // fallback if missing
                $tourTitle = $ot->tour->title ?? 'Unknown Tour';

                if (!$tourDate) continue;

                // Count guests
                $guestCount = 0;
                $pricingItems = json_decode($ot->tour_pricing, true);
                if (is_array($pricingItems)) {
                    foreach ($pricingItems as $p) {
                        $guestCount += $p['quantity'] ?? 0;
                    }
                }

                // Get driver for that order/date
                // $orderDriver = \App\Models\OrderDriver::where('order_id', $order->id)
                //     ->whereDate('assigned_date', $ot->tour_date)
                //     ->first();

                $orderDrivers = OrderDriver::with('driver')
                    ->where('order_id', $order->id)
                    ->whereDate('assigned_date', $ot->tour_date)
                    ->get();

                $driverIds = $orderDrivers->pluck('driver_id')->toArray();
                $driverNames = $orderDrivers->pluck('driver.name')->filter()->toArray();

                $tourDetail = $ot->tour?->detail;

                $grid[$tourTitle][$tourDate][] = [
                    'order_id'     => $order->id,
                    'order_encrypt_id'  => encrypt($order->id),
                    'order_number' => $order->order_number,
                    'customer'     => $order->customer?->name,
                    'guest_count'  => $guestCount,
                    'driver_ids'   => $driverIds,
                    'driver_names' => $driverNames,
                    'tour_time'    => $slotTime,
                    'tour_assignable' => $tourDetail?->assign_driver ?? false,
                ];
                $dateKey = $ot->tour_date;
                $tourPaxMap[$tourTitle] = $tourPaxMap[$tourTitle] ?? 0;

                if ($dateKey === $date) {
                    $tourPaxMap[$tourTitle] = ($tourPaxMap[$tourTitle] ?? 0) + $guestCount;
                }

                // Store time for sort reference
                $tourTimes[$tourTitle] = $slotTime;
                $tourAssignableMap[$tourTitle] = $tourDetail?->assign_driver ?? false;
            }
        }

        // ✅ Convert times for sorting
        $sortedGrid = collect($grid)
            ->sortBy(function ($dates, $tour) use ($tourTimes, $tourAssignableMap, $tourPaxMap) {

                // ❌ NON-ASSIGNABLE → ALWAYS LAST
                $assignableSort = ($tourAssignableMap[$tour] ?? false) ? 0 : 1;

                // ✅ PAX priority (only inside assignable group)
                $hasPax = ($tourPaxMap[$tour] ?? 0) > 0 ? 0 : 1;

                // ✅ Time sorting
                try {
                    $timeSort = Carbon::parse($tourTimes[$tour] ?? '00:00 AM')->format('Hi');
                } catch (\Exception $e) {
                    $timeSort = 0;
                }

                // ✅ FINAL PRIORITY
                return $assignableSort . '_' . $hasPax . '_' . $timeSort;
            })
            ->toArray();

        // Generate week range
        $dateRange = [];

        $totalPaxPerDay = [];
        $assignedPaxPerDay = [];
        $d = $startOfWeek->copy();
        while ($d->lte($endOfWeek)) {
            $dateRange[] = $d->copy();
            // $totalPaxPerDay[$key] = 0;
            // $assignedPaxPerDay[$key] = 0;
            $d->addDay();
        }

        $drivers = User::where('role', 'Driver')->get();

        return view('admin.manifest.driver-1', compact(
            'sortedGrid',
            'dateRange',
            'tourTimes',
            'drivers',
            'date'
        ));
    }

    public function driverManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        $startOfWeek = Carbon::parse($date);
        $endOfWeek   = Carbon::parse($date)->copy()->addDays(6);

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
        $tourAssignableMap = [];
        $tourPaxMap = [];

        // ✅ TOTAL ARRAYS
        $totalPaxPerDay = [];
        $assignedPaxPerDay = [];

        // INIT DATE RANGE + TOTALS
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

                $tourDate = $ot->tour_date;
                $slotTime = $ot->tour_time ?? '00:00 AM';
                $tourTitle = $ot->tour->title ?? 'Unknown Tour';

                if (!$tourDate) continue;

                // ✅ GUEST COUNT
                $guestCount = 0;
                $pricingItems = json_decode($ot->tour_pricing, true);
                if (is_array($pricingItems)) {
                    foreach ($pricingItems as $p) {
                        $guestCount += $p['quantity'] ?? 0;
                    }
                }

                // ✅ DRIVERS
                $orderDrivers = OrderDriver::with('driver')
                    ->where('order_id', $order->id)
                    ->whereDate('assigned_date', $tourDate)
                    ->get();

                $driverIds = $orderDrivers->pluck('driver_id')->toArray();
                $driverNames = $orderDrivers->pluck('driver.name')->filter()->toArray();

                // ✅ TOTAL PAX
                if (isset($totalPaxPerDay[$tourDate])) {
                    $totalPaxPerDay[$tourDate] += $guestCount;
                }

                // ✅ ASSIGNED PAX (ONLY IF DRIVER EXISTS)
                if (!empty($driverIds) && isset($assignedPaxPerDay[$tourDate])) {
                    $assignedPaxPerDay[$tourDate] += $guestCount;
                }

                $tourDetail = $ot->tour?->detail;

                $grid[$tourTitle][$tourDate][] = [
                    'order_id'     => $order->id,
                    'order_encrypt_id' => encrypt($order->id),
                    'order_number' => $order->order_number,
                    'customer'     => $order->customer?->name,
                    'guest_count'  => $guestCount,
                    'driver_ids'   => $driverIds,
                    'driver_names' => $driverNames,
                    'tour_assignable' => $tourDetail?->assign_driver ?? false,
                ];

                // SORTING HELPERS
                if ($tourDate === $date) {
                    $tourPaxMap[$tourTitle] = ($tourPaxMap[$tourTitle] ?? 0) + $guestCount;
                }

                $tourTimes[$tourTitle] = $slotTime;
                $tourAssignableMap[$tourTitle] = $tourDetail?->assign_driver ?? false;
            }
        }

        // SORT
        $sortedGrid = collect($grid)
            ->sortBy(function ($dates, $tour) use ($tourTimes, $tourAssignableMap, $tourPaxMap) {

                $assignableSort = ($tourAssignableMap[$tour] ?? false) ? 0 : 1;
                $hasPax = ($tourPaxMap[$tour] ?? 0) > 0 ? 0 : 1;

                try {
                    $timeSort = Carbon::parse($tourTimes[$tour] ?? '00:00 AM')->format('Hi');
                } catch (\Exception $e) {
                    $timeSort = 0;
                }

                return $assignableSort . '_' . $hasPax . '_' . $timeSort;
            })
            ->toArray();

        $drivers = User::where('role', 'Driver')->get();

        return view('admin.manifest.driver', compact(
            'sortedGrid',
            'dateRange',
            'tourTimes',
            'drivers',
            'date',
            'totalPaxPerDay',
            'assignedPaxPerDay'
        ));
    }

    public function assignDriver4june(Request $request)
    {
        $request->validate([
            'order_ids'  => 'required|array',
            'driver_ids' => 'nullable|array', // allow empty → means remove all
            'date'       => 'required|date'
        ]);

        foreach ($request->order_ids as $orderId) {

            // ✅ Existing drivers for this order/date
            $existingDrivers = OrderDriver::where('order_id', $orderId)
                ->whereDate('assigned_date', $request->date)
                ->pluck('driver_id')
                ->toArray();

            $selectedDrivers = $request->driver_ids ?? [];

            // ✅ 1. ADD NEW DRIVERS (which are selected but not in DB)
            $toAdd = array_diff($selectedDrivers, $existingDrivers);

            foreach ($toAdd as $driverId) {
                OrderDriver::create([
                    'order_id'      => $orderId,
                    'driver_id'     => $driverId,
                    'assigned_date' => $request->date
                ]);
            }

            // ✅ 2. REMOVE DRIVERS (which are in DB but NOT selected)
            $toRemove = array_diff($existingDrivers, $selectedDrivers);

            if (!empty($toRemove)) {
                OrderDriver::where('order_id', $orderId)
                    ->whereDate('assigned_date', $request->date)
                    ->whereIn('driver_id', $toRemove)
                    ->delete();
            }
        }

        return response()->json(['success' => true]);
    }
    public function assignDriver(Request $request)
{
    $request->validate([
        'orders' => 'required|array',
        'date'   => 'required|date'
    ]);

    foreach ($request->orders as $item) {

        $orderId = $item['order_id'];
        $selectedDrivers = $item['driver_ids'] ?? [];

        // ✅ Existing drivers
        $existingDrivers = OrderDriver::where('order_id', $orderId)
            ->whereDate('assigned_date', $request->date)
            ->pluck('driver_id')
            ->toArray();

        // =========================
        // ✅ ADD NEW DRIVERS
        // =========================
        $toAdd = array_diff($selectedDrivers, $existingDrivers);

        foreach ($toAdd as $driverId) {
            OrderDriver::create([
                'order_id'      => $orderId,
                'driver_id'     => $driverId,
                'assigned_date' => $request->date
            ]);
        }

        // =========================
        // ✅ REMOVE DRIVERS
        // =========================
        $toRemove = array_diff($existingDrivers, $selectedDrivers);

        if (!empty($toRemove)) {
            OrderDriver::where('order_id', $orderId)
                ->whereDate('assigned_date', $request->date)
                ->whereIn('driver_id', $toRemove)
                ->delete();
        }
    }

    return response()->json(['success' => true]);
}
    public function removeDriver(Request $request)
    {
        OrderDriver::whereIn('order_id', $request->order_ids)
            ->where('driver_id', $request->driver_id)
            ->whereDate('assigned_date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function exportDriverManifest(Request $request)
    {
        $date = $request->input('date') ?? now()->toDateString();
        // return Excel::download(
        //     new DriverManifestExport($request->date, $request->driver_id),
        //     'driver-manifest.xlsx'
        // );
        return Excel::download(
            new DriverManifestExport($request->date, $request->driver_id),
            'driver-manifest'. $date.'.xlsx'
        );
    }

}
