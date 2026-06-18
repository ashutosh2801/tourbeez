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

                <div class="mt-2">
                    <label>
                        <input type="checkbox" id="select_all_orders" checked> Apply to all orders
                    </label>
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

            if (this.dataset.assignable !== '1') {
                Swal.fire('Not Allowed', 'Driver cannot be assigned', 'warning');
                return;
            }

            let orders = JSON.parse(this.dataset.orders);
            let date = this.dataset.date;

            document.getElementById('modal_date').value = date;
            document.getElementById('modal_tour_title').innerText = this.dataset.tour;
            document.getElementById('modal_date_display').innerText = date;

            let container = document.getElementById('order_list');
            container.innerHTML = '';

            // build driver options
            let driversHtml = '';
            driversList.forEach(d => {
                driversHtml += `<option value="${d.id}">${d.name}</option>`;
            });

            // render orders
            orders.forEach(o => {
                let orderUrl = orderEditRoute.replace(':id', o.order_encrypt_id);
                container.innerHTML += `
                <div class="order-content mb-2 p-2 border rounded">
                    <div class="d-flex justify-content-between">

                        <div style="width:50%">
                            <input type="checkbox" class="order-checkbox" value="${o.order_id}" checked>
                            <a href="${orderUrl}" class="alink" target="_blank">
                        #${o.order_number}
                    </a><br>
                            <small>${o.customer || ''}</small><br>
                            <small>👥 ${o.guest_count}</small>
                        </div>

                        <div style="width:50%">
                            <select 
                                class="form-control aiz-selectpicker order-driver-select"
                                multiple
                                data-live-search="true"
                                data-order-id="${o.order_id}">
                                ${driversHtml}
                            </select>
                        </div>

                    </div>
                </div>`;
            });

            // ✅ INIT AIZ SELECT PROPERLY
            TB.plugins.bootstrapSelect();

            // ✅ PRESELECT EXISTING DRIVERS
            orders.forEach(o => {
                let select = document.querySelector(
                    `.order-driver-select[data-order-id="${o.order_id}"]`
                );

                if (!select) return;

                let selected = o.driver_ids || [];

                Array.from(select.options).forEach(opt => {
                    opt.selected = selected.includes(parseInt(opt.value));
                });
            });

            // ✅ REFRESH AFTER SETTING VALUES
            TB.plugins.bootstrapSelect('refresh');

            new bootstrap.Modal(document.getElementById('driverModal')).show();
        });
    });

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

        let selectedDrivers = [];

        // ✅ READ FROM SELECTED OPTIONS (REAL FIX)
        $select.find('option:selected').each(function () {
            selectedDrivers.push(parseInt($(this).val()));
        });

        console.log('FIXED:', orderId, selectedDrivers);

        ordersPayload.push({
            order_id: orderId,
            driver_ids: selectedDrivers
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
//     driverFilter.addEventListener('change', function() {

//     let selectedDriver = parseInt(this.value);

//     let totalMap = {};
//     let assignedMap = {};
//     let driverWiseMap = {}; // NEW

//     document.querySelectorAll('.manifest-cell').forEach(cell => {

//         if (!cell.classList.contains('has-orders')) return;

//         let orders = JSON.parse(cell.dataset.orders);
//         let date = cell.dataset.date;

//         let visible = false;

//         orders.forEach(o => {

//             let match = !selectedDriver ||
//                 (o.driver_ids && o.driver_ids.includes(selectedDriver));

//             if (match) {
//                 visible = true;

//                 totalMap[date] = (totalMap[date] || 0) + o.guest_count;

//                 if (o.driver_ids?.length) {
//                     assignedMap[date] = (assignedMap[date] || 0) + o.guest_count;

//                     // driver-wise
//                     o.driver_ids.forEach(dId => {

//                         if (selectedDriver && dId !== selectedDriver) return;

//                         if (!driverWiseMap[date]) driverWiseMap[date] = {};
//                         driverWiseMap[date][dId] =
//                             (driverWiseMap[date][dId] || 0) + o.guest_count;
//                     });
//                 }
//             }
//         });

//         cell.style.opacity = visible ? '1' : '0.2';
//     });

//     // update totals
//     document.querySelectorAll('.total-pax').forEach(td => {
//         td.innerText = totalMap[td.dataset.date] || 0;
//     });

//     document.querySelectorAll('.assigned-pax').forEach(td => {

//         let date = td.dataset.date;
//         let html = `<strong>${assignedMap[date] || 0}</strong>`;

//         if (driverWiseMap[date]) {

//             html += `<div style="margin-top:5px;">`;

//             Object.entries(driverWiseMap[date]).forEach(([driverId, pax]) => {

//                 let driver = driversList.find(d => d.id == driverId);

//                 html += `
//                     <div style="font-size:12px; color:#374151;">
//                         ${driver?.name || 'Unknown'}: <strong>${pax}</strong>
//                     </div>
//                 `;
//             });

//             html += `</div>`;
//         }

//         td.innerHTML = html;
//     });

//     updateExportUrl();
// });

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