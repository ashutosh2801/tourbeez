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
                    'driver_id' => request('driver_id')
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
                            <p>{{ $tourTitle }}</p>
                            @if(isset($tourTimes[$tourTitle]))
                                <!-- <br><small class="text-muted">{{ $tourTimes[$tourTitle] }}</small> -->
                            @endif
                        </td>
                        @foreach($dateRange as $d)
                            @php
                                $dateKey = $d->toDateString();
                                //$cellOrders = $dates[$dateKey] ?? [];
                                $cellOrders = collect($dates[$dateKey] ?? [])
                                ->filter(function ($o) use ($selectedDriver) {

                                    if (!$selectedDriver) return true;

                                    return in_array($selectedDriver, $o['driver_ids'] ?? []);
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
                                        ->flatMap(function ($o) use ($selectedDriver) {

                                            // no filter → show all
                                            if (!$selectedDriver) {
                                                return $o['driver_ids'] ?? [];
                                            }

                                            // filter → only matching driver
                                            return collect($o['driver_ids'] ?? [])
                                                ->filter(fn ($id) => $id == $selectedDriver);
                                        })
                                        ->unique()
                                        ->map(function ($driverId) use ($driverNameMap) {
                                            return $driverNameMap[$driverId] ?? null;
                                        })
                                        ->filter()
                                        ->implode(', ');


                                    $vehicleNames = collect($cellOrders)
                                    ->flatMap(function ($o) {
                                        return $o['vehicle_ids'] ?? [];
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
                                    <strong>{{ $totalGuests }}</strong>
                                    @if($driverNames)
                                        <br><small class="text-success">{{ $driverNames }}</small>
                                    @else
                                        <!-- <br><small class="text-danger">No Driver</small> -->
                                    @endif

                                    @if($vehicleNames)
                                        <br><small class="text-primary">{{ $vehicleNames }}</small>
                                    @endif
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
                class="form-control aiz-selectpicker"
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
<script>
window.allDrivers = @json($drivers);
let driversList = @json($drivers);
let vehiclesList = @json($vehicles);
const orderEditRoute = "{{ route('admin.orders.edit', ':id') }}";
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    let driverFilter = document.getElementById('driverFilter');
    let exportBtn = document.querySelector('.btn-download');
    let dateInput = document.getElementById('filter-date');

    // =========================
    // OPEN CALENDAR
    // =========================
    dateInput.addEventListener('click', function() {
        if (this.showPicker) this.showPicker();
    });

    // =========================
    // TODAY BUTTON
    // =========================
    document.getElementById('today-date').addEventListener('click', function() {
        let today = new Date();
        let formatted = today.toISOString().split('T')[0];
        window.location.href = "?date=" + formatted;
    });

    // =========================
    // EXPORT URL
    // =========================
    function updateExportUrl() {
        let selectedDriver = driverFilter.value;
        let date = dateInput.value;

        let url = `?date=${date}`;
        if (selectedDriver) url += `&driver_id=${selectedDriver}`;

        exportBtn.href = "{{ route('admin.driver.manifest.export') }}" + url;
    }

    // =========================
    // WEEK NAVIGATION
    // =========================
    document.getElementById('prev-week').onclick = function() {
        let d = new Date(dateInput.value);
        d.setDate(d.getDate() - 6);
        window.location.href = "?date=" + d.toISOString().split('T')[0];
    };

    document.getElementById('next-week').onclick = function() {
        let d = new Date(dateInput.value);
        d.setDate(d.getDate() + 6);
        window.location.href = "?date=" + d.toISOString().split('T')[0];
    };

    dateInput.onchange = function() {
        window.location.href = "?date=" + this.value;
    };

    // =========================
    // CELL CLICK → MODAL
    // =========================
    document.querySelectorAll('.manifest-cell.has-orders').forEach(cell => {

        cell.addEventListener('click', function() {

            

            $('#bulkAssignPanel').hide();

            $('#bulkAssignBtn').text('Assign Driver & Vehicle to All Orders');

            const assignable = this.dataset.assignable === '1';


            // if (this.dataset.assignable !== '1') {
            //     Swal.fire('Not Allowed', 'Driver cannot be assigned', 'warning');
            //     return;
            // }

            let orders = JSON.parse(this.dataset.orders);
            let date = this.dataset.date;

            document.getElementById('modal_date').value = date;
            document.getElementById('modal_tour_title').innerText = this.dataset.tour;
            document.getElementById('modal_date_display').innerText = date;

            let container = document.getElementById('order_list');
            container.innerHTML = '';

            // build driver options
            let driversHtml = '<option value="">Select Drivers</option>';
            driversList.forEach(d => {
                driversHtml += `<option value="${d.id}">${d.name}</option>`;
            });

            let vehiclesHtml = '<option value="">Select Vehicle</option>';
            vehiclesList.forEach(d => {
                vehiclesHtml += `<option value="${d.id}">${d.name}</option>`;
            });



            // render orders
            orders.forEach(o => {
                let orderUrl = orderEditRoute.replace(':id', o.order_encrypt_id);
                container.innerHTML += `
                <div class="order-content mb-2 p-2 border rounded" data-assignment-type="${o.assignment_type}">
                    <div class="d-flex justify-content-between">

                        <div style="width:30%">
                            <input type="checkbox" class="order-checkbox" value="${o.order_id}" checked>
                            <a href="${orderUrl}" class="alink" target="_blank">
                                #${o.order_number}
                            </a><br>
                            <small>${o.customer || ''}</small><br>
                            <small>👥 ${o.guest_count}</small>
                        </div>

                        <div style="width:32%">
                            <select 
                                class="form-control aiz-selectpicker order-driver-select"
                                multiple
                                data-live-search="true"
                                data-order-id="${o.order_id}">
                                ${driversHtml}
                            </select>
                        </div>

                        <div style="width:35%; margin-left:2%">
                            <select 
                                class="form-control aiz-selectpicker order-vehicle-select"
                                
                                data-live-search="true"
                                data-order-id="${o.order_id}">
                                ${vehiclesHtml}
                            </select>
                        </div>

                    </div>
                </div>`;
            });

            // $('#bulkDriver').html(driversHtml);

            // $('#bulkVehicle').html(vehiclesHtml);

            // TB.plugins.bootstrapSelect();

            // ✅ INIT AIZ SELECT PROPERLY
            TB.plugins.bootstrapSelect();
            loadBulkDropdowns();

            // ✅ PRESELECT EXISTING DRIVERS
            orders.forEach(o => {
                let driverSelect = document.querySelector(
                    `.order-driver-select[data-order-id="${o.order_id}"]`
                );

                if (!driverSelect) return;

                let selectedDrivers = (o.driver_ids || []).map(String);

                $(driverSelect).selectpicker('val', selectedDrivers);
                $(driverSelect).selectpicker('refresh');
            });

            orders.forEach(o => {
                let vehicleSelect = document.querySelector(
                    `.order-vehicle-select[data-order-id="${o.order_id}"]`
                );

                if (!vehicleSelect) return;

                let selectedVehicles = (o.vehicle_ids || []).map(String);

                $(vehicleSelect).selectpicker('val', selectedVehicles);
                $(vehicleSelect).selectpicker('refresh');
            });

            // ✅ REFRESH AFTER SETTING VALUES
            // TB.plugins.bootstrapSelect('refresh');

            new bootstrap.Modal(document.getElementById('driverModal')).show();
           

           if (!assignable) { $('.order-driver-select') .prop('disabled', true) .selectpicker('refresh'); $('.order-vehicle-select') .prop('disabled', true) .selectpicker('refresh'); $('#assignDriver').prop('disabled', true); $('#bulkAssignBtn').prop('disabled', true); Swal.fire({ icon: 'warning', title: 'Driver Assignment Disabled', text: 'This tour is not assignable.' }); 

                $('#bulkAssignBtn').hide();
                $('#bulkAssignPanel').hide();

            }


            else { $('.order-driver-select') .prop('disabled', false) .selectpicker('refresh'); $('.order-vehicle-select') .prop('disabled', false) .selectpicker('refresh'); $('#assignDriver').prop('disabled', false); $('#bulkAssignBtn').prop('disabled', false); 

                $('#bulkAssignBtn').show();
                
            } 
           });
            
    });

    document
.getElementById('bulkAssignBtn')
.addEventListener('click', function(){

    let panel = document.getElementById('bulkAssignPanel');

    if(panel.style.display === 'none' || panel.style.display === ''){

        panel.style.display='block';
        this.innerHTML='Hide Bulk Assignment';

    }else{

        panel.style.display='none';
        this.innerHTML='Assign Driver & Vehicle to All Orders';

    }

});
        document
.getElementById('applyBulkAssignment')
.addEventListener('click', function () {

    let drivers =
        $('#bulkDriver').val() || [];

    let vehicle =
        $('#bulkVehicle').val();

    $('.order-driver-select').each(function () {

        $(this)
            .selectpicker('val', drivers)
            .selectpicker('refresh');

    });

    $('.order-vehicle-select').each(function () {

        $(this)
            .selectpicker('val', vehicle)
            .selectpicker('refresh');

    });

});

function loadBulkDropdowns() {

    let driverHtml = '';

    window.allDrivers.forEach(function(driver) {
        driverHtml += `<option value="${driver.id}">${driver.name}</option>`;
    });

    $('#bulkDriver').html(driverHtml);

    let vehicleHtml = '<option value="">Select</option>';

    vehiclesList.forEach(function(vehicle) {
        vehicleHtml += `<option value="${vehicle.id}">${vehicle.name}</option>`;
    });

    $('#bulkVehicle').html(vehicleHtml);

    $('#bulkDriver').selectpicker('destroy').selectpicker();
    $('#bulkVehicle').selectpicker('destroy').selectpicker();
}

    // =========================
    // ASSIGN DRIVER (FIXED)
    // =========================
    document.getElementById('assignDriver').addEventListener('click', async function() {

        let btn = this;
        let date = document.getElementById('modal_date').value;

        let ordersPayload = [];

        document.querySelectorAll('.order-content').forEach(function(row) {

            let orderId = parseInt(row.querySelector('.order-checkbox').value);

            let $select = $(row).find('.order-driver-select');
            let $select2 = $(row).find('.order-vehicle-select');

            let selectedDrivers = [];
            let selectedVehicles = [];

            // ✅ READ FROM SELECTED OPTIONS (REAL FIX)
            $select.find('option:selected').each(function () {
                selectedDrivers.push(parseInt($(this).val()));
            });

            $select2.find('option:selected').each(function () {
                selectedVehicles.push(parseInt($(this).val()));
            });

            console.log('FIXED:', orderId, selectedDrivers, selectedVehicles);

            ordersPayload.push({
                order_id: orderId,
                driver_ids: selectedDrivers,
                vehicle_ids: selectedVehicles,
                assignment_type: row.dataset.assignmentType
            });
        });

        try {
            btn.disabled = true;
            btn.innerText = 'Saving...';

            let res = await fetch("{{ route('admin.assign.driver') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    orders: ordersPayload,
                    date: date
                })
            });

            if (!res.ok) throw new Error();

            location.reload();

        } catch (e) {
            alert('Error saving');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Assign';
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