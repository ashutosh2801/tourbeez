<x-admin>
@section('title', 'Driver Manifest')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
.table-scroll-wrapper {
    max-width: 800px;
    height: 420px;
    overflow: auto;
    cursor: grab;
    border: 1px solid #dee2e6;
    background: #fff;
}

.table-scroll-wrapper.active {
    cursor: grabbing;
}

table {
    min-width: 1000px;
    user-select: none;
    margin-bottom: 0;
}

th,
td {
    min-width: 150px !important;
    white-space: nowrap;
    vertical-align: middle;
}

thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    background: #212529 !important;
    color: #fff;
}

th:first-child,
td:first-child {
    position: sticky;
    left: 0;
    z-index: 2;
    font-weight: 600;
    min-width: 300px !important;
    vertical-align: middle !important;
    background: #f1f5f9;
}

thead th:first-child {
    z-index: 4;
}

.manifest-grid {
    font-size: 16px;
}

.manifest-grid th {
    font-size: 14px;
    font-weight: 600;
    padding: 10px 8px;
    background: #f1f5f9;
}

.manifest-grid td {
    font-size: 14px;
    padding: 10px 8px;
    vertical-align: middle;
}

.manifest-grid td strong {
    font-size: 18px;
    font-weight: 700;
    display:inline-block;
    padding: 2px 8px;
    background: #e2e8f0;
    color: #1f2937;
    border-radius: 10px;
    width: 40px;
    height: 40px;
    line-height: 37px;
    text-align: center;
}

.manifest-grid td p {
    margin: 0;
}

.manifest-grid small {
    font-size: 12px;
    font-weight: 500;
}

.manifest-cell.has-orders:hover {
    transition: 0.2s;
}

.total-pax {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
}

.assigned-pax {
    font-size: 15px;
    font-weight: 700;
    color: #16a34a;
}

.manifest-grid td, .manifest-grid th {
    vertical-align: middle;
}

.select2-container {
    width: 100% !important;
}

.select2-selection__choice {
    background: #607D8B !important;
    color: white !important;
    border: none !important;
}

.select2-selection__choice__remove {
    color: white !important;
    margin-right: 6px;
}

.select2-container--default .select2-selection--multiple  {
    min-height: calc(1.3125rem + 1.2rem + 2px);
    padding: 0.6rem 1rem;
    margin-bottom: 15px;
}

.manifest-grid th,
.manifest-grid td {
    word-wrap: break-word;
    white-space: normal;
    vertical-align: middle;
}


.toggle-orders {
    transition: transform 0.3s ease;
}

.toggle-orders.active {
    transform: rotate(-180deg);
}
.main-order-wrapper{
    text-align:left;
    font-size:13px;
    line-height:1.9;
    background-color:#01228b;
    color: #fff;
    padding:10px;
    border-radius:10px;
    position: relative;
}
.order-wrapper {border-top: 1px dotted #f9f9f9;line-height: 3rem;}
.order-wrapper span:first-child {width: 70px; display: inline-block; font-size: 14px;}
.order-wrapper span:nth-child(2) {width: 40px; display: inline-block; font-size: 14px;}
.order-wrapper span:nth-child(3) {width: 65px; display: inline-block; font-size: 14px;}
.order-wrapper span:nth-child(4) {display: inline-block; font-size: 14px;}

.summary-wra {border-bottom: 1px dotted #f9f9f9;line-height: 2rem;}
.summary-wra span:first-child {min-width: 90px; display: inline-block; font-size: 14px;}
.summary-wra span:nth-child(2) {min-width: 115px; display: inline-block; font-size: 14px;}
.summary-wra:last-child {border-bottom: 0;}

.orders-container {
    display: none;
    position: absolute;
    left: -45%;
    top: 80px;
    overflow: visible;
    background: #9C27B0;
    z-index: 11;
    width: 360px;
    border-radius: 8px;
}

.orders-container:hover {
    background: #01228c;
}

.orders-container::before {
    content: "";
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-bottom: 10px solid #9C27B0;
}

.orders-container:hover::before {
    border-bottom-color: #01228c;
}

.toggle-orders .icon {
    transition: transform 0.3s ease;
}

.toggle-orders .icon.active {
    transform: rotate(-180deg);
}
</style>

<div class="card-primary mb-3">
    <div class="card-header order-manifest-head">
        <div class="d-flex justify-content-between align-items-center w-100 mb-manifest">
            <div class="manifest-calendar">
                <div class="d-flex column-gap-10">
                    <button type="button" class="btn btn-sm today-btn " id="today-date">
                        Today
                    </button>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center left-btn" id="prev-week">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <input type="date" name="date" id="filter-date" class="form-control form-control-sm filterDate" 
                            value="{{ $date }}" style="width: 150px;" />
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center right-btn" id="next-week">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <select id="driverFilter" class="form-control driver-filter">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{request()->input('driver_id') == $driver->id ? 'Selected' : ''}}>{{ $driver->name }}</option>
                    @endforeach
                </select>
                <select id="vehicleFilter" class="form-control vehicle-filter">
                    <option value="">All Vehicle</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{request()->input('vehicle_id') == $vehicle->id ? 'Selected' : ''}}>{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>
           

                <a href="{{ route('admin.driver.manifest.export', [
                    'date' => $date,
                    'driver_id' => request('driver_id'),
                    'vehicle_id' => request('vehicle_id')
                ]) }}" 
                class="btn btn-download btn-sm">
                    <i class="bi bi-download"></i> Download Excel
                </a>
        </div>
    </div>
</div>

<div class="card-primary bg-white border rounded-lg-custom">
    <div class="card-body table-responsive p-0" id="tableWrapper">
        <table class="table table-bordered table-sm manifest-grid">
            <thead>
                <tr>
                    <th>Tours</th>
                    @foreach($dateRange as $d)
                    <th class="text-center">
                        {{ $d->format('j-M-Y') }}<br>
                        <small>{{ $d->format('l') }}</small>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $previousReportGroup = null;
                    $tourKeys = array_keys($sortedGrid);
                @endphp
               @forelse($sortedGrid as $tourTitle => $dates)

                    @php
                        $currentIndex = array_search($tourTitle, $tourKeys);

                        $currentGroup = $tourReportGroupMap[$tourTitle] ?? 99;

                        $nextTour = $tourKeys[$currentIndex + 1] ?? null;

                        $nextGroup = $nextTour
                            ? ($tourReportGroupMap[$nextTour] ?? 99)
                            : null;

                        $totalCellOrders = [];
                    @endphp
                    <tr>
                        <td>
                            <p>{!! $tourTitle !!}</p>
                            @if(isset($tourTimes[$tourTitle]))
                                <!-- <br><small class="text-muted">{{ $tourTimes[$tourTitle] }}</small> -->
                            @endif
                        </td>
                        @foreach($dateRange as $d)
                            @php
                                $dateKey = $d->toDateString();

                                $cellOrders = collect($dates[$dateKey] ?? [])
                                    ->filter(function ($o) use ($selectedDriver, $selectedVehicle) {

                                        $driverMatch = !$selectedDriver ||
                                            in_array($selectedDriver, $o['driver_ids'] ?? []);

                                        $vehicleMatch = !$selectedVehicle ||
                                            in_array($selectedVehicle, $o['vehicle_ids'] ?? []);

                                        return $driverMatch && $vehicleMatch;
                                    })
                                    ->values();

                                $totalGuests = collect($cellOrders)->sum('guest_count');
                                $totalCellOrders[] = $cellOrders;
                                $driverNames = collect($cellOrders)
                                    ->flatMap(function ($o) use ($selectedDriver, $selectedVehicle) {

                                        $driverIds = collect($o['driver_ids'] ?? []);
                                        $vehicleIds = collect($o['vehicle_ids'] ?? []);

                                        if ($selectedVehicle && !$vehicleIds->contains($selectedVehicle)) {
                                            return [];
                                        }

                                        if ($selectedDriver) {
                                            return $driverIds->filter(fn($id) => $id == $selectedDriver);
                                        }

                                        return $driverIds;
                                    })
                                    ->unique()
                                    ->map(function ($driverId) use ($driverNameMap) {
                                        return $driverNameMap[$driverId] ?? null;
                                    })
                                    ->filter()
                                    ->implode(', ');


                                $vehicleNames = collect($cellOrders)
                                    ->flatMap(function ($o) use ($selectedVehicle) {

                                        $vehicles = collect($o['vehicle_ids'] ?? []);

                                        if ($selectedVehicle) {
                                            return $vehicles->filter(fn($id) => $id == $selectedVehicle);
                                        }

                                        return $vehicles;
                                    })
                                    ->unique()
                                    ->map(function ($vehicleId) use ($vehicleNameMap) {
                                        return $vehicleNameMap[$vehicleId] ?? null;
                                    })
                                    ->filter()
                                    ->implode(', ');


                            @endphp
                            <td style="cursor: {{ count($cellOrders) ? 'pointer' : 'grab' }};">

                                @if(count($cellOrders))

                                    <div class="main-order-wrapper">                                        

                                        @php
                                        $driverSummary = collect($cellOrders)
                                            ->flatMap(function ($order) use ($selectedDriver, $selectedVehicle) {
                                                $drivers = $order['driver_ids'] ?? [];
                                                $driverNames = $order['driver_names'] ?? [];
                                                $vehicles = $order['vehicle_names'] ?? [];
                                                $guestCount = $order['guest_count'] ?? 0;
                                                if (empty($driverNames)) {
                                                    return [[
                                                        'driver'  => 'NA',
                                                        'vehicle' => 'NA',
                                                        'pax'     => 0,
                                                    ]];
                                                }

                                                $rows = [];



                                                foreach ($drivers as $index => $driverId) {

                                                    // Driver filter
                                                    if ($selectedDriver && $driverId != $selectedDriver) {
                                                        continue;
                                                    }

                                                    // Vehicle filter
                                                    $vehicleId = $order['vehicle_ids'][$index] ?? null;

                                                    if ($selectedVehicle && $vehicleId != $selectedVehicle) {
                                                        continue;
                                                    }

                                                    $driverName = trim($driverNames[$index] ?? '');
                                                    $driverName = ($driverName === '' || strtoupper($driverName) === 'NA')
                                                        ? 'NA'
                                                        : $driverName;

                                                    $vehicleName = trim($vehicles[$index] ?? '');
                                                    $vehicleName = ($vehicleName === '' || strtoupper($vehicleName) === 'NA')
                                                        ? 'NA'
                                                        : $vehicleName;

                                                    $rows[] = [
                                                        'driver'  => $driverName,
                                                        'vehicle' => $vehicleName,
                                                        'pax'     => $guestCount,
                                                    ];
                                                }

                                                return $rows;
                                            })
                                            ->groupBy('driver')
                                            ->map(function ($items, $driver) {

                                                $driverTotal = $items->sum('pax');

                                                $busSummary = $items
                                                    ->groupBy('vehicle')
                                                    ->map(function ($busItems, $bus) {
                                                        return '<i class="fas fa-shuttle-van"></i> '.$bus.' x'.$busItems->sum('pax');
                                                    })
                                                    ->implode(', ');

                                                return '<i class="fas fa-user-tie"></i> '.$driverTotal.' - '.$driver.' ('.$busSummary.')';
                                            });
                                    @endphp

                                    <div class="d-flex align-items-center justify-content-between toggle-orders" style="cursor:pointer;">
                                        <div class="flex align-items-center text-truncate" style="font-size:13px; display: flex; gap: 10px; align-items: center;">
                                            <div><strong>{{ $totalGuests }} </strong></div>
                                            <div>
                                            @if($driverSummary)
                                                {!! collect($cellOrders)
                                                    ->flatMap(function ($order) use ($selectedDriver, $selectedVehicle) {
                                                        $drivers = $order['driver_ids'] ?? [];
                                                        $driverNames = $order['driver_names'] ?? [];
                                                        $vehicles = $order['vehicle_names'] ?? [];
                                                        $guestCount = $order['guest_count'] ?? 0;

                                                        if (empty($drivers)) {
                                                                return [[
                                                                    'driver'  => 'NA',
                                                                    'vehicle' => 'NA',
                                                                    'pax'     => 0,
                                                                ]];
                                                            }

                                                        $rows = [];

                                                        foreach ($drivers as $index => $driverId) {

                                                            if ($selectedDriver && $driverId != $selectedDriver) {
                                                                continue;
                                                            }

                                                            $vehicleId = $vehicleIds[$index] ?? null;

                                                            if ($selectedVehicle && $vehicleId != $selectedVehicle) {
                                                                continue;
                                                            }
                                                            $rows[] = [
                                                                'driver' => $driverNames[$index] ?? 'Unknown',
                                                                'vehicle' => $vehicles[$index] ?? 'NA',
                                                                'pax' => $guestCount,
                                                            ];
                                                        }

                                                        return $rows;
                                                    })
                                                    ->groupBy('driver')
                                                    ->map(function ($items, $driver) {

                                                        $driverTotal = $items->sum('pax');

                                                        $busSummary = $items
                                                            ->groupBy('vehicle')
                                                            ->map(function ($busItems, $bus) {
                                                                return '<span><i class="fas fa-shuttle-van"></i> '.$bus.' x '.$busItems->sum('pax').'</span>';
                                                            })
                                                            ->implode(', ');

                                                        return '<div class="summary-wra"><span><i class="fas fa-user-tie"></i> '.$driverTotal.' - '.$driver.'</span> - '.$busSummary.'</div>';
                                                    })
                                                    ->implode('') !!}
                                            @endif
                                            </div>
                                            <div><i class="fas fa-chevron-down ms-2 icon" style="cursor:pointer;"></i></div>
                                        </div>

                                    </div>

                                        <div class="orders-container mt-2 text-center manifest-cell {{ count($cellOrders) ? 'has-orders' : '' }}"
                                                data-tour="{{ $tourTitle }}"
                                                data-date="{{ $dateKey }}"
                                                data-orders='@json($cellOrders)'
                                                data-assignable="{{ $cellOrders[0]['tour_assignable'] ?? false }}">                                        

                                        @foreach($cellOrders as $order)
                                            @if($order['tour_assignable'] != '1')
                                                @continue
                                            @endif
                                            @php


                                                $driver = !empty($order['driver_names'])
                                                    ? implode(', ', array_unique($order['driver_names']))
                                                    : 'NA';

                                                $vehicle = !empty($order['vehicle_names'])
                                                    ? implode(', ', array_unique($order['vehicle_names']))
                                                    : 'NA';
                                            @endphp

                                            <div class="order-wrapper">                                            
                                            <span class="font-bold">{{ $order['order_number'] }}</span>
                                            -
                                            <span ><i class="fas fa-users"></i> {{ $order['guest_count'] }}</span>
                                            -
                                           <span class="text-success"><i class="fas fa-user-tie"></i>  {{ $driver }}</span>
                                            -
                                            <span class="text-primary"><i class="fas fa-shuttle-van"></i> {{ $vehicle }}</span>
                                            </div>

                                        @endforeach

                                        </div>

                                    </div>

                                @endif


                            </td>
                        @endforeach
                    </tr>

                    @if($nextGroup !== $currentGroup && $currentGroup === 1)

                    <tr style="background:#eef2f7;font-weight:700;">
                        <td>
                            Total {{ report_group_tour_status($currentGroup) }} 
                        </td>

                        @foreach($dateRange as $d)

                            @php
                                $groupTotal = 0;

                                foreach ($sortedGrid as $title => $tourDates) {

                                    if (($tourReportGroupMap[$title] ?? 99) != $currentGroup) {
                                        continue;
                                    }

                                    $orders = collect($tourDates[$d->toDateString()] ?? [])
                                        ->filter(function ($o) use ($selectedDriver, $selectedVehicle) {

                                            $driverMatch = !$selectedDriver ||
                                                in_array($selectedDriver, $o['driver_ids'] ?? []);

                                            $vehicleMatch = !$selectedVehicle ||
                                                in_array($selectedVehicle, $o['vehicle_ids'] ?? []);

                                            return $driverMatch && $vehicleMatch;
                                        });

                                    $groupTotal += $orders->sum('guest_count');
                                }
                            @endphp

                            <td class="text-center">
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <strong>{{ $groupTotal }}</strong>

                                        <button
                                            type="button"
                                            id="pickupMailDropdown"
                                            class="btn btn-warning btn-sm manifest-cell {{ $groupTotal ? 'has-orders' : '' }}"
                                                data-tour="Send Pickup Mail"
                                                data-date="{{ $_COOKIE['manifest_date'] ?? $date }}"
                                                data-orders='@json($cellOrders ?? [])'
                                                data-assignable="1"
                                        >
                                            Send Pickup Mail
                                        </button>

                                        
                                    </div>
                                </div>
                            </td>

                        @endforeach
                    </tr>

                    @endif

                    @empty
                    <tr>
                        <td colspan="{{ count($dateRange) + 1 }}" class="text-center text-muted">
                            No tours found for this week.
                        </td>
                    </tr>
                @endforelse
                    <tr style="background:#f8f9fa; font-weight:600;">
                        <td>Total Pax</td>
                        @foreach($dateRange as $d)
                            <td class="text-center total-pax" data-date="{{ $d->toDateString() }}">
                                {{ $totalPaxPerDay[$d->toDateString()] ?? 0 }}
                            </td>
                        @endforeach
                    </tr>

                   
                    <tr style="font-weight:600;">
                        <td>Assigned Pax</td>
                        @foreach($dateRange as $d)
                            @php $day = $d->toDateString(); @endphp

                            <td class="text-center assigned-pax text-success" data-date="{{ $day }}">

                                <div>
                                    <strong>{{ $assignedPaxPerDay[$day] ?? 0 }}</strong>
                                </div>

                                @if(isset($driverPaxPerDay[$day]))
                                    <div style="margin-top:5px;">
                                        @foreach($driverPaxPerDay[$day] as $driverId => $pax)
                                            <div style="font-size:12px; color:#374151;">
                                                {{ $driverNameMap[$driverId] ?? 'Unknown' }}: 
                                                <strong>{{ $pax }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                            </td>
                        @endforeach
                    </tr>

            </tbody>
        </table>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="driverModal">
    <div class="modal-dialog modal-xl" style="max-width:1000px;">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><strong>Assign Driver</strong> - <span id="modal_tour_title"></span></h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_date">

                <label class="form-label">Orders - <span class="text-muted" id="modal_date_display"></span></label>
                <button type="button"
                        class="btn btn-primary btn-sm"
                        id="bulkAssignBtn">
                    Assign Driver & Vehicle to All Orders
                </button>

                <div id="bulkAssignPanel" class="border rounded p-3 mt-3" style="display:none;">

                    <h6>Bulk Assignment</h6>

                    <div class="row">

                        <div class="col-md-6">
                            <label>Drivers</label>

                            <select
                                id="bulkDriver"
                                class="form-control"
                                multiple
                                data-live-search="true">

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Vehicle</label>

                            <select
                                id="bulkVehicle"
                                class="form-control aiz-selectpicker"
                                data-live-search="true">

                            </select>
                        </div>

                    </div>

                    <div class="mt-3">

                        <button
                            type="button"
                            class="btn btn-success"
                            id="applyBulkAssignment">

                            Apply To All Orders

                        </button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            id="bulkAssignBtnBack">

                            Cancel

                        </button>

                    </div>

                </div>

                <div id="order_list" class="order-list bg-light" style="min-height: auto;max-height: fit-content;overflow: visible;"></div>
                

            </div>
            <div class="modal-footer">
                <div class="dropdown">
                    <button
                        class="btn btn-warning dropdown-toggle"
                        type="button"
                        id="pickupMailDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Send Pickup Mail
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="pickupMailDropdown">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" id="pickupMailDriver">
                                <i class="fas fa-user-tie me-2"></i> To the Driver
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" id="pickupMailPassenger">
                                <i class="fas fa-users me-2"></i> To the Passenger
                            </a>
                        </li>
                    </ul>
                </div>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="assignDriver">Assign</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
window.allDrivers = @json($drivers);
let driversList = @json($drivers);
let vehiclesList = @json($vehicles);
const orderEditRoute = "{{ route('admin.orders.edit', ':id') }}";
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const driverFilter = document.getElementById('driverFilter');
    const exportBtn = document.querySelector('.btn-download');
    const dateInput = document.getElementById('filter-date');
    let vehicleFilter = document.getElementById('vehicleFilter');

    // ====================================
    // Calendar
    // ====================================
    if (dateInput) {let me 

        dateInput.addEventListener('click', function () {
            if (this.showPicker) {
                this.showPicker();
            }
        });

        dateInput.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('date', this.value);
            window.location.href = url.toString();
        });

    }

    // ====================================
    // Today
    // ====================================
    $('#today-date').on('click', function () {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        window.location.href = '?date=' + `${yyyy}-${mm}-${dd}`;
    });

    // ====================================
    // Previous Week
    // ====================================

    $('#prev-week').on('click', function () {
        let d = new Date(dateInput.value);
        d.setDate(d.getDate() - 6);
        window.location.href = '?date=' + d.toISOString().split('T')[0];
    });

    // ====================================
    // Next Week
    // ====================================

    $('#next-week').on('click', function () {
        let d = new Date(dateInput.value);
        d.setDate(d.getDate() + 5);
        window.location.href = '?date=' + d.toISOString().split('T')[0];
    });

    // ====================================
    // Export URL
    // ====================================

    function updateExportUrl() {

        let selectedDriver  = driverFilter.value;
        let selectedVehicle = vehicleFilter.value;
        let date            = dateInput.value;

        let url = `?date=${date}`;

        if (selectedDriver) {
            url += `&driver_id=${selectedDriver}`;
        }

        if (selectedVehicle) {
            url += `&vehicle_id=${selectedVehicle}`;
        }

        exportBtn.href = "{{ route('admin.driver.manifest.export') }}" + url;
    }

    updateExportUrl();

    // ====================================
    // Driver Filter
    // ====================================

    driverFilter.addEventListener('change', function () {

        const url = new URL(window.location.href);

        url.searchParams.set('date', dateInput.value);

        if (this.value) {

            url.searchParams.set(
                'driver_id',
                this.value
            );

        } else {

            url.searchParams.delete('driver_id');

        }

        window.location.href = url.toString();

    });

    function applyFilters() {

        let date = dateInput.value;

        let driver  = driverFilter.value;
        let vehicle = vehicleFilter.value;

        let url = `?date=${date}`;

        if (driver) {
            url += `&driver_id=${driver}`;
        }

        if (vehicle) {
            url += `&vehicle_id=${vehicle}`;
        }

        window.location.href = url;
    }

    driverFilter.addEventListener('change', applyFilters);
    vehicleFilter.addEventListener('change', applyFilters);

    // ====================================
    // Cell Click
    // ====================================

    document.querySelectorAll('.manifest-cell.has-orders').forEach(function (cell) {

        cell.addEventListener('click', function () {

            $('#bulkAssignPanel').hide();
            $('#bulkAssignBtn').text('Assign Driver & Vehicle to All Orders');

            const assignable = this.dataset.assignable === '1';
            const orders = JSON.parse(this.dataset.orders);
            const date = this.dataset.date;

            $('#modal_date').val(date);
            $('#modal_tour_title').text(this.dataset.tour);
            $('#modal_date_display').text(date);

            const container = document.getElementById('order_list');
            container.innerHTML = '';

            let driversHtml = '';
            driversList.forEach(function (driver) {
                driversHtml +=`<option value="${driver.id}">${driver.name}</option>`;
            });

            let vehiclesHtml = `<option value="">Select Vehicle</option>`;
            vehiclesList.forEach(function (vehicle) {
                vehiclesHtml += `<option value="${vehicle.id}">${vehicle.name}</option>`;
            });

            // Render orders starts here...


            // ====================================
            // Render Orders
            // ====================================

            orders.forEach(function (o) {

                let orderUrl = orderEditRoute.replace(
                    ':id',
                    o.order_encrypt_id
                );

                container.innerHTML += `

                <div class="order-content mb-2 p-2 border rounded"
                data-assignment-type="${o.assignment_type}">

                <div class="row align-items-center">

                    <!-- Order Details -->
                    <div class="col-md-2">

                        <input
                            type="checkbox"
                            class="order-checkbox"
                            value="${o.order_id}" style="width:20px; height:20px;"
                            checked>

                        <a href="${orderUrl}" target="_blank">
                            <strong>#${o.order_number}</strong>
                        </a>

                        <br>

                        <small class="text-muted">${o.customer ?? ''}</small>

                        <br>

                        <small><i class="fas fa-users"></i> ${o.guest_count} Pax            

                        <span
                            class="ml-2 order-info-btn" style="cursor:pointer;"
                            data-order='${JSON.stringify(o)}'>
                            <i class="bi bi-info-circle"></i> Info
                        </span>
                    </small>
                    </div>

                    <!-- Pickup Time -->
                    <div class="col-md-3">

                        <label class="small text-muted mb-1">
                            Pickup Time
                        </label>

                        <input
                            type="time" step="300"
                            class="form-control order-pickup-time mb-3"
                            value="${o.pickup_time ? o.pickup_time.substring(0,5) : ''}"
                            data-order-id="${o.order_id}">

                    </div>

                    <!-- Driver -->
                    <div class="col-md-4">

                        <label class="small text-muted mb-1">
                            Driver
                        </label>

                        <select
                            class="form-control order-driver-select "
                            multiple
                            data-order-id="${o.order_id}">

                            ${driversHtml}

                        </select>

                    </div>

                    <!-- Vehicle -->
                    <div class="col-md-3">

                        <label class="small text-muted mb-1">
                            Vehicle
                        </label>

                        <select
                            class="form-control aiz-selectpicker order-vehicle-select mb-3"
                            data-live-search="true"
                            data-order-id="${o.order_id}">

                            ${vehiclesHtml}

                        </select>

                    </div>

                </div>

            </div>

                `;

            });


            // ====================================
            // Initialise Select2 (Drivers)
            // ====================================

            $('.order-driver-select').select2({

                width: '100%',

                placeholder: 'Select Driver',

                closeOnSelect: false

            });


            // ====================================
            // Initialise Vehicle Picker
            // ====================================

            $('.order-vehicle-select').selectpicker('refresh');
            loadBulkDropdowns();


            // ====================================
            // Preselect Existing Drivers
            // ====================================

            orders.forEach(function (o) {

                let driverSelect = document.querySelector(

                    `.order-driver-select[data-order-id="${o.order_id}"]`

                );

                if (!driverSelect) {

                    return;

                }

                let drivers = (o.driver_ids || []).map(String);

                $(driverSelect)

                    .val(drivers)

                    .trigger('change');

            });


            // ====================================
            // Preselect Existing Vehicles
            // ====================================

            orders.forEach(function (o) {

                let vehicleSelect = document.querySelector(

                    `.order-vehicle-select[data-order-id="${o.order_id}"]`

                );

                if (!vehicleSelect) {

                    return;

                }

                let vehicles = (o.vehicle_ids || []).map(String);

                $(vehicleSelect)

                    .selectpicker('val', vehicles)

                    .selectpicker('refresh');

            });


            // ====================================
            // Open Modal
            // ====================================

            new bootstrap.Modal( document.getElementById('driverModal') ).show();


            // ====================================
            // Enable / Disable
            // ====================================

            if (!assignable) {

                $('.order-driver-select')
                    .prop('disabled', true)
                    .trigger('change');

                $('.order-vehicle-select')
                    .prop('disabled', true)
                    .selectpicker('refresh');

                $('#assignDriver').prop('disabled', true);
                $('#bulkAssignBtn').hide();
                $('#bulkAssignPanel').hide();

                Swal.fire({
                    icon: 'warning',
                    title: 'Driver Assignment Disabled',
                    text: 'This tour is not assignable.'
                });

            } else {

                $('.order-driver-select')
                    .prop('disabled', false)
                    .trigger('change');

                $('.order-vehicle-select')
                    .prop('disabled', false)
                    .selectpicker('refresh');

                $('#assignDriver').prop('disabled', false);
                $('#bulkAssignBtn').show();
            }
        });
    });


    // ========================================
    // BULK ASSIGN PANEL
    // ========================================

    $('#bulkAssignBtn').on('click', function () {
        $(this).hide();
        $('#bulkAssignPanel').stop(true, true).slideDown(200);
    });

    $('#bulkAssignBtnBack').on('click', function () {
        $('#bulkAssignPanel').stop(true, true).slideUp(200, function () {
            $('#bulkAssignBtn').show();
        });
    });


    // ========================================
    // LOAD BULK DROPDOWNS
    // ========================================

    function loadBulkDropdowns() {

        let driverHtml = '';

        window.allDrivers.forEach(function(driver) {
            driverHtml += `
                <option value="${driver.id}">
                    ${driver.name}
                </option>`;
        });

        $('#bulkDriver').html(driverHtml);

        let vehicleHtml = `
            <option value="">
                Select Vehicle
            </option>`;

        vehiclesList.forEach(function(vehicle){
            vehicleHtml += `
                <option value="${vehicle.id}">
                    ${vehicle.name}
                </option>
            `;
        });

        $('#bulkVehicle').html(vehicleHtml);

        // Destroy if already initialized
        if ($('#bulkDriver').hasClass('select2-hidden-accessible')) {
            $('#bulkDriver').select2('destroy');
        }

        $('#bulkDriver').select2({
            width: '100%',
            placeholder: 'Select Drivers',
            closeOnSelect: false
        });

        $('#bulkVehicle').selectpicker('destroy');
        $('#bulkVehicle').selectpicker();
    }


    // ========================================
    // APPLY BULK ASSIGNMENT
    // ========================================

    $('#applyBulkAssignment').on('click', function () {

        let drivers = $('#bulkDriver').val() || [];
        let vehicle = $('#bulkVehicle').val();

        // Apply Driver
        $('.order-driver-select').each(function(){
            $(this)
                .val(drivers)
                .trigger('change');
        });

        // Apply Vehicle
        $('.order-vehicle-select').each(function() {
            $(this)
                .selectpicker('val', vehicle)
                .selectpicker('refresh');
        });
    });

    // =========================
    // ASSIGN DRIVER (FIXED)
    // =========================
   // ========================================
    // ASSIGN DRIVER
    // ========================================

    $('#assignDriver').on('click', async function () {

        const btn = this;
        const date = $('#modal_date').val();
        let pickup_time;
        let ordersPayload = [];

        $('.order-content').each(function () {
            const row = $(this);
            const orderId = parseInt(
                row.find('.order-checkbox').val()
            );

            // ----------------------------
            // Driver IDs
            // ----------------------------

            let driverIds = row.find('.order-driver-select').val() || [];
            driverIds = driverIds.map(Number);
            pickup_time = row.find('.order-pickup-time').val();

            // ----------------------------
            // Vehicle IDs
            // ----------------------------

            let vehicleIds = [];
            let vehicleSelect = row.find('.order-vehicle-select');
            let vehicleValue = vehicleSelect.selectpicker('val');
            if (vehicleValue) {
                if (Array.isArray(vehicleValue)) {
                    vehicleIds = vehicleValue.map(Number);
                } else {
                    vehicleIds = [Number(vehicleValue)];
                }
            }

            console.log({
                order: orderId,
                drivers: driverIds,
                vehicles: vehicleIds
            });

            ordersPayload.push({
                order_id: orderId,
                driver_ids: driverIds,
                vehicle_ids: vehicleIds,
                pickup_time: pickup_time,
                assignment_type: row.data('assignment-type')
            });
        });

        console.log("Sending Payload", ordersPayload);

        try {

            btn.disabled = true;
            btn.innerText = "Saving...";
            const response = await fetch(
                "{{ route('admin.assign.driver') }}",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        date: date,
                        orders: ordersPayload
                    })
                }
            );

            const result = await response.json();
            console.log(result);

            if (!response.ok) {
                throw result;
            }

            Swal.fire({
                icon: 'success',
                title: 'Saved',
                timer: 1000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });

        } catch (e) {

            console.error(e);
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: 'Unable to assign driver.'
            });

        } finally {

            btn.disabled = false;
            btn.innerText = "Assign";

        }

    });

    // ========================================
    // SEND MAIL TO DRIVER OR PASSENGER
    // ========================================    

    $(document).on('click', '#pickupMailDriver', async function () {

        let checkedOrders = [];

        $('.order-content').each(function () {

            const row = $(this);
            const checkbox = row.find('.order-checkbox');

            if (!checkbox.is(':checked')) {
                return;
            }

            const orderId = parseInt(checkbox.val());

            const orderNumber = row
                .find('a strong')
                .text()
                .replace('#', '')
                .trim();

            const pickupTime = row
                .find('.order-pickup-time')
                .val();

            const driverIds = row
                .find('.order-driver-select')
                .val() || [];

            const vehicleId = row
                .find('.order-vehicle-select')
                .val() || null;

            checkedOrders.push({
                order_id: orderId,
                order_number: orderNumber,
                pickup_time: pickupTime,
                driver_ids: driverIds.map(Number),
                vehicle_id: vehicleId ? Number(vehicleId) : null
            });
        });

        if (!checkedOrders.length) {
            Swal.fire({
                icon: 'warning',
                title: 'No Orders Selected',
                text: 'Please select at least one order.'
            });

            return;
        }

        const ordersList = checkedOrders.map(order => {
            return `<div>Order #${order.order_number}</div>`;
        }).join('');

        const confirmResult = await Swal.fire({
            title: 'Send Pickup Mail to Driver',
            html: `
                <div class="text-start">
                    <p>
                        Please make sure the correct orders and drivers are
                        selected before sending the pickup mail.
                    </p>

                    <div class="border rounded p-2 mt-2">
                        <strong>Selected Orders:</strong>
                        ${ordersList}
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Send Now',
            cancelButtonText: 'Cancel'
        });

        if (!confirmResult.isConfirmed) {
            return;
        }

        Swal.fire({
            title: 'Sending Driver Pickup Mail',
            html: `
                <div class="text-start">
                    <p id="driverMailStatus">
                        Preparing selected orders...
                    </p>

                    <div class="progress" style="height:20px;">
                        <div
                            id="driverMailProgress"
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            style="width:10%">
                            10%
                        </div>
                    </div>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        try {

            $('#driverMailStatus').html(
                `Sending ${checkedOrders.length} selected orders...`
            );

            $('#driverMailProgress')
                .css('width', '50%')
                .text('50%');

            const response = await fetch(
                "{{ route('admin.driver.pickup.mail') }}",
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        date: $('#modal_date').val(),
                        orders: checkedOrders
                    })
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Unable to send driver pickup mail.'
                );
            }

            $('#driverMailProgress')
                .css('width', '100%')
                .text('100%');

            await new Promise(resolve => setTimeout(resolve, 400));

            const sentDrivers = (result.sent || []).map(item => `
                <div class="text-success mb-1">
                    <i class="fas fa-check-circle"></i>
                    ${item.driver_name} — ${item.email}
                    (${item.orders_count} orders)
                </div>
            `).join('');

            const failedDrivers = (result.failed || []).map(item => `
                <div class="text-danger mb-1">
                    <i class="fas fa-times-circle"></i>
                    ${item.driver_name || 'Unknown Driver'}
                    — ${item.message}
                </div>
            `).join('');

            Swal.fire({
                icon: result.failed_count > 0 ? 'warning' : 'success',
                title: 'Driver Pickup Mail Completed',
                html: `
                    <div class="text-start">
                        <p>
                            <strong>Sent:</strong> ${result.sent_count}
                            &nbsp; | &nbsp;
                            <strong>Failed:</strong> ${result.failed_count}
                        </p>

                        ${sentDrivers}

                        ${failedDrivers}
                    </div>
                `,
                confirmButtonText: 'OK'
            });

        } catch (error) {

            Swal.fire({
                icon: 'error',
                title: 'Mail Failed',
                text: error.message || 'Unable to send driver pickup mail.'
            });
        }
    });

    $(document).on('click', '#pickupMailPassenger', async function () {

        Swal.fire({
            title: 'Send Pickup Mail to the Passenger',
            text: 'Please make sure to select the correct recipient before sending the pickup mail.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Send Now',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {

            if (!result.isConfirmed) return;

            const date = $('#modal_date').val();

            let orders = [];

            $('.order-content').each(function () {

                let checkbox = $(this).find('.order-checkbox');

                if (!checkbox.is(':checked')) {
                    return;
                }

                orders.push({
                    order_id: parseInt(checkbox.val()),
                    order_number: $(this).find('a strong').text().replace('#', '')
                });
            });

            if (!orders.length) {

                Swal.fire(
                    'No Orders',
                    'No orders found to send mail.',
                    'warning'
                );

                return;
            }

            let html = `
                <div class="text-start">
                    <p><strong>Total Orders:</strong> ${orders.length}</p>
                    <div id="mailProgressList"></div>
                </div>
            `;

            Swal.fire({
                title: 'Sending Passenger Pickup Mail',
                html: html,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: async () => {

                    let sent = 0;
                    let failed = 0;

                    for (let i = 0; i < orders.length; i++) {

                        let order = orders[i];
                        let orderId = order.order_id;
                        let orderNumber = order.order_number;

                        $('#mailProgressList').append(`
                            <div id="mail-row-${orderId}">
                                ⏳ Order #${orderNumber} - Sending...
                            </div>
                        `);

                        try {

                            const response = await fetch(
                                "{{ route('admin.passenger.pickup.mail') }}",
                                {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({
                                        date: date,
                                        order_id: orderId
                                    })
                                }
                            );

                            const result = await response.json();

                            if (result.success) {

                                sent++;

                                $(`#mail-row-${orderId}`).html(`
                                    ✅ Order #${orderNumber} - Sent
                                `);

                            } else {

                                failed++;

                                $(`#mail-row-${orderId}`).html(`
                                    ❌ Order #${orderNumber}
                                    - Request failed
                                `);
                            }

                        } catch (error) {

                            failed++;

                            $(`#mail-row-${orderId}`).html(`
                                ❌ Order ID ${orderId}
                                - Request failed
                            `);
                        }

                        // Slow down sending
                        await new Promise(resolve => setTimeout(resolve, 1000));
                    }

                    Swal.fire({
                        icon: failed > 0 ? 'warning' : 'success',
                        title: 'Pickup Mail Completed',
                        html: `
                            <p><strong>Sent:</strong> ${sent}</p>
                            <p><strong>Failed:</strong> ${failed}</p>
                        `,
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });

    // =========================
    // DRIVER FILTER
    // =========================
    driverFilter.addEventListener('change', function () {

        let selectedDriver = this.value;
        let date = dateInput.value;
        let url = `?date=${date}`;

        if (selectedDriver) {
            url += `&driver_id=${selectedDriver}`;
        }

        window.location.href = url;
    });

    updateExportUrl();

    $(document).on('click', '.toggle-orders', function (e) {
        e.stopPropagation();

        let wrapper = $(this).closest('.main-order-wrapper');
        let currentContainer = wrapper.find('.orders-container');
        let icon = $(this).find('.icon');

        $('.orders-container').not(currentContainer).slideUp(300);
        $('.toggle-orders .icon').not(icon).removeClass('active');

        currentContainer.slideToggle(300);
        icon.toggleClass('active');
    });

    $(document).on('click', function () {
        $('.orders-container').slideUp(300);
        $('.toggle-orders .icon').removeClass('active');
    });

    $(document).on('click', '.orders-container', function (e) {
        e.stopPropagation();
    });
});

</script>
<script>
    /*document.getElementById('today-date').addEventListener('click', function() {
        let dateInput = document.getElementById('filter-date'); // ✅ define it here

        let today = new Date();
        let yyyy = today.getFullYear();
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let dd = String(today.getDate()).padStart(2, '0');

        let formatted = `${yyyy}-${mm}-${dd}`;

        // set value
        dateInput.value = formatted;

        // redirect like your other filters
        window.location.href = "?date=" + formatted;
    });*/
    
    $(document).on('click', '.order-info-btn', function () {

        const o = $(this).data('order');

        Swal.fire({

            title: 'Order Details',

            width: 700,

            html: `
                <table class="table table-bordered table-sm text-start mb-0 text-left" style="width:auto">

                    <tr>
                        <th style="width: 150px;position: relative;min-width: auto !important;">Order No</th>
                        <td>${o.order_number}</td>
                    </tr>

                    <tr>
                        <th style="width: 150px;position: relative;min-width: auto !important;">Customer</th>
                        <td>${o.customer ?? '-'}</td>
                    </tr>                    

                    <tr>
                        <th style="width: 150px;position: relative;min-width: auto !important;">Pickup Location</th>
                        <td>${o.pickup_location || '-'}</td>
                    </tr>

                    <tr>
                        <th style="width: 150px;position: relative;min-width: auto !important;">Instructions</th>
                        <td>${o.instruction || '-'}</td>
                    </tr>

                    <tr>
                        <th style="width: 150px;position: relative;min-width: auto !important;">Internal Notes</th>
                        <td>${o.internal_notes || '-'}</td>
                    </tr>

                </table>
            `,

            confirmButtonText: 'Close'

        });

    });
</script>
<script>
    const tableWrapper = document.getElementById("tableWrapper");

    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let scrollLeft = 0;
    let scrollTop = 0;

    tableWrapper.addEventListener("mousedown", function (e) {
      isDragging = true;
      tableWrapper.classList.add("active");

      startX = e.pageX - tableWrapper.offsetLeft;
      startY = e.pageY - tableWrapper.offsetTop;

      scrollLeft = tableWrapper.scrollLeft;
      scrollTop = tableWrapper.scrollTop;
    });

    tableWrapper.addEventListener("mouseleave", stopDragging);
    tableWrapper.addEventListener("mouseup", stopDragging);

    function stopDragging() {
      isDragging = false;
      tableWrapper.classList.remove("active");
    }

    tableWrapper.addEventListener("mousemove", function (e) {
      if (!isDragging) return;

      e.preventDefault();

      const x = e.pageX - tableWrapper.offsetLeft;
      const y = e.pageY - tableWrapper.offsetTop;

      tableWrapper.scrollLeft = scrollLeft - (x - startX);
      tableWrapper.scrollTop = scrollTop - (y - startY);
    });
  </script>
@endsection
</x-admin>