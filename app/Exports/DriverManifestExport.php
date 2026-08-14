<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderDriver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DriverManifestExport implements
    FromCollection,
    WithColumnWidths,
    WithStyles,
    WithEvents,
    ShouldAutoSize
{
    protected $date;
    protected $driverId;
    protected $vehicleId;

    public function __construct($date, $driverId = null, $vehicleId = null)
    {
        $this->date = $date;
        $this->driverId = $driverId;
        $this->vehicleId = $vehicleId;
    }


    public function collection()
{
    $startOfWeek = Carbon::parse($this->date);
    $endOfWeek   = Carbon::parse($this->date)->copy()->addDays(4);

    $driverPaxPerDay = [];
    $driverNameMap   = [];

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

    $orderDriverMap = OrderDriver::with([
            'driver',
            'vehicle'
        ])
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

        $matchDriver = !$this->driverId || in_array($this->driverId, $driverIds);
        $matchVehicle = !$this->vehicleId || in_array($this->vehicleId, $vehicleIds);

        if (!$matchDriver || !$matchVehicle) {
            continue;
        }

        if ($this->driverId) {
            $orderDrivers = $orderDrivers->where('driver_id', $this->driverId);
        }

        if ($this->vehicleId) {
            $orderDrivers = $orderDrivers->where('vehicle_id', $this->vehicleId);
        }

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

            // -----------------------------------
            // TOTALS
            // -----------------------------------

            $totalPaxPerDay[$tourDate] += $guestCount;

            foreach ($orderDrivers as $driver) {

                $driverId = $driver->driver_id;

                $driverNameMap[$driverId] = $driver->driver?->name;

                if (!isset($driverPaxPerDay[$tourDate][$driverId])) {
                    $driverPaxPerDay[$tourDate][$driverId] = 0;
                }

                $driverPaxPerDay[$tourDate][$driverId] += $guestCount;
            }

            $matchDriver = !$this->driverId || in_array($this->driverId, $driverIds);
            $matchVehicle = !$this->vehicleId || in_array($this->vehicleId, $vehicleIds);

            if ($matchDriver && $matchVehicle && !empty($driverIds)) {
                $assignedPaxPerDay[$tourDate] += $guestCount;
            }

            $tourDetail = $ot->tour?->detail;

            $grid[$tourTitle][$tourDate][] = [

                'guest_count'   => $guestCount,

                'driver_names'  => $driverNames,

                'vehicle_names' => $vehicleNames,

                'driver_ids'    => $driverIds,

                'vehicle_ids'   => $vehicleIds,

                'assignment_type' => 'tour',

                'tour_assignable' => $tourDetail?->assign_driver ?? false,
            ];

            $tourTimes[$tourTitle] =
                $slotTime;

            $tourAssignableMap[$tourTitle] =
                $tourDetail?->assign_driver ?? false;

            $tourReportGroupMap[$tourTitle] =
                $ot->tour->report_group ?? 999;

            $tourPaxMap[$tourTitle] =
                ($tourPaxMap[$tourTitle] ?? 0)
                + $guestCount;

            /*
            |--------------------------------------------------------------------------
            | NEXT DAY PICKUP
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

                $driverKey =
                    $order->id .
                    '_' .
                    $extraDate .
                    '_next_day_pickup';

                $pickupDrivers =
                    $orderDriverMap[$driverKey] ?? collect();

                $driverIds =
                    $pickupDrivers->pluck('driver_id')->toArray();

                $vehicleIds =
                    $pickupDrivers->pluck('vehicle_id')->toArray();

                $driverNames =
                    $pickupDrivers->pluck('driver.name')
                    ->filter()
                    ->values()
                    ->toArray();

                $vehicleNames =
                    $pickupDrivers->pluck('vehicle.name')
                    ->filter()
                    ->values()
                    ->toArray();

                $matchDriver = !$this->driverId || in_array($this->driverId, $driverIds);
                $matchVehicle = !$this->vehicleId || in_array($this->vehicleId, $vehicleIds);

                if (!$matchDriver || !$matchVehicle) {
                    continue;
                }

                if ($this->driverId) {
                    $pickupDrivers = $pickupDrivers->where('driver_id', $this->driverId);
                }

                if ($this->vehicleId) {
                    $pickupDrivers = $pickupDrivers->where('vehicle_id', $this->vehicleId);
                }

                $driverIds = $pickupDrivers->pluck('driver_id')->toArray();

                $vehicleIds = $pickupDrivers->pluck('vehicle_id')->toArray();

                $driverNames = $pickupDrivers
                    ->pluck('driver.name')
                    ->filter()
                    ->values()
                    ->toArray();

                $vehicleNames = $pickupDrivers
                    ->pluck('vehicle.name')
                    ->filter()
                    ->values()
                    ->toArray();

                $extraGuestCount =
                    (int)($extra['quantity'] ?? 0);

                if (!isset($totalPaxPerDay[$extraDate])) {

                    $totalPaxPerDay[$extraDate] = 0;
                    $assignedPaxPerDay[$extraDate] = 0;
                }

                $totalPaxPerDay[$extraDate] +=
                    $extraGuestCount;

                foreach ($pickupDrivers as $driver) {

                    $driverId = $driver->driver_id;

                    $driverNameMap[$driverId] =
                        $driver->driver?->name;

                    $driverPaxPerDay[$extraDate][$driverId] =
                        ($driverPaxPerDay[$extraDate][$driverId] ?? 0)
                        + $extraGuestCount;
                }

                $matchDriver = !$this->driverId || in_array($this->driverId, $driverIds);
                $matchVehicle = !$this->vehicleId || in_array($this->vehicleId, $vehicleIds);

                if ($matchDriver && $matchVehicle && !empty($driverIds)) {
                    $assignedPaxPerDay[$extraDate] += $extraGuestCount;
                }

                $grid['Next Day Pick Up'][$extraDate][] = [

                    'guest_count' => $extraGuestCount,

                    'driver_names' => $driverNames,

                    'vehicle_names' => $vehicleNames,

                    'driver_ids' => $driverIds,

                    'vehicle_ids' => $vehicleIds,

                    'assignment_type' => 'next_day_pickup',

                    'tour_assignable' => true,
                ];

                $tourTimes['Next Day Pick Up'] = '00:00 AM';

                $tourAssignableMap['Next Day Pick Up'] = true;

                $tourReportGroupMap['Next Day Pick Up'] = 999;

                $tourPaxMap['Next Day Pick Up'] =
                    ($tourPaxMap['Next Day Pick Up'] ?? 0)
                    + $extraGuestCount;
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
        });

        // 🧾 BUILD EXCEL ROWS
        $rows = [];

        // HEADER
           // ==========================
    // BUILD EXCEL
    // ==========================

    // $rows = [];

    // Header
    $header = ['Tours'];

    foreach ($dateRange as $d) {
        $header[] = $d->format('j-M-Y');
    }

    $rows[] = $header;

    // Day Row
    $dayRow = [''];

    foreach ($dateRange as $d) {
        $dayRow[] = $d->format('l');
    }

    $rows[] = $dayRow;

    // ==========================
    // TOUR ROWS
    // ==========================
    $tourKeys = array_keys($sortedGrid->toArray());

    foreach ($sortedGrid as $tourTitle => $dates) {

        $currentIndex = array_search($tourTitle, $tourKeys);

        $currentGroup = $tourReportGroupMap[$tourTitle] ?? 99;

        $nextTour = $tourKeys[$currentIndex + 1] ?? null;

        $nextGroup = $nextTour
            ? ($tourReportGroupMap[$nextTour] ?? 99)
            : null;

        $row = [$tourTitle];

        foreach ($dateRange as $d) {

            $dateKey = $d->toDateString();

            $orders = $dates[$dateKey] ?? [];

            if (empty($orders)) {
                $row[] = '';
                continue;
            }

            $totalGuests = collect($orders)->sum('guest_count');

            $driverSummary = [];

            foreach ($orders as $order) {

                $drivers  = $order['driver_names'] ?? [];
                $vehicles = $order['vehicle_names'] ?? [];

                foreach ($drivers as $index => $driver) {

                    if (!isset($driverSummary[$driver])) {

                        $driverSummary[$driver] = [
                            'pax' => 0,
                            'vehicles' => [],
                        ];
                    }

                    $driverSummary[$driver]['pax'] += $order['guest_count'];

                    if (!empty($vehicles[$index])) {
                        $driverSummary[$driver]['vehicles'][$vehicles[$index]] = true;
                    }
                }
            }

            $cell = '';

            if ($totalGuests > 0) {

                $cell .= 'Total - ' . $totalGuests;

                foreach ($driverSummary as $driver => $info) {

                    $vehicleText = '';

                    if (!empty($info['vehicles'])) {
                        $vehicleText =
                            ' (' .
                            implode(', ', array_keys($info['vehicles'])) .
                            ')';
                    }

                    $cell .= "\n"
                        . $info['pax']
                        . ' - '
                        . $driver
                        . $vehicleText;
                }
            }

            $row[] = trim($cell);
        }

        $rows[] = $row;
        if ($nextGroup !== $currentGroup) {

            $groupRow = [
                'Total ' . report_group_tour_status($currentGroup)
            ];

            foreach ($dateRange as $d) {

                $groupTotal = 0;

                foreach ($sortedGrid as $title => $tourDates) {

                    if (($tourReportGroupMap[$title] ?? 99) != $currentGroup) {
                        continue;
                    }

                    $orders = collect($tourDates[$d->toDateString()] ?? [])
                                ->filter(function ($o) {

                                    $driverMatch = !$this->driverId
                                        || in_array($this->driverId, $o['driver_ids'] ?? []);

                                    $vehicleMatch = !$this->vehicleId
                                        || in_array($this->vehicleId, $o['vehicle_ids'] ?? []);

                                    return $driverMatch && $vehicleMatch;
                                });

                    $groupTotal += $orders->sum('guest_count');
                }

                $groupRow[] = $groupTotal;
            }

            $rows[] = $groupRow;
        }
    }

    // ==========================
    // TOTAL PAX
    // ==========================

    $totalRow = ['Total Pax'];

    foreach ($dateRange as $d) {

        $totalRow[] =
            $totalPaxPerDay[$d->toDateString()] ?? 0;
    }

    $rows[] = $totalRow;

    // ==========================
    // ASSIGNED PAX
    // ==========================

    $assignedRow = ['Assigned Pax'];

    foreach ($dateRange as $d) {

        $assignedRow[] =
            $assignedPaxPerDay[$d->toDateString()] ?? 0;
    }

    $rows[] = $assignedRow;

    // ==========================
    // DRIVER TOTALS
    // ==========================

    if (!empty($driverNameMap)) {

        $rows[] = [];

        $rows[] = ['Driver Totals'];

        asort($driverNameMap);

        foreach ($driverNameMap as $driverId => $driverName) {

            $driverRow = [$driverName];

            foreach ($dateRange as $d) {

                $driverRow[] =
                    $driverPaxPerDay[$d->toDateString()][$driverId] ?? 0;
            }

            $rows[] = $driverRow;
        }
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
    public function styles(Worksheet $sheet)
{
    return [
        1 => [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ],

        2 => [
            'font' => [
                'bold' => true,
            ],
        ],
    ];
}

public function registerEvents(): array
{
    return [

        AfterSheet::class => function (AfterSheet $event) {

            $sheet = $event->sheet;

            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            $range = 'A1:' . $highestColumn . $highestRow;

            $sheet->getStyle($range)
                ->getAlignment()
                ->setWrapText(true);

            $sheet->getStyle($range)
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP);

            $sheet->getStyle($range)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // Header row
            $sheet->getStyle("A1:{$highestColumn}2")
                ->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    // 'fill' => [
                    //     'fillType' => Fill::FILL_SOLID,
                    //     'startColor' => [
                    //         'rgb' => 'D9EAD3',
                    //     ],
                    // ],
                ]);

            // Total Pax row
            for ($row = 1; $row <= $highestRow; $row++) {

                $value = $sheet->getCell("A{$row}")->getValue();

                if ($value == 'Total Pax') {

                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            // 'fill' => [
                            //     'fillType' => Fill::FILL_SOLID,
                            //     'startColor' => [
                            //         'rgb' => 'FFF2CC',
                            //     ],
                            // ],
                        ]);
                }

                if ($value == 'Assigned Pax') {

                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            // 'fill' => [
                            //     'fillType' => Fill::FILL_SOLID,
                            //     'startColor' => [
                            //         'rgb' => 'D0E0E3',
                            //     ],
                            // ],
                        ]);
                }

                if ($value == 'Driver Totals') {

                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'EAD1DC',
                                ],
                            ],
                        ]);
                }

                $sheet->getRowDimension($row)->setRowHeight(35);
            }

            $sheet->freezePane('B3');
        },

    ];
}
}