<?php

namespace App\Http\Controllers;

use App\Exports\DriverManifestExport;
use App\Mail\DriverPickupMail;
use App\Mail\EmailManager;
use App\Mail\PassengerPickupMail;
use App\Models\Order;
use App\Models\OrderDriver;
use App\Models\OrderEmailHistory;
use App\Models\PickupLocation;
use App\Models\TourPricing;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;


class ManifestController extends Controller
{
    //

    /**
     * Send pickup mail to drivers for selected orders on a specific date.
     */

    public function driverPickupMail(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'orders' => ['required', 'array', 'min:1'],
            'orders.*.order_id' => ['required','integer','exists:orders,id'],
            'orders.*.order_number' => ['nullable','string'],
            'orders.*.pickup_time' => ['nullable','date_format:H:i'],
            'orders.*.driver_ids' => ['required','array','min:1'],
            'orders.*.driver_ids.*' => ['required','integer','exists:users,id'],
            'orders.*.vehicle_id' => ['nullable','integer','exists:vehicles,id'],
        ]);

        $date = $validated['date'];
        $requestedOrders = collect($validated['orders']);

        /*
        |--------------------------------------------------------------------------
        | Load Orders
        |--------------------------------------------------------------------------
        */

        $orderIds = $requestedOrders
            ->pluck('order_id')
            ->unique()
            ->values();

        $orders = Order::with([
                'customer',
                'orderTours.tour',
            ])
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Load Drivers
        |--------------------------------------------------------------------------
        */

        $driverIds = $requestedOrders
            ->flatMap(function ($item) {
                return $item['driver_ids'] ?? [];
            })
            ->filter()
            ->unique()
            ->values();

        $drivers = User::whereIn('id', $driverIds)
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Load Vehicles
        |--------------------------------------------------------------------------
        */

        $vehicleIds = $requestedOrders
            ->pluck('vehicle_id')
            ->filter()
            ->unique()
            ->values();

        $vehicles = Vehicle::whereIn('id', $vehicleIds)
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Group Selected Orders by Driver
        |--------------------------------------------------------------------------
        */

        $driverOrderGroups = [];

        foreach ($requestedOrders as $item) {

            $order = $orders->get($item['order_id']);

            if (!$order) {
                continue;
            }

            foreach ($item['driver_ids'] as $driverId) {

                $driverOrderGroups[$driverId][] = [
                    'order' => $order,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name ?? 'N/A',
                    'customer_phone' => $order->customer?->phone ?? null,
                    'guest_count' => getManifestOrderGuestCount(
                        $order,
                        $date
                    ),
                    'pickup_time' => $item['pickup_time'] ?? null,
                    'pickup_location' => getManifestPickupLocation(
                        $order
                    ),
                    'instruction' => $order->customer?->instructions,
                    'internal_notes' => $order->internal_notes,
                    'vehicle' => isset($item['vehicle_id'])
                        ? $vehicles->get($item['vehicle_id'])
                        : null,
                ];
            }
        }

        $sent = [];
        $failed = [];

        /*
        |--------------------------------------------------------------------------
        | Send One Email per Driver
        |--------------------------------------------------------------------------
        */

        foreach ($driverOrderGroups as $driverId => $driverOrders) {

            $driver = $drivers->get($driverId);

            if (!$driver) {
                $failed[] = [
                    'driver_id' => $driverId,
                    'driver_name' => 'Unknown Driver',
                    'message' => 'Driver not found.',
                ];

                continue;
            }

            if (!$driver->email) {

                $failed[] = [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->name,
                    'message' => 'Driver email address not found.',
                ];

                foreach ($driverOrders as $driverOrder) {
                    OrderEmailHistory::create([
                        'order_id'   => $driverOrder['order']->id,
                        'to_email'   => null,
                        'from_email' => env('MAIL_FROM_ADDRESS'),
                        'subject'    => "Driver email address not found.",
                        'body'       => "Driver email address not found.",
                        'status'     => 'failed',
                        'message_id' => null,
                    ]);
                }

                continue;
            }

            try {

                // Explicitly use Mailgun mailer
                $mailer = Mail::mailer('mailgun');

                // Send email and capture message inf
                $sentMessage = $mailer->to($driver->email);
                
                $sentMessage = $sentMessage->send(new EmailManager(new DriverPickupMail(
                        $driver,
                        collect($driverOrders),
                        $date
                    )));
            
                $messageId = null;
                if ($sentMessage instanceof \Illuminate\Mail\SentMessage) {
                    $symfonySent = $sentMessage->getSymfonySentMessage();
                    if ($symfonySent && method_exists($symfonySent, 'getMessageId')) {
                        $messageId = $symfonySent->getMessageId();
                        $messageId = trim($messageId, '<>');
                    }
                }

                // Mail::to($driver->email)->send(
                //     new DriverPickupMail(
                //         $driver,
                //         collect($driverOrders),
                //         $date
                //     )
                // );

                foreach ($driverOrders as $driverOrder) {
                    OrderEmailHistory::create([
                        'order_id'   => $driverOrder['order']->id,
                        'to_email'   => $driver->email,
                        'from_email' => env('MAIL_FROM_ADDRESS'),
                        'subject'    => "Driver email address not found.",
                        'body'       => 'Driver pickup mail sent to ' . $driver->name,
                        'status'     => 'sent',
                        'message_id' => $messageId,
                    ]);
                }

                $sent[] = [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->name,
                    'email' => $driver->email,
                    'orders_count' => count($driverOrders),
                    'order_numbers' => collect($driverOrders)
                        ->pluck('order_number')
                        ->values()
                        ->toArray(),
                ];

            } catch (\Throwable $e) {

                Log::error('Driver pickup mail failed', [
                    'driver_id' => $driver->id,
                    'driver_email' => $driver->email,
                    'order_ids' => collect($driverOrders)
                        ->pluck('order.id')
                        ->toArray(),
                    'error' => $e->getMessage(),
                ]);

                foreach ($driverOrders as $driverOrder) {
                    OrderEmailHistory::create([
                        'order_id'   => $driverOrder['order']->id,
                        'to_email'   => $driver->email,
                        'from_email' => env('MAIL_FROM_ADDRESS'),
                        'subject'    => "Driver email address not found.",
                        'body'       => "Driver email address not found.",
                        'status'     => 'failed',
                        'message_id' => null,
                    ]);
                }

                $failed[] = [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->name,
                    'email' => $driver->email,
                    'message' => 'Mail could not be sent.',
                ];
            }

            /*
            * Multiple drivers hone par SMTP ko slight gap milega.
            */
            usleep(500000);
        }

        return response()->json([
            'success' => count($sent) > 0,
            'message' => count($sent) > 0
                ? 'Driver pickup mail process completed.'
                : 'No driver pickup emails were sent.',
            'sent_count' => count($sent),
            'failed_count' => count($failed),
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }

    /**
     * Send pickup mail to passengers for selected orders on a specific date.
     */

    public function passengerPickupMail(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'date'     => 'required|date',
        ]);

        $order = Order::with([
            'customer',
            'orderTours.tour'
        ])->find($request->order_id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'order_id' => $request->order_id,
                'message' => 'Order not found.',
            ]);
        }

        $email = $order->customer?->email;

        // 'order_id', 'to_email', 'from_email', 'subject', 'body', 'status', 'message_id'
        if (!$email) {
            OrderEmailHistory::create([
                'order_id'   => $order->id,
                'to_email'   => $email,
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'subject'    => "Passenger Pickup Mail for Order #{$order->order_number}",
                'body'       => "Passenger pickup mail could not be sent because the customer's email address is missing.",
                'status'     => 'failed',
                'message_id' => null,
            ]);

            return response()->json([
                'success' => false,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => 'Customer email not found.',
            ]);
        }

        try {

            // $array = [
            //     'view'    => 'emails.passenger-pickup-mail',
            //     'subject' => "Passenger Pickup Mail for Order #{$order->order_number}",
            //     'from'    => env('MAIL_FROM_ADDRESS'),
            //     'content' => "Passenger Pickup Mail for Order #{$order->order_number}",
            //     'data'    => [
            //         'order' => $order,
            //         'date'  => $request->date,
            //     ]
            // ];

            // $email = 'ashutosh@tourbeez.com'; // For testing purposes, remove in production

            // Explicitly use Mailgun mailer
            $mailer = Mail::mailer('mailgun');

            // Send email and capture message inf
            $sentMessage = $mailer->to($email);
            
            $sentMessage = $sentMessage->send(
                new PassengerPickupMail($order, $request->date)
            );
        
            $messageId = null;
            if ($sentMessage instanceof \Illuminate\Mail\SentMessage) {
                $symfonySent = $sentMessage->getSymfonySentMessage();
                if ($symfonySent && method_exists($symfonySent, 'getMessageId')) {
                    $messageId = $symfonySent->getMessageId();
                    $messageId = trim($messageId, '<>');
                }
            }

            OrderEmailHistory::create([
                'order_id'   => $order->id,
                'to_email'   => $email,
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'subject'    => "Passenger Pickup Mail for Order #{$order->order_number}",
                'body'       => "Passenger pickup mail sent successfully for Order #{$order->order_number}.",
                'status'     => 'sent',
                'message_id' => $messageId
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'email' => $email,
                'message' => 'Mail sent successfully.',
            ]);

        } catch (\Exception $e) {

            OrderEmailHistory::create([
                'order_id'   => $order->id,
                'to_email'   => $email,
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'subject'    => "Passenger Pickup Mail for Order #{$order->order_number}",
                'body'       => $e->getMessage(),
                'status'     => 'failed',
                'message_id' => null,
            ]);

            return response()->json([
                'success' => false,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate driver manifest for a specific date.
    */
    public function driverManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        $selectedDriver  = $request->input('driver_id');
        $selectedVehicle = $request->input('vehicle_id');

        $startOfWeek = Carbon::parse($date);
        $endOfWeek   = Carbon::parse($date)->copy()->addDays(4);

        $driverPaxPerDay = [];
        $driverNameMap   = [];

        $dateRange = [];
        $totalPaxPerDay = [];
        $assignedPaxPerDay = [];
        $reportGroupTotals = [];

        $d = $startOfWeek->copy();

        while ($d->lte($endOfWeek)) {
            $day = $d->toDateString();

            $dateRange[] = $d->copy();
            $totalPaxPerDay[$day] = 0;
            $assignedPaxPerDay[$day] = 0;

            $d->addDay();
        }

        $orders = Order::with([
                'customer',
                'tour',
                'subTour',
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

        $orderDriverMap = OrderDriver::with(['driver', 'vehicle'])
            ->whereBetween('assigned_date', [
                $startOfWeek->toDateString(),
                $endOfWeek->copy()->addDay()->toDateString()
            ])
            ->get()
            ->groupBy(function ($item) {
                return $item->order_id . '_' . $item->assigned_date . '_' . $item->assignment_type;
            });

        $pickupLocationIds = $orders
            ->pluck('customer.pickup_id')
            ->filter()
            ->unique()
            ->values();

        $pickupLocations = PickupLocation::whereIn('id', $pickupLocationIds)
            ->get()
            ->keyBy('id');

        $grid = [];

        $tourTimes = [];
        $tourAssignableMap = [];
        $tourReportGroupMap = [];
        $tourPaxMap = [];

        foreach ($orders as $order) {

            $encryptedOrderId = encrypt($order->id);

            foreach ($order->orderTours as $ot) {

                $tourDate = $ot->tour_date;

                if (!$tourDate) {
                    continue;
                }

                if ($order->sub_tour_id && $order->subTour) {
                    $tourTitle = $order->tour?->title . '<br><small>' . ($ot->tour?->title ?? '') . '</small>';
                    $sortTitle = $order->tour?->report_group ?? 99;
                } else {
                    $tourTitle = $ot->tour?->title ?? 'Unknown Tour';
                    $sortTitle = $ot->tour?->report_group ?? 99;
                }

                $slotTime = $ot->tour_time ?? '00:00 AM';

                $guestCount = collect(
                    json_decode($ot->tour_pricing, true) ?? []
                )->sum('quantity');

                $driverKey = $order->id . '_' . $tourDate . '_tour';

                $orderDrivers = $orderDriverMap[$driverKey] ?? collect();

                $driverIds = $orderDrivers->pluck('driver_id')->filter()->values()->toArray();
                $vehicleIds = $orderDrivers->pluck('vehicle_id')->filter()->values()->toArray();

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

                $totalPaxPerDay[$tourDate] =
                    ($totalPaxPerDay[$tourDate] ?? 0) + $guestCount;

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

                        if (!$driverId) {
                            continue;
                        }

                        $driverNameMap[$driverId] = $driver->driver?->name;

                        $driverPaxPerDay[$tourDate][$driverId] =
                            ($driverPaxPerDay[$tourDate][$driverId] ?? 0) + $guestCount;
                    }
                }

                $matchDriver = !$selectedDriver || in_array($selectedDriver, $driverIds);
                $matchVehicle = !$selectedVehicle || in_array($selectedVehicle, $vehicleIds);

                if (
                    $matchDriver &&
                    $matchVehicle &&
                    !empty($driverIds) &&
                    !empty($vehicleIds)
                ) {
                    $assignedPaxPerDay[$tourDate] =
                        ($assignedPaxPerDay[$tourDate] ?? 0) + $guestCount;
                }

                $reportGroup = $sortTitle ?? 99;

                $reportGroupTotals[$reportGroup][$tourDate] =
                    ($reportGroupTotals[$reportGroup][$tourDate] ?? 0) + $guestCount;

                $tourDetail = $ot->tour?->detail;
                $pickupTime = optional($orderDrivers->first())->pickup_time;

                $pickName = '';
                $instruction = '';

                if ($order->customer && $order->customer->pickup_name) {

                    $pickName = $order->customer->pickup_name;
                    $instruction = $order->customer->instructions;

                } elseif ($order->customer && $order->customer->pickup_id) {

                    $pickLocation = $pickupLocations[$order->customer->pickup_id] ?? null;

                    $pickName = trim(
                        ($pickLocation?->location ?? '') .
                        ' - ' .
                        ($pickLocation?->address ?? '') .
                        ' - ' .
                        ($pickLocation?->time ?? '')
                    );

                    $instruction = $order->customer->instructions;
                }

                $grid[$tourTitle][$tourDate][] = [
                    'order_id'         => $order->id,
                    'order_encrypt_id' => $encryptedOrderId,
                    'order_number'     => $order->order_number,
                    'customer'         => $order->customer?->name,
                    'guest_count'      => $guestCount,
                    'driver_ids'       => $driverIds,
                    'driver_names'     => $driverNames,
                    'vehicle_ids'      => $vehicleIds,
                    'vehicle_names'    => $vehicleNames,
                    'tour_assignable'  => $tourDetail?->assign_driver ?? false,
                    'assignment_type'  => 'tour',
                    'pickup_time'      => $pickupTime,
                    'pickup_location'  => $pickName,
                    'instruction'      => $instruction,
                    'internal_notes'   => $order->internal_notes,
                ];

                $tourTimes[$tourTitle] = $slotTime;

                $tourAssignableMap[$tourTitle] = $tourDetail?->assign_driver ?? false;

                $tourReportGroupMap[$tourTitle] = $sortTitle ?? 999;

                $tourPaxMap[$tourTitle] =
                    ($tourPaxMap[$tourTitle] ?? 0) + $guestCount;

                /*
                |--------------------------------------------------------------------------
                | Next Day Pick Up
                |--------------------------------------------------------------------------
                */

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

                    $extraOrderDrivers = $orderDriverMap[$extraDriverKey] ?? collect();

                    $extraDriverIds = $extraOrderDrivers
                        ->pluck('driver_id')
                        ->filter()
                        ->values()
                        ->toArray();

                    $extraVehicleIds = $extraOrderDrivers
                        ->pluck('vehicle_id')
                        ->filter()
                        ->values()
                        ->toArray();

                    $extraDriverNames = $extraOrderDrivers
                        ->pluck('driver.name')
                        ->filter()
                        ->values()
                        ->toArray();

                    $extraVehicleNames = $extraOrderDrivers
                        ->pluck('vehicle.name')
                        ->filter()
                        ->values()
                        ->toArray();

                    $extraGuestCount = (int) ($extra['quantity'] ?? 0);

                    $totalPaxPerDay[$extraDate] =
                        ($totalPaxPerDay[$extraDate] ?? 0) + $extraGuestCount;

                    $assignedPaxPerDay[$extraDate] =
                        $assignedPaxPerDay[$extraDate] ?? 0;

                    if (
                        (!$selectedDriver || in_array($selectedDriver, $extraDriverIds)) &&
                        (!$selectedVehicle || in_array($selectedVehicle, $extraVehicleIds))
                    ) {
                        foreach ($extraOrderDrivers as $driver) {

                            if ($selectedDriver && $driver->driver_id != $selectedDriver) {
                                continue;
                            }

                            if ($selectedVehicle && $driver->vehicle_id != $selectedVehicle) {
                                continue;
                            }

                            $driverId = $driver->driver_id;

                            if (!$driverId) {
                                continue;
                            }

                            $driverNameMap[$driverId] = $driver->driver?->name;

                            $driverPaxPerDay[$extraDate][$driverId] =
                                ($driverPaxPerDay[$extraDate][$driverId] ?? 0) + $extraGuestCount;
                        }
                    }

                    $matchExtraDriver = !$selectedDriver || in_array($selectedDriver, $extraDriverIds);
                    $matchExtraVehicle = !$selectedVehicle || in_array($selectedVehicle, $extraVehicleIds);

                    if (
                        $matchExtraDriver &&
                        $matchExtraVehicle &&
                        !empty($extraDriverIds) &&
                        !empty($extraVehicleIds)
                    ) {
                        $assignedPaxPerDay[$extraDate] =
                            ($assignedPaxPerDay[$extraDate] ?? 0) + $extraGuestCount;
                    }

                    $extraReportGroup = 999;

                    $reportGroupTotals[$extraReportGroup][$extraDate] =
                        ($reportGroupTotals[$extraReportGroup][$extraDate] ?? 0) + $extraGuestCount;

                    $extraPickupTime = optional($extraOrderDrivers->first())->pickup_time;

                    $grid['Next Day Pick Up'][$extraDate][] = [
                        'order_id'         => $order->id,
                        'order_encrypt_id' => $encryptedOrderId,
                        'order_number'     => $order->order_number,
                        'customer'         => $order->customer?->name,
                        'guest_count'      => $extraGuestCount,
                        'driver_ids'       => $extraDriverIds,
                        'driver_names'     => $extraDriverNames,
                        'vehicle_ids'      => $extraVehicleIds,
                        'vehicle_names'    => $extraVehicleNames,
                        'tour_assignable'  => true,
                        'assignment_type'  => 'next_day_pickup',
                        'pickup_time'      => $extraPickupTime,
                        'pickup_location'  => $pickName,
                        'instruction'      => $instruction,
                        'internal_notes'   => $order->internal_notes,
                    ];

                    $tourTimes['Next Day Pick Up'] = '00:00 AM';
                    $tourAssignableMap['Next Day Pick Up'] = true;
                    $tourReportGroupMap['Next Day Pick Up'] = $extraReportGroup;

                    $tourPaxMap['Next Day Pick Up'] =
                        ($tourPaxMap['Next Day Pick Up'] ?? 0) + $extraGuestCount;
                }
            }
        }

        $sortedGrid = collect($grid)
            ->sortBy(function ($dates, $tour) use (
                $tourReportGroupMap,
                $tourAssignableMap,
                $tourPaxMap,
                $tourTimes
            ) {
                $reportGroup = $tourReportGroupMap[$tour] ?? 99;

                $assignableSort = ($tourAssignableMap[$tour] ?? false) ? 0 : 1;

                $hasPax = ($tourPaxMap[$tour] ?? 0) > 0 ? 0 : 1;

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

        $groupedGrid = collect($sortedGrid)->groupBy(function ($dates, $tourTitle) use ($tourReportGroupMap) {
            return $tourReportGroupMap[$tourTitle] ?? 99;
        });

        $drivers = User::where('role', 'Driver')
            ->orderBy('name')
            ->get();

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
            'selectedVehicle',
            'reportGroupTotals',
            'tourReportGroupMap',
            'groupedGrid'
        ));
    }

    /**
     * Summary of assignDriver
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignDriver(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.order_id' => 'required|integer',
            'orders.*.driver_ids' => 'nullable|array',
            'orders.*.vehicle_ids' => 'nullable|array',
            'orders.*.pickup_time' => 'nullable',
            'orders.*.assignment_type' => 'nullable|string',
            'date' => 'required|date',
        ]);

        foreach ($request->orders as $item) {

            $orderId          = $item['order_id'];
            $selectedDrivers  = $item['driver_ids'] ?? [];
            $selectedVehicles = $item['vehicle_ids'] ?? [];
            $selectedTime     = $item['pickup_time'] ?? null;
            $assignmentType   = $item['assignment_type'] ?? 'tour';

            OrderDriver::where('order_id', $orderId)
                ->whereDate('assigned_date', $request->date)
                ->where('assignment_type', $assignmentType)
                ->delete();

            foreach ($selectedDrivers as $index => $driverId) {

                if (!$driverId) {
                    continue;
                }

                OrderDriver::create([
                    'order_id'        => $orderId,
                    'driver_id'       => $driverId,
                    'vehicle_id'      => $selectedVehicles[$index] ?? $selectedVehicles[0] ?? null,
                    'assigned_date'   => $request->date,
                    'pickup_time'     => $selectedTime,
                    'assignment_type' => $assignmentType,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Driver assignment saved successfully.',
        ]);
    }

    public function driverManifest9July(Request $request)
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
        $reportGroupTotals = [];

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

                if($order->sub_tour_id && $order->subTour){

                    $tourTitle = $order->tour?->title .'<br>' . '<small>' . $ot->tour->title . '</small>';


                    $sortTitle = $order->tour?->report_group ?? 99;

                } else{
                    $tourTitle = $ot->tour->title ?? 'Unknown Tour';
                    $sortTitle = $ot->tour->report_group ?? 99;

                }


                
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

                $reportGroup = $sortTitle ?? 99;

                if (!isset($reportGroupTotals[$reportGroup][$tourDate])) {
                    $reportGroupTotals[$reportGroup][$tourDate] = 0;
                }

                $reportGroupTotals[$reportGroup][$tourDate] += $guestCount;

                $tourDetail = $ot->tour?->detail;
                

                $pickName = '';
                $instruction = '';
                $pickLocationTime = NULL;

                if ($order->customer && $order->customer->pickup_name) {

                    $pickName = $order->customer->pickup_name;
                    $instruction = $order->customer->instructions;

                } elseif ($order->customer && $order->customer->pickup_id) {

                    $pickLocation = PickupLocation::find($order->customer->pickup_id);

                    $pickName = trim(
                        ($pickLocation?->location ?? '') .
                        ' - ' .
                        ($pickLocation?->address ?? '') .
                        ' - ' .
                        ($pickLocation?->time ?? '')
                    );
                    $pickLocationTime = convertTo24HourFormat($pickLocation?->time);
                    $instruction = $order->customer->instructions;
                }

                $pickupTime = optional($orderDrivers->first())->pickup_time ?? $pickLocationTime;
                
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
                    'pickup_time' => $pickupTime,
                    'pickup_location' => $pickName,
                    'instruction'     => $instruction,
                    'internal_notes'  => $order->internal_notes,
                ];

                // Maps for sorting
                $tourTimes[$tourTitle] = $slotTime;

                $tourAssignableMap[$tourTitle] =
                    $tourDetail?->assign_driver ?? false;

                $tourReportGroupMap[$tourTitle] =
                    $sortTitle ?? 999;

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

                $reportGroup = $sortTitle ?? 99;

                if (!isset($reportGroupTotals[$reportGroup][$extraDate])) {
                    $reportGroupTotals[$reportGroup][$extraDate] = 0;
                }

                $reportGroupTotals[$reportGroup][$extraDate] += $extraGuestCount;
                

                $pickName = '';
                $instruction = '';
                $pickLocationTime = NULL;

                if ($order->customer && $order->customer->pickup_name) {

                    $pickName = $order->customer->pickup_name;
                    $instruction = $order->customer->instructions;

                } elseif ($order->customer && $order->customer->pickup_id) {

                    $pickLocation = PickupLocation::find($order->customer->pickup_id);

                    $pickName = trim(
                        ($pickLocation?->location ?? '') .
                        ' - ' .
                        ($pickLocation?->address ?? '') .
                        ' - ' .
                        ($pickLocation?->time ?? '')
                    );
                    $pickLocationTime = convertTo24HourFormat($pickLocation?->time);

                    $instruction = $order->customer->instructions;
                }

                $pickupTime = optional($orderDrivers->first())->pickup_time ?? $pickLocationTime;

                

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
                    'pickup_time' => $pickupTime,
                    'pickup_location' => $pickName,
                    'instruction'     => $instruction,
                    'internal_notes'  => $order->internal_notes,

                ];

                $tourTimes['Next Day Pick Up'] = '00:00 AM';
                $tourAssignableMap['Next Day Pick Up'] = true;
                $tourReportGroupMap['Next Day Pick Up'] = $sortTitle;
                $tourPaxMap['Next Day Pick Up'] =
                    ($tourPaxMap['Next Day Pick Up'] ?? 0) + $extraGuestCount;
            }
        }
        // dd(collect($grid));


        
        // Sort by report_group ASC first;

        
        $sortedGrid = collect($grid)
            ->sortBy(function ($dates, $tour) use (
                $tourReportGroupMap,
                $tourAssignableMap,
                $tourPaxMap,
                $tourTimes
            ) {



                $reportGroup = $tourReportGroupMap[$tour] ?? 99;
                
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

       $groupedGrid = collect($sortedGrid)->groupBy(function ($dates, $tourTitle) use ($tourReportGroupMap) {
            return $tourReportGroupMap[$tourTitle] ?? 99;
        });

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
            'selectedVehicle',
            'reportGroupTotals',
            'tourReportGroupMap',
            'groupedGrid'
        ));
    }

    public function assignDriver9July(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'date'   => 'required|date'
        ]);


        foreach ($request->orders as $item) {

            $orderId            = $item['order_id'];
            $selectedDrivers    = $item['driver_ids'] ?? [];
            $selectedVehicles   = $item['vehicle_ids'] ?? [];
            $selectedTime       = $item['pickup_time'] ?? NULL;
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
                    'pickup_time'     => $selectedTime,
                    'assignment_type' => $assignmentType,
                ]);
            }

            
        }

        return response()->json([
            'success' => true,
        ]);
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
            new DriverManifestExport($request->date, $request->driver_id, $request->vehicle_id),
            'driver-manifest'. $date.'.xlsx'
        );
    }

}
