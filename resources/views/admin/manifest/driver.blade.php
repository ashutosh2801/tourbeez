<x-admin>
@section('title', 'Driver Manifest')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
    
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
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }

    .manifest-grid td p {
        margin: 0;
    }

    .manifest-grid small {
        font-size: 12px;
        font-weight: 500;
    }

    .manifest-cell.has-orders:hover {
        background-color: #eef6ff;
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

    .manifest-cell.has-orders:hover {
        background-color: #f0f7ff;
    }

    .select2-container {
    width: 100% !important;
}

/*.select2-selection--multiple {
    min-height: 45px !important;
    border: 1px solid #ced4da !important;
    border-radius: .375rem !important;
}*/

.select2-selection__choice {
    background: #607D8B !important;
    color: white !important;
    border: none !important;
}

.select2-selection__choice__remove {
    color: white !important;
    margin-right: 6px;
}
/*.selection .select2-selection .select2-selection--multiple {
    min-height: calc(1.3125rem + 1.2rem + 2px) !important;
    padding: 0.6rem 1rem !important;
    margin-bottom: 15px !important;
}*/

.select2-container--default .select2-selection--multiple  {
    min-height: calc(1.3125rem + 1.2rem + 2px);
    padding: 0.6rem 1rem;
    margin-bottom: 15px;
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
            <!-- <a href="{{ route('admin.driver.manifest.export', ['date' => $date]) }}" 
                   class="btn btn-success btn-sm">
                    Export Excel
                </a> -->

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
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm manifest-grid">
            <thead>
                <tr>
                    <th style="min-width: 200px;">Tours</th>
                    @foreach($dateRange as $d)
                        <th class="text-center" style="min-width: 120px;">
                            {{ $d->format('j-M-Y') }}<br>
                            <small>{{ $d->format('l') }}</small>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($sortedGrid as $tourTitle => $dates)
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
                                //$cellOrders = $dates[$dateKey] ?? [];
                                // $cellOrders = collect($dates[$dateKey] ?? [])
                               // ->filter(function ($o) use ($selectedDriver) {

                               //     if (!$selectedDriver) return true;

                               //     return in_array($selectedDriver, $o['driver_ids'] ?? []);
                              //  })
                              //  ->values(); 

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
                                //$driverNames = collect($cellOrders)
                                //    ->pluck('driver_names')   // array of //arrays
                                 //   ->flatten()
                                 //   ->filter()
                                 //   ->unique()
                                 //   ->implode(', ');

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
                            <td class="text-center manifest-cell {{ count($cellOrders) ? 'has-orders' : '' }}"
                                data-tour="{{ $tourTitle }}"
                                data-date="{{ $dateKey }}"
                                data-orders='@json($cellOrders)'
                                data-assignable="{{ $cellOrders[0]['tour_assignable'] ?? false }}"
                                style="cursor: {{ count($cellOrders) ? 'pointer' : 'default' }};">
                                

                                @if(count($cellOrders))

                                    <strong>Total - {{ $totalGuests }}</strong>

                                    @php
                                        $summary = [];

                                        foreach ($cellOrders as $order) {

                                            foreach (($order['driver_ids'] ?? []) as $index => $driverId) {

                                                $driver = $driverNameMap[$driverId] ?? 'Unknown';
                                                $vehicleId = $order['vehicle_ids'][$index] ?? null;
                                                $vehicle = $vehicleNameMap[$vehicleId] ?? '';

                                                if (!isset($summary[$driver])) {
                                                    $summary[$driver] = [
                                                        'pax' => 0,
                                                        'vehicles' => []
                                                    ];
                                                }

                                                $summary[$driver]['pax'] += $order['guest_count'];

                                                if ($vehicle) {
                                                    $summary[$driver]['vehicles'][$vehicle] = true;
                                                }
                                            }
                                        }
                                    @endphp

                                    @foreach($summary as $driver => $info)
                                        <br>
                                        <small class="text-success">
                                            {{ $info['pax'] }} - {{ $driver }}
                                            @if(count($info['vehicles']))
                                                <span class="text-primary">({{ implode(', ', array_keys($info['vehicles'])) }})</span>
                                            @endif
                                        </small>
                                    @endforeach

                                @endif



                            </td>
                        @endforeach
                    </tr>
                    <tr style="background:#f8f9fa; font-weight:600;">

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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Driver</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_date">

                <div class="mb-3">
                    <label class="form-label"><strong id="modal_tour_title"></strong></label>
                    <div class="text-muted" id="modal_date_display"></div>
                </div>                

                <label class="form-label">Orders</label>
                <div id="order_list" class="order-list bg-light" style="min-height: 300px;"></div>

                <!-- <div class="mt-2">
                    <label>
                        <input type="checkbox" id="select_all_orders" checked> Apply to all orders
                    </label>
                </div> -->


                <div class="mt-3">
                    <button type="button"
                            class="btn btn-primary btn-sm"
                            id="bulkAssignBtn">
                        Assign Driver & Vehicle to All Orders
                    </button>
                </div>

<div id="bulkAssignPanel"
     class="border rounded p-3 mt-3"
     style="display:none;">

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
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-danger" id="removeDriver">
                    Remove Driver
                </button> -->
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

    if (dateInput) {

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

        window.location.href =
            '?date=' + d.toISOString().split('T')[0];

    });

    // ====================================
    // Next Week
    // ====================================

    $('#next-week').on('click', function () {

        let d = new Date(dateInput.value);

        d.setDate(d.getDate() + 6);

        window.location.href =
            '?date=' + d.toISOString().split('T')[0];

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

        exportBtn.href =
            "{{ route('admin.driver.manifest.export') }}" + url;
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

            $('#bulkAssignBtn')
                .text('Assign Driver & Vehicle to All Orders');

            const assignable =
                this.dataset.assignable === '1';

            const orders =
                JSON.parse(this.dataset.orders);

            const date =
                this.dataset.date;

            $('#modal_date').val(date);

            $('#modal_tour_title').text(this.dataset.tour);

            $('#modal_date_display').text(date);

            const container =
                document.getElementById('order_list');

            container.innerHTML = '';

            let driversHtml = '';

            driversList.forEach(function (driver) {

                driversHtml +=
                    `<option value="${driver.id}">
                        ${driver.name}
                    </option>`;

            });

            let vehiclesHtml =
                `<option value="">
                    Select Vehicle
                </option>`;

            vehiclesList.forEach(function (vehicle) {

                vehiclesHtml +=
                    `<option value="${vehicle.id}">
                        ${vehicle.name}
                    </option>`;

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

        <div class="d-flex justify-content-between align-items-start">

            <div style="width:28%;">

                <input
                    type="checkbox"
                    class="order-checkbox"
                    value="${o.order_id}"
                    checked>

                <a href="${orderUrl}"
                   target="_blank">

                    #${o.order_number}

                </a>

                <br>

                <small>${o.customer ?? ''}</small>

                <br>

                <small>👥 ${o.guest_count}</small>

            </div>

            <div style="width:34%;">

                <select
                    class="form-control order-driver-select"
                    multiple
                    data-order-id="${o.order_id}">

                    ${driversHtml}

                </select>

            </div>

            <div style="width:34%;">

                <select
                    class="form-control aiz-selectpicker order-vehicle-select"
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

new bootstrap.Modal(

    document.getElementById('driverModal')

).show();


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

    window.allDrivers.forEach(function(driver){

        driverHtml += `
            <option value="${driver.id}">
                ${driver.name}
            </option>
        `;

    });

    $('#bulkDriver').html(driverHtml);



    let vehicleHtml = `
        <option value="">
            Select Vehicle
        </option>
    `;

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

    $('.order-vehicle-select').each(function(){

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

    let ordersPayload = [];

    $('.order-content').each(function () {

        const row = $(this);

        const orderId = parseInt(
            row.find('.order-checkbox').val()
        );

        // ----------------------------
        // Driver IDs
        // ----------------------------

        let driverIds =
            row.find('.order-driver-select').val() || [];

        driverIds = driverIds.map(Number);

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
});
</script>
<script>
    document.getElementById('today-date').addEventListener('click', function() {
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
    });
</script>
@endsection
</x-admin>