<?php

namespace App\Http\Controllers;

use App\Exports\DriverManifestExport;
use App\Models\Order;
use App\Models\OrderDriver;
use App\Models\TourPricing;
use App\Models\User;
use App\Models\Vehicle;
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
        $vehicles = Vehicle::orderBy('name')->get();

        return view('admin.manifest.driver-1', compact(
            'sortedGrid',
            'dateRange',
            'tourTimes',
            'drivers',
            'vehicles',
            'date'
        ));
    }

    public function driverManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();
        $selectedDriver = $request->input('driver_id');
        $selectedVehicle = $request->input('vehicle_id');

        $startOfWeek = Carbon::parse($date);
        $endOfWeek   = Carbon::parse($date)->copy()->addDays(6);

        $driverPaxPerDay = [];   // [date][driver_id] => pax
        $driverNameMap = [];     // [driver_id] => name

        $orders = Order::with([
                'customer',
                'orderTours.tour.detail'
            ])
            ->where('order_status', 5)
            ->whereHas('orderTours', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('tour_date', [
                    $startOfWeek->toDateString(),
                    $endOfWeek->toDateString()
                ]);
            })
            ->get();

        $orderDriverMap = OrderDriver::with('driver')
            ->whereBetween('assigned_date', [
                $startOfWeek->toDateString(),
                $endOfWeek->toDateString()
            ])
            ->get()
            ->groupBy(function ($item) {
                return $item->order_id . '_' . $item->assigned_date . '_' . $item->assignment_type;
            });

        $grid = [];

        $tourTimes = [];
        $tourAssignableMap = [];
        $tourReportGroupMap = [];
        $tourPaxMap = [];

        $totalPaxPerDay = [];
        $assignedPaxPerDay = [];
        $dateRange = [];

        $d = $startOfWeek->copy();

        while ($d->lte($endOfWeek)) {

            $day = $d->toDateString();

            $dateRange[] = $d->copy();

            $totalPaxPerDay[$day] = 0;
            $assignedPaxPerDay[$day] = 0;

            $d->addDay();
        }

        foreach ($orders as $order) {

            $encryptedOrderId = encrypt($order->id);

            foreach ($order->orderTours as $ot) {

                $tourDate = $ot->tour_date;

                if (!$tourDate) {
                    continue;
                }

                $tourTitle = $ot->tour->title ?? 'Unknown Tour';
                $slotTime  = $ot->tour_time ?? '00:00 AM';

                $guestCount = collect(
                    json_decode($ot->tour_pricing, true) ?? []
                )->sum('quantity');

                // $driverKey = $order->id . '_' . $tourDate;
                $driverKey = $order->id . '_' . $tourDate . '_tour';

                $orderDrivers = $orderDriverMap[$driverKey] ?? collect();

                $driverIds = $orderDrivers->pluck('driver_id')->toArray();
                $vehicleIds = $orderDrivers->pluck('vehicle_id')->toArray();

                $driverNames = $orderDrivers
                    ->pluck('driver.name')
                    ->filter()
                    ->values()
                    ->toArray();

                $vehicleNames = $orderDrivers
                    ->pluck('vehicle.name')
                    ->filter()
                    ->values()
                    ->toArray();    

                // Totals
                $totalPaxPerDay[$tourDate] += $guestCount;

                if (
                        (!$selectedDriver || in_array($selectedDriver, $driverIds)) &&
                        (!$selectedVehicle || in_array($selectedVehicle, $vehicleIds))
                    ) {
                                        
                        foreach ($orderDrivers as $driver) {

                        if ($selectedDriver && $driver->driver_id != $selectedDriver) {
                            continue;
                        }

                        if ($selectedVehicle && $driver->vehicle_id != $selectedVehicle) {
                            continue;
                        }

                        $driverId = $driver->driver_id;
                        $driverName = $driver->driver?->name;

                        $driverNameMap[$driverId] = $driverName;

                        if (!isset($driverPaxPerDay[$tourDate][$driverId])) {
                            $driverPaxPerDay[$tourDate][$driverId] = 0;
                        }

                        $driverPaxPerDay[$tourDate][$driverId] += $guestCount;
                    }
                }

                $matchDriver = !$selectedDriver || in_array($selectedDriver, $driverIds);
                $matchVehicle = !$selectedVehicle || in_array($selectedVehicle, $vehicleIds);

                if ($matchDriver && $matchVehicle && !empty($driverIds)) {
                    $assignedPaxPerDay[$tourDate] += $guestCount;
                }

                $tourDetail = $ot->tour?->detail;

                $grid[$tourTitle][$tourDate][] = [
                    'order_id'           => $order->id,
                    'order_encrypt_id'   => $encryptedOrderId,
                    'order_number'       => $order->order_number,
                    'customer'           => $order->customer?->name,
                    'guest_count'        => $guestCount,
                    'driver_ids'         => $driverIds,
                    'driver_names'       => $driverNames,
                    'vehicle_ids'        => $vehicleIds,
                    'vehicle_names'      => $vehicleNames,
                    'tour_assignable'    => $tourDetail?->assign_driver ?? false,
                    'assignment_type'    => 'tour',
                ];

                // Maps for sorting
                $tourTimes[$tourTitle] = $slotTime;

                $tourAssignableMap[$tourTitle] =
                    $tourDetail?->assign_driver ?? false;

                $tourReportGroupMap[$tourTitle] =
                    $ot->tour->report_group ?? 999;

                $tourPaxMap[$tourTitle] =
                    ($tourPaxMap[$tourTitle] ?? 0) + $guestCount;
            }
            $extras = json_decode($ot->tour_extra, true) ?? [];

            foreach ($extras as $extra) {

                if (
                    !isset($extra['label']) ||
                    stripos($extra['label'], 'Next Day Pick Up') === false
                ) {
                    continue;
                }

                $extraDate = Carbon::parse($tourDate)
                    ->addDay()
                    ->toDateString();

                $extraDriverKey = $order->id . '_' . $extraDate . '_next_day_pickup';
                $orderDrivers = $orderDriverMap[$extraDriverKey] ?? collect();

                $driverIds = $orderDrivers->pluck('driver_id')->toArray();

                $driverNames = $orderDrivers
                    ->pluck('driver.name')
                    ->filter()
                    ->values()
                    ->toArray();
                $vehicleIds = $orderDrivers->pluck('vehicle_id')->toArray();

                $driverNames = $orderDrivers
                    ->pluck('driver.name')
                    ->filter()
                    ->values()
                    ->toArray();

                $vehicleNames = $orderDrivers
                    ->pluck('vehicle.name')
                    ->filter()
                    ->values()
                    ->toArray();

                $extraGuestCount = (int)($extra['quantity'] ?? 0);

                if (!isset($totalPaxPerDay[$extraDate])) {
                    $totalPaxPerDay[$extraDate] = 0;
                    $assignedPaxPerDay[$extraDate] = 0;
                }

                $totalPaxPerDay[$extraDate] += $extraGuestCount;

               if (
                        (!$selectedDriver || in_array($selectedDriver, $driverIds)) &&
                        (!$selectedVehicle || in_array($selectedVehicle, $vehicleIds))
                    ) {

                    foreach ($orderDrivers as $driver) {

                        if ($selectedDriver && $driver->driver_id != $selectedDriver) {
                            continue;
                        }

                        if ($selectedVehicle && $driver->vehicle_id != $selectedVehicle) {
                            continue;
                        }

                        $driverId = $driver->driver_id;
                        $driverNameMap[$driverId] = $driver->driver?->name;

                        $driverPaxPerDay[$extraDate][$driverId] =
                            ($driverPaxPerDay[$extraDate][$driverId] ?? 0)
                            + $extraGuestCount;
                    }
                }

                $matchDriver = !$selectedDriver || in_array($selectedDriver, $driverIds);
                $matchVehicle = !$selectedVehicle || in_array($selectedVehicle, $vehicleIds);

                if ($matchDriver && $matchVehicle && !empty($driverIds)) {
                    $assignedPaxPerDay[$extraDate] += $extraGuestCount;
                }

                $grid['Next Day Pick Up'][$extraDate][] = [
                    'order_id' => $order->id,
                    'order_encrypt_id' => $encryptedOrderId,
                    'order_number' => $order->order_number,
                    'customer' => $order->customer?->name,
                    'guest_count' => $extraGuestCount,
                    'driver_ids' => $driverIds,
                    'driver_names' => $driverNames,
                    'vehicle_ids'        => $vehicleIds,
                    'vehicle_names'      => $vehicleNames,
                    'tour_assignable' => true,
                    'assignment_type'    => 'next_day_pickup',
                ];

                $tourTimes['Next Day Pick Up'] = '00:00 AM';
                $tourAssignableMap['Next Day Pick Up'] = true;
                $tourReportGroupMap['Next Day Pick Up'] = 999;
                $tourPaxMap['Next Day Pick Up'] =
                    ($tourPaxMap['Next Day Pick Up'] ?? 0) + $extraGuestCount;
            }
        }

        // Sort by report_group ASC first
        $sortedGrid = collect($grid)
            ->sortBy(function ($dates, $tour) use (
                $tourReportGroupMap,
                $tourAssignableMap,
                $tourPaxMap,
                $tourTimes
            ) {

                $reportGroup = $tourReportGroupMap[$tour] ?? 999;

                $assignableSort =
                    ($tourAssignableMap[$tour] ?? false) ? 0 : 1;

                $hasPax =
                    ($tourPaxMap[$tour] ?? 0) > 0 ? 0 : 1;

                try {
                    $timeSort = Carbon::parse(
                        $tourTimes[$tour] ?? '00:00 AM'
                    )->format('Hi');
                } catch (\Exception $e) {
                    $timeSort = 9999;
                }

                return sprintf(
                    '%04d_%d_%d_%s',
                    $reportGroup,
                    $assignableSort,
                    $hasPax,
                    $timeSort
                );
            })
            ->toArray();

        $drivers = User::where('role', 'Driver')->orderBy('name')->get();
        $vehicles = Vehicle::orderBy('id')->get();

        $vehicleNameMap = Vehicle::pluck('name', 'id')->toArray();

        return view('admin.manifest.driver', compact(
            'sortedGrid',
            'dateRange',
            'tourTimes',
            'drivers',
            'vehicles',
            'vehicleNameMap',
            'date',
            'totalPaxPerDay',
            'assignedPaxPerDay',
            'driverPaxPerDay',
            'driverNameMap',
            'selectedDriver',
            'selectedVehicle'
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

            $orderId            = $item['order_id'];
            $selectedDrivers    = $item['driver_ids'] ?? [];
            $selectedVehicles   = $item['vehicle_ids'] ?? [];
            $assignmentType     = $item['assignment_type'] ?? 'tour';

            // =========================
            // Existing drivers
            // =========================
            $existingDrivers = OrderDriver::where('order_id', $orderId)
                ->whereDate('assigned_date', $request->date)
                ->where('assignment_type', $assignmentType)
                ->pluck('driver_id')
                ->toArray();
            
            // =========================
            // REMOVE DRIVERS
            // =========================
            // $toRemove = array_diff($existingDrivers, $selectedDrivers);
            if (!empty($existingDrivers)) {
                OrderDriver::where('order_id', $orderId)
                    ->whereDate('assigned_date', $request->date)
                    ->where('assignment_type', $assignmentType)
                    ->whereIn('driver_id', $existingDrivers)
                    ->delete();
            }

            // =========================
            // ADD NEW DRIVERS
            // =========================
            // $toAdd = array_diff($selectedDrivers, $existingDrivers);
            foreach ($selectedDrivers as $index => $driverId) {
                OrderDriver::create([
                    'order_id'        => $orderId,
                    'driver_id'       => $driverId,
                    'vehicle_id'      => $selectedVehicles[0] ?? null,
                    'assigned_date'   => $request->date,
                    'assignment_type' => $assignmentType,
                ]);
            }

            
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function assignDriver_prenextdaytour(Request $request)
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
