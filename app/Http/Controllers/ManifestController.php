<?php

namespace App\Http\Controllers;

use App\Exports\DriverManifestExport;
use App\Exports\VehicleExport;
use App\Mail\DriverPickupMail;
use App\Mail\EmailManager;
use App\Mail\PassengerPickupMail;
use App\Models\Order;
use App\Models\OrderDriver;
use App\Models\OrderEmailHistory;
use App\Models\PickupLocation;
use App\Models\Tour;
use App\Models\TourPricing;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


class ManifestController extends Controller
{
    //

    /**
     * Send pickup mail to drivers for selected orders on a specific date.
     */

    public function driverPickupMail(Request $request)
{
    $validator = Validator::make($request->all(), [
        'date' => [
            'required',
            'date',
        ],

        'orders' => [
            'required',
            'array',
            'min:1',
        ],

        'orders.*.order_id' => [
            'required',
            'integer',
            'distinct',
            'exists:orders,id',
        ],

        'orders.*.order_number' => [
            'nullable',
            'string',
        ],

        'orders.*.tour_id' => [
            'nullable',
            'integer',
            'exists:tours,id',
        ],

        'orders.*.pickup_time' => [
            'nullable',
            'date_format:H:i',
        ],

        'orders.*.driver_ids' => [
            'required',
            'array',
            'min:1',
        ],

        'orders.*.driver_ids.*' => [
            'required',
            'integer',
            'exists:users,id',
        ],

        'orders.*.vehicle_id' => [
            'nullable',
            'integer',
            'exists:vehicles,id',
        ],

        'customMessage' => [
            'nullable',
            'string',
            'max:20000',
        ],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $validated = $validator->validated();

    $date = $validated['date'];

    $customMessage = $validated['customMessage'] ?? null;

    $requestedOrders = collect(
        $validated['orders']
    );

    /*
    |--------------------------------------------------------------------------
    | Load Order IDs
    |--------------------------------------------------------------------------
    */

    $orderIds = $requestedOrders
        ->pluck('order_id')
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Load Driver IDs
    |--------------------------------------------------------------------------
    */

    $driverIds = $requestedOrders
        ->flatMap(function ($item) {
            return collect(
                $item['driver_ids'] ?? []
            )->map(fn ($id) => (int) $id);
        })
        ->filter()
        ->unique()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Load Vehicle IDs
    |--------------------------------------------------------------------------
    */

    $vehicleIds = $requestedOrders
        ->pluck('vehicle_id')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Load Orders
    |--------------------------------------------------------------------------
    */

    $orders = Order::with([
        'user',
        'customer',
        'orderTours.tour',
    ])
        ->whereIn('id', $orderIds)
        ->get()
        ->keyBy('id');

    /*
    |--------------------------------------------------------------------------
    | Load Selected Drivers
    |--------------------------------------------------------------------------
    */

    $drivers = User::query()
        ->whereIn('id', $driverIds)
        ->where('role', 'Driver')
        ->get()
        ->keyBy('id');

    /*
    |--------------------------------------------------------------------------
    | Load Selected Vehicles
    |--------------------------------------------------------------------------
    */

    $vehicles = Vehicle::query()
        ->whereIn('id', $vehicleIds)
        ->get()
        ->keyBy('id');

    /*
    |--------------------------------------------------------------------------
    | Group Orders by Driver
    |--------------------------------------------------------------------------
    */

    $driverOrderGroups = [];

    foreach ($requestedOrders as $item) {
        $orderId = (int) $item['order_id'];

        $order = $orders->get($orderId);

        if (!$order) {
            continue;
        }

        $selectedDriverIds = collect(
            $item['driver_ids'] ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $vehicleId = !empty($item['vehicle_id'])
            ? (int) $item['vehicle_id']
            : null;

        $selectedVehicle = $vehicleId
            ? $vehicles->get($vehicleId)
            : null;

        foreach ($selectedDriverIds as $driverId) {
            $driverOrderGroups[$driverId][] = [
                'order' => $order,

                'order_number' =>
                    $order->order_number,

                'customer_name' =>
                    $order->customer?->name ?? 'N/A',

                'customer_email' =>
                    $order->customer?->email,

                'customer_phone' =>
                    $order->customer?->phone,

                'guest_count' =>
                    getManifestOrderGuestCount(
                        $order,
                        $date
                    ),

                'pickup_time' =>
                    $item['pickup_time'] ?? null,

                'pickup_location' =>
                    getManifestPickupLocation($order),

                'instruction' =>
                    $order->customer?->instructions,

                'internal_notes' =>
                    $order->internal_notes,

                'vehicle' =>
                    $selectedVehicle,
            ];
        }
    }

    $sent = [];
    $failed = [];

    /*
    |--------------------------------------------------------------------------
    | Send One Email Per Driver
    |--------------------------------------------------------------------------
    */

    foreach ($driverOrderGroups as $driverId => $driverOrders) {
        $driver = $drivers->get(
            (int) $driverId
        );

        if (!$driver) {
            $failed[] = [
                'driver_id' => $driverId,
                'message' => 'Driver not found.',
            ];

            continue;
        }

        if (!$driver->email) {
            foreach ($driverOrders as $driverOrder) {
                OrderEmailHistory::create([
                    'order_id' =>
                        $driverOrder['order']->id,

                    'to_email' =>
                        null,

                    'from_email' =>
                        config('mail.from.address'),

                    'subject' =>
                        'Driver Pickup Mail',

                    'body' =>
                        'Driver email missing.',

                    'status' =>
                        'failed',

                    'message_id' =>
                        null,
                ]);
            }

            $failed[] = [
                'driver_id' => $driver->id,
                'driver_name' => $driver->name,
                'message' => 'Driver email missing.',
            ];

            continue;
        }

        try {
            $driverOrdersCollection = collect(
                $driverOrders
            );

            /*
             * The order verification page accepts any valid Order ID, so the
             * driver email only needs one shared QR after the passenger list.
             */
            $galleryUploadUrl = null;
            $galleryQrUrl = null;
            $qrSourceOrder = $driverOrdersCollection
                ->pluck('order')
                ->filter()
                ->first();

            if ($qrSourceOrder) {
                try {
                    $galleryQr = generateQRCodeForPassengerPickup(
                        $qrSourceOrder
                    );

                    $galleryUploadUrl = $galleryQr[0] ?? null;
                    $galleryQrUrl = $galleryQr[1] ?? null;
                } catch (\Throwable $e) {
                    Log::warning(
                        'Driver pickup QR generation failed',
                        [
                            'driver_id' => $driver->id,
                            'order_id' => $qrSourceOrder->id,
                            'error' => $e->getMessage(),
                        ]
                    );
                }
            }

            $sentMessage = Mail::mailer('mailgun')
                ->to($driver->email)
                ->send(
                    new DriverPickupMail(
                        driver: $driver,
                        orders: $driverOrdersCollection,
                        date: $date,
                        customMessage: $customMessage,
                        galleryUploadUrl: $galleryUploadUrl,
                        galleryQrUrl: $galleryQrUrl
                    )
                );

            $messageId = null;

            if (
                $sentMessage instanceof
                \Illuminate\Mail\SentMessage
            ) {
                $symfonySentMessage =
                    $sentMessage->getSymfonySentMessage();

                if (
                    $symfonySentMessage &&
                    method_exists(
                        $symfonySentMessage,
                        'getMessageId'
                    )
                ) {
                    $rawMessageId =
                        $symfonySentMessage->getMessageId();

                    $messageId = $rawMessageId
                        ? trim($rawMessageId, '<>')
                        : null;
                }
            }

            foreach ($driverOrders as $driverOrder) {
                OrderEmailHistory::create([
                    'order_id' =>
                        $driverOrder['order']->id,

                    'to_email' =>
                        $driver->email,

                    'from_email' =>
                        config('mail.from.address'),

                    'subject' =>
                        'Driver Pickup Mail',

                    'body' =>
                        'Driver pickup mail sent successfully to ' .
                        $driver->name . '.',

                    'status' =>
                        'sent',

                    'message_id' =>
                        $messageId,
                ]);
            }

            $sent[] = [
                'driver_id' =>
                    $driver->id,

                'driver_name' =>
                    $driver->name,

                'email' =>
                    $driver->email,

                'orders_count' =>
                    count($driverOrders),

                'orders' => collect($driverOrders)
                    ->map(function ($driverOrder) use (
                        $galleryUploadUrl,
                        $galleryQrUrl
                    ) {
                        return [
                            'order_id' =>
                                $driverOrder['order']->id,

                            'order_number' =>
                                $driverOrder['order_number'],

                            'gallery_upload_url' =>
                                $galleryUploadUrl,

                            'gallery_qr_url' =>
                                $galleryQrUrl,
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        } catch (\Throwable $e) {
            Log::error(
                'Driver pickup mail failed',
                [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->name,
                    'driver_email' => $driver->email,
                    'error' => $e->getMessage(),
                ]
            );

            foreach ($driverOrders as $driverOrder) {
                OrderEmailHistory::create([
                    'order_id' =>
                        $driverOrder['order']->id,

                    'to_email' =>
                        $driver->email,

                    'from_email' =>
                        config('mail.from.address'),

                    'subject' =>
                        'Driver Pickup Mail',

                    'body' =>
                        $e->getMessage(),

                    'status' =>
                        'failed',

                    'message_id' =>
                        null,
                ]);
            }

            $failed[] = [
                'driver_id' =>
                    $driver->id,

                'driver_name' =>
                    $driver->name,

                'email' =>
                    $driver->email,

                'message' =>
                    $e->getMessage(),
            ];
        }

        usleep(500000);
    }

    $sentCount = count($sent);
    $failedCount = count($failed);

    return response()->json([
        'success' => $sentCount > 0,

        'message' => $sentCount > 0
            ? $sentCount .
                ' driver pickup email(s) sent successfully.'
            : 'No driver pickup emails were sent.',

        'sent_count' =>
            $sentCount,

        'failed_count' =>
            $failedCount,

        'sent' =>
            $sent,

        'failed' =>
            $failed,
    ], $sentCount > 0 ? 200 : 422);
}

    /**
     * Send pickup mail to passengers for selected orders on a specific date.
     */

    public function passengerPickupMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],

            'orders' => ['required', 'array', 'min:1'],

            'orders.*.order_id' => [
                'required',
                'integer',
                'distinct',
                'exists:orders,id',
            ],

            'orders.*.order_number' => [
                'nullable',
                'string',
            ],

            'orders.*.tour_id' => [
                'nullable',
                'integer',
                'exists:tours,id',
            ],

            'orders.*.pickup_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'orders.*.driver_ids' => [
                'nullable',
                'array',
            ],

            'orders.*.driver_ids.*' => [
                'integer',
                'exists:users,id',
            ],

            'orders.*.vehicle_id' => [
                'nullable',
                'integer',
                'exists:vehicles,id',
            ],

            'customMessage' => [
                'nullable',
                'string',
                'max:20000',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $date = $validated['date'];

        $customMessage = $validated['customMessage'] ?? null;

        $requestedOrders = collect(
            $validated['orders']
        );

        /*
        |--------------------------------------------------------------------------
        | Load order IDs
        |--------------------------------------------------------------------------
        */

        $orderIds = $requestedOrders
            ->pluck('order_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Load driver IDs
        |--------------------------------------------------------------------------
        */

        $driverIds = $requestedOrders
            ->flatMap(function ($item) {
                return collect(
                    $item['driver_ids'] ?? []
                )->map(fn ($id) => (int) $id);
            })
            ->filter()
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Load vehicle IDs
        |--------------------------------------------------------------------------
        */

        $vehicleIds = $requestedOrders
            ->pluck('vehicle_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Load orders
        |--------------------------------------------------------------------------
        */

        $orders = Order::with([
            'user',
            'customer',
            'orderTours.tour',
        ])
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Load selected drivers
        |--------------------------------------------------------------------------
        */

        $drivers = User::query()
            ->whereIn('id', $driverIds)
            ->where('role', 'Driver')
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Load selected vehicles
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->whereIn('id', $vehicleIds)
            ->get()
            ->keyBy('id');

        $sent = [];
        $failed = [];

        /*
        |--------------------------------------------------------------------------
        | Send separate email for every selected order
        |--------------------------------------------------------------------------
        */

        foreach ($requestedOrders as $item) {
            $orderId = (int) $item['order_id'];

            $order = $orders->get($orderId);

            if (!$order) {
                $failed[] = [
                    'order_id' => $orderId,

                    'order_number' =>
                        $item['order_number'] ?? null,

                    'message' => 'Order could not be loaded.',
                ];

                continue;
            }

            $email = $order->customer?->email;

            if (!$email) {
                OrderEmailHistory::create([
                    'order_id' => $order->id,
                    'to_email' => null,

                    'from_email' =>
                        config('mail.from.address'),

                    'subject' =>
                        'Passenger Pickup Mail',

                    'body' =>
                        'Customer email missing',

                    'status' => 'failed',

                    'message_id' => null,
                ]);

                $failed[] = [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => 'Customer email missing.',
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Get selected drivers for current order
            |--------------------------------------------------------------------------
            */

            $selectedDriverIds = collect(
                $item['driver_ids'] ?? []
            )
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $selectedDrivers = $selectedDriverIds
                ->map(function ($driverId) use ($drivers) {
                    return $drivers->get($driverId);
                })
                ->filter()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Get selected vehicle for current order
            |--------------------------------------------------------------------------
            */

            $vehicleId = !empty($item['vehicle_id'])
                ? (int) $item['vehicle_id']
                : null;

            $selectedVehicle = $vehicleId
                ? $vehicles->get($vehicleId)
                : null;

            $pickupTime =
                $item['pickup_time'] ?? null;

            try {

                $galleryQr = generateQRCodeForPassengerPickup(
                    $order,
                    false
                );

                $sentMessage = Mail::mailer('mailgun')
                    ->to($email)
                    ->send(
                        new PassengerPickupMail(
                            order: $order,
                            date: $date,
                            pickupTime: $pickupTime,
                            customMessage: $customMessage,
                            drivers: $selectedDrivers,
                            vehicle: $selectedVehicle,
                            galleryUploadUrl: $galleryQr[0],
                            galleryQrUrl: $galleryQr[1]
                        )
                    );

                $messageId = null;

                if (
                    $sentMessage instanceof
                    \Illuminate\Mail\SentMessage
                ) {
                    $symfonySentMessage =
                        $sentMessage->getSymfonySentMessage();

                    if (
                        $symfonySentMessage &&
                        method_exists(
                            $symfonySentMessage,
                            'getMessageId'
                        )
                    ) {
                        $rawMessageId =
                            $symfonySentMessage->getMessageId();

                        $messageId = $rawMessageId
                            ? trim($rawMessageId, '<>')
                            : null;
                    }
                }

                OrderEmailHistory::create([
                    'order_id' => $order->id,
                    'to_email' => $email,

                    'from_email' =>
                        config('mail.from.address'),

                    'subject' =>
                        'Passenger Pickup Mail',

                    'body' =>
                        'Passenger pickup mail sent successfully.',

                    'status' => 'sent',

                    'message_id' => $messageId,
                ]);

                $sent[] = [
                    'order_id' => $order->id,

                    'order_number' =>
                        $order->order_number,

                    'customer_name' =>
                        $order->customer?->name,

                    'email' => $email,

                    'drivers' =>
                        $selectedDrivers
                            ->pluck('name')
                            ->values()
                            ->all(),

                    'vehicle' =>
                        $selectedVehicle?->name,
                ];
            } catch (\Throwable $e) {
                Log::error(
                    'Passenger pickup mail failed',
                    [
                        'order_id' => $order->id,

                        'order_number' =>
                            $order->order_number,

                        'email' => $email,

                        'error' =>
                            $e->getMessage(),
                    ]
                );

                OrderEmailHistory::create([
                    'order_id' => $order->id,
                    'to_email' => $email,

                    'from_email' =>
                        config('mail.from.address'),

                    'subject' =>
                        'Passenger Pickup Mail',

                    'body' =>
                        $e->getMessage(),

                    'status' => 'failed',

                    'message_id' => null,
                ]);

                $failed[] = [
                    'order_id' => $order->id,

                    'order_number' =>
                        $order->order_number,

                    'email' => $email,

                    'message' =>
                        $e->getMessage(),
                ];
            }
        }

        $sentCount = count($sent);
        $failedCount = count($failed);

        return response()->json([
            'success' => $sentCount > 0,

            'message' => $sentCount > 0
                ? $sentCount .
                    ' passenger pickup email(s) sent successfully.'
                : 'No passenger pickup emails were sent.',

            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'sent' => $sent,
            'failed' => $failed,
        ], $sentCount > 0 ? 200 : 422);
    }

    /**
     * Display the driver manifest for a specific date.
     */

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
                        ($pickLocation?->address ?? '') 
                        //. ' - ' .
                        //($pickLocation?->time ?? '')
                    );
                    $pickLocationTime = convertTo24HourFormat($pickLocation?->time);
                    $instruction = $order->customer->instructions;
                }

                $pickupTime = optional($orderDrivers->first())->pickup_time ?? $pickLocationTime;
                
                // $grid[$tourTitle][$tourDate][] = [
                //     'order_id'           => $order->id,
                //     'order_encrypt_id'   => $encryptedOrderId,
                //     'order_number'       => $order->order_number,
                //     'customer'           => $order->customer?->name,
                //     'guest_count'        => $guestCount,
                //     'driver_ids'         => $driverIds,
                //     'driver_names'       => $driverNames,
                //     'vehicle_ids'        => $vehicleIds,
                //     'vehicle_names'      => $vehicleNames,
                //     'tour_assignable'    => $tourDetail?->assign_driver ?? false,
                //     'assignment_type'    => 'tour',
                //     'pickup_time' => $pickupTime,
                //     'pickup_location' => $pickName,
                //     'instruction'     => $instruction,
                //     'internal_notes'  => $order->internal_notes,
                // ];
                $grid[$tourTitle][$tourDate][] = [
                    'order_id'           => $order->id,
                    'order_encrypt_id'   => $encryptedOrderId,
                    'order_number'       => $order->order_number,

                    // Add these two lines
                    'tour_id'            => $ot->tour_id,
                    'tour_name'          => $ot->tour?->title ?? 'Unknown Tour',

                    'customer'           => $order->customer?->name,
                    'guest_count'        => $guestCount,
                    'driver_ids'         => $driverIds,
                    'driver_names'       => $driverNames,
                    'vehicle_ids'        => $vehicleIds,
                    'vehicle_names'      => $vehicleNames,
                    'tour_assignable'    => $tourDetail?->assign_driver ?? false,
                    'assignment_type'    => 'tour',
                    'pickup_time'        => $pickupTime,
                    'pickup_location'    => $pickName,
                    'instruction'        => $instruction,
                    'internal_notes'     => $order->internal_notes,
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

                

                // $grid['Next Day Pick Up'][$extraDate][] = [
                //     'order_id' => $order->id,
                //     'order_encrypt_id' => $encryptedOrderId,
                //     'order_number' => $order->order_number,
                //     'customer' => $order->customer?->name,
                //     'guest_count' => $extraGuestCount,
                //     'driver_ids' => $driverIds,
                //     'driver_names' => $driverNames,
                //     'vehicle_ids'        => $vehicleIds,
                //     'vehicle_names'      => $vehicleNames,
                //     'tour_assignable' => true,
                //     'assignment_type'    => 'next_day_pickup',
                //     'pickup_time' => $pickupTime,
                //     'pickup_location' => $pickName,
                //     'instruction'     => $instruction,
                //     'internal_notes'  => $order->internal_notes,
                // ];

                $grid['Next Day Pick Up'][$extraDate][] = [
                    'order_id' => $order->id,
                    'order_encrypt_id' => $encryptedOrderId,
                    'order_number' => $order->order_number,

                    // Add these lines
                    'tour_id' => $ot->tour_id,
                    'tour_name' => $ot->tour?->title ?? 'Other Tour',

                    'customer' => $order->customer?->name,
                    'guest_count' => $extraGuestCount,
                    'driver_ids' => $driverIds,
                    'driver_names' => $driverNames,
                    'vehicle_ids' => $vehicleIds,
                    'vehicle_names' => $vehicleNames,
                    'tour_assignable' => true,
                    'assignment_type' => 'next_day_pickup',
                    'pickup_time' => $pickupTime,
                    'pickup_location' => $pickName,
                    'instruction' => $instruction,
                    'internal_notes' => $order->internal_notes,
                ];

                $tourTimes['Next Day Pick Up'] = '00:00 AM';
                $tourAssignableMap['Next Day Pick Up'] = true;
                $tourReportGroupMap['Next Day Pick Up'] = $sortTitle;
                $tourPaxMap['Next Day Pick Up'] = ($tourPaxMap['Next Day Pick Up'] ?? 0) + $extraGuestCount;
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


   public function vehicleManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        $startOfWeek = Carbon::parse($date);
        $endOfWeek   = Carbon::parse($date)->copy()->addDays(6);

        $selectedVehicle = $request->vehicle_id;

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
        ->when($selectedVehicle, function ($q) use ($selectedVehicle) {
            $q->where('vehicle_id', $selectedVehicle);
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
                        '<br><small>' .
                        $tour->tour->title .
                        '</small>';

                } else {

                    $tourTitle = $tour->tour->title ?? 'Unknown Tour';
                }

                $grid[$vehicleName][$assignment->assigned_date][] = [

                    'order_id' => $order->id,
                    'order_encrypt_id' => encrypt($order->id),
                    'order_number' => $order->order_number,
                    'customer' => $order->customer?->name,
                    'guest_count' => $guestCount,

                    'driver_name' => $assignment->driver?->name,
                    'vehicle_name' => $vehicleName,

                    'pickup_time' => $assignment->pickup_time,
                    'pickup_location' => $assignment->pickup_location,

                    'assignment_type' => $assignment->assignment_type,

                    'tour_title' => $tourTitle,

                    'internal_notes' => $order->internal_notes,

                ];

                $vehicleTotals[$assignment->assigned_date][$vehicleName] =
                    ($vehicleTotals[$assignment->assigned_date][$vehicleName] ?? 0)
                    + $guestCount;
            }
        }

        ksort($grid);

        $vehicles = Vehicle::orderBy('name')->get();

        return view(
            'admin.manifest.vehicle',
            compact(
                'grid',
                'dateRange',
                'vehicles',
                'selectedVehicle',
                'vehicleTotals',
                'date'
            )
        );
    }


    public function exportVehicleManifest(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        return Excel::download(
            new VehicleExport(
                $request->date,
                $request->vehicle_id
            ),
            'Vehicle_Manifest_'.\Carbon\Carbon::parse($request->date)->format('d_M_Y').'.xlsx'
        );
    }

    /**
     * Assign drivers to orders for a specific date.
     */

    public function assignDriver(Request $request)
    {
        // $request->validate([
        //     'orders' => 'required|array',
        //     'date'   => 'required|date'
        // ]);

        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*.order_id' => 'required|integer',
            'orders.*.driver_ids' => 'nullable|array',
            'orders.*.vehicle_ids' => 'nullable|array',
            'orders.*.pickup_time' => 'required|date_format:H:i',
            'orders.*.assignment_type' => 'nullable|string',
            'date' => 'required|date',
        ],[
            'orders.required' => 'At least one order is required.',
            'orders.*.pickup_time.required' => 'Pickup time is required.',
            'orders.*.pickup_time.date_format' => 'Pickup time must be in HH:MM:SS format.',
        ]);

        //if validation failed return in json format
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }


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

    /**
     * Remove a driver from selected orders for a specific date.
     */
    public function removeDriver(Request $request)
    {
        OrderDriver::whereIn('order_id', $request->order_ids)
            ->where('driver_id', $request->driver_id)
            ->whereDate('assigned_date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Export driver manifest to Excel for a specific date.
     */
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

    public function getTourItinerary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_ids' => ['required', 'array', 'min:1'],

            'tour_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:tours,id',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tourIds = collect($validator->validated()['tour_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $tours = Tour::query()
            ->with([
                'itineraries' => function ($query) {
                    $query->orderBy('order');
                },
            ])
            ->whereIn('id', $tourIds)
            ->get();

        if ($tours->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tour was not found.',
                'itinerary' => '',
            ], 404);
        }

        $sections = [];

        foreach ($tours as $tour) {
            $lines = $tour->itineraries
                ->map(function ($itinerary) {

                    $time = $this->formatTourItineraryTime(
                        strtoupper($itinerary->datetime)
                    );

                    $title = trim(
                        strip_tags($itinerary->title ?? '')
                    );

                    if (
                        collect([
                            'day tour begins',
                            'drop-off',
                            'drop off',
                            'dropoff',
                            'evening tour begins'
                        ])->contains(fn ($word) => str_contains(strtolower($title), $word))
                    ) {
                        return null;
                    }

                    $address = trim(
                        strip_tags($itinerary->address ?? '')
                    );
                    $address = preg_replace('/(Canada).*/', '$1', $address);

                    if ($title === '' && $address === '') {
                        return null;
                    }

                    $text = e($title);

                    if ($address !== '') {
                        $text .= ' - ' . e($address);
                    }

                    return '
                        <tr>
                            <td style="position: relative;
                        left: none;
                        z-index: 2;
                        vertical-align: middle !important;
                        width:100px !important;
                        min-width:100px !important;
                        font-weight:bold;
                        background:white;">
                                ' . e($time ?: '00:00') . '
                            </td>

                            <td style="flex:1;font-size:15px;">
                                ' . $text . '
                            </td>
                        </tr>
                    ';
                })
                ->filter()
                ->values();

            if ($lines->isEmpty()) {
                continue;
            }

            if($tour->id === 71) {
                $sections[] = $lines->implode(''); break;
            //}
            //else if ($tour->id === 71 && $tours->count() > 1) {
                //$sections[] = $lines->implode(''); break;
            } else {
                $sections[] = $lines->implode('');
            }
        }

        $itineraryHtml = collect($sections)
            ->filter()
            ->implode('');

        $fullItineraryHtml = '
        <table width="100%" cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#d7dce3;background:#ffffff;font-size:14px">
            <tbody>
            <tr style="background:#01228c;color:#ffffff">
                <th align="center" width="120" 
                style="position: relative;
                        left: none;
                        z-index: 2;
                        font-weight: 600;
                        min-width: auto !important;
                        vertical-align: middle !important;
                        background:inherit;">Time</th>
                <th align="left">Activity</th>
            </tr>            
            </tbody>
            '.$itineraryHtml.'
        </table>';

        return response()->json([
            'success' => true,

            'message' => $itineraryHtml
                ? 'Itinerary loaded successfully.'
                : 'No itinerary found for the selected tour.',

            'itinerary' => $fullItineraryHtml,
        ]);
    }

    private function formatTourItineraryTime($time): ?string
    {
        if (!$time) {
            return null;
        }

        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            return trim((string) $time);
        }
    }

    /**
     * Generate driver manifest for a specific date.
    */
    public function driverManifest123(Request $request)
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
                //$pickupTime = optional($orderDrivers->first())->pickup_time;

                $pickName = '';
                $instruction = '';
                $pickLocationTime = null;

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
                    $pickLocationTime = convertTo24HourFormat($pickLocation?->time);
                    $instruction = $order->customer->instructions;
                }

                $pickupTime = optional($orderDrivers->first())->pickup_time ?? $pickLocationTime;

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
    public function assignDriver123(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.order_id' => 'required|integer',
            'orders.*.driver_ids' => 'nullable|array',
            'orders.*.vehicle_ids' => 'nullable|array',
            'orders.*.pickup_time' => 'required|date_format:H:i:s',
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
}
