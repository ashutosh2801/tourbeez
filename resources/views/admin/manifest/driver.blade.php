<x-admin>
@section('title', 'Driver Manifest')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
    
    .manifest-grid {
    font-size: 16px; /* base font bigger */
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

/* Main pax number */
.manifest-grid td strong {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

/* Driver names */
.manifest-grid small {
    font-size: 12px;
    font-weight: 500;
}

/* Hover effect */
.manifest-cell.has-orders:hover {
    background-color: #eef6ff;
    transition: 0.2s;
}

/* Totals row */
.total-pax {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
}

/* Assigned row */
.assigned-pax {
    font-size: 15px;
    font-weight: 700;
    color: #16a34a;
}
</style>



<div class="card-primary mb-3">
    <div class="card-header order-manifest-head">
        <div class="d-flex justify-content-between align-items-center w-100 mb-manifest">
            <h3 class="card-title text-white">Driver Manifest</h3>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center left-btn" id="prev-week">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <input type="date" name="date" id="filter-date" class="form-control form-control-sm filterDate" 
                       value="{{ $date }}" style="width: 150px;" />
                <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center right-btn" id="next-week">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <select id="driverFilter" class="form-control" style="width: 200px;">
                <option value="">All Drivers</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>
            <!-- <a href="{{ route('admin.driver.manifest.export', ['date' => $date]) }}" 
                   class="btn btn-success btn-sm">


                    Export Excel
                </a> -->

                <a href="{{ route('admin.driver.manifest.export', [
                    'date' => $date,
                    'driver_id' => request('driver_id')
                ]) }}" 
                class="btn btn-success btn-sm">
                    Export Excel
                </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm manifest-grid">
            <thead class="">
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
                            <strong>{{ $tourTitle }}</strong>
                            @if(isset($tourTimes[$tourTitle]))
                                <!-- <br><small class="text-muted">{{ $tourTimes[$tourTitle] }}</small> -->
                            @endif
                        </td>
                        @foreach($dateRange as $d)
                            @php
                                $dateKey = $d->toDateString();
                                $cellOrders = $dates[$dateKey] ?? [];
                                $totalGuests = collect($cellOrders)->sum('guest_count');
                                $driverNames = collect($cellOrders)
                                    ->pluck('driver_names')   // array of arrays
                                    ->flatten()
                                    ->filter()
                                    ->unique()
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
                            <td class="text-center assigned-pax text-success" data-date="{{ $d->toDateString() }}">
                                {{ $assignedPaxPerDay[$d->toDateString()] ?? 0 }}
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
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_date">
                <input type="hidden" id="modal_order_ids">

                <div class="mb-3">
                    <label class="form-label"><strong id="modal_tour_title"></strong></label>
                    <div class="text-muted" id="modal_date_display"></div>
                </div>

                

                <div class="form-group">
                    <label for="driver_id" class="form-label">Select Drivers *</label>

                    <select name="driver_ids[]" 
                            id="driver_id" 
                            class="form-control aiz-selectpicker" 
                            data-live-search="true" 
                            multiple>

                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->name }}
                            </option>
                        @endforeach

                    </select>

                    <small class="text-muted">You can select multiple drivers</small>
                </div>

                <label class="form-label">Orders</label>
                <div id="order_list" style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 6px;"></div>

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
                <button type="button" class="btn btn-primary" id="assignDriver">Assign</button>

            </div>
        </div>
    </div>
</div>

<style>
.manifest-grid td, .manifest-grid th {
    vertical-align: middle;
}
.manifest-cell.has-orders:hover {
    background-color: #f0f7ff;
}
</style>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    let driverFilter = document.getElementById('driverFilter');
    let exportBtn = document.querySelector('a.btn-success');
    let dateInput = document.getElementById('filter-date');

    // =========================
    // ✅ UPDATE EXPORT URL
    // =========================
    function updateExportUrl() {
        let selectedDriver = driverFilter.value;
        let date = dateInput.value;

        let url = `?date=${date}`;

        if (selectedDriver) {
            url += `&driver_id=${selectedDriver}`;
        }

        exportBtn.href = "{{ route('admin.driver.manifest.export') }}" + url;
    }

    // =========================
    // WEEK NAVIGATION
    // =========================
    document.getElementById('prev-week').addEventListener('click', function() {
        let current = new Date(dateInput.value);
        current.setDate(current.getDate() - 6);
        dateInput.value = current.toISOString().split('T')[0];
        window.location.href = "?date=" + dateInput.value;
    });

    document.getElementById('next-week').addEventListener('click', function() {
        let current = new Date(dateInput.value);
        current.setDate(current.getDate() + 6);
        dateInput.value = current.toISOString().split('T')[0];
        window.location.href = "?date=" + dateInput.value;
    });

    dateInput.addEventListener('change', function() {
        window.location.href = "?date=" + this.value;
    });

    // =========================
    // CELL CLICK → MODAL
    // =========================
    document.querySelectorAll('.manifest-cell.has-orders').forEach(function(cell) {
        cell.addEventListener('click', function() {

            let isAssignable = this.dataset.assignable;

            if (isAssignable !== '1') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Not Allowed',
                    text: 'Driver cannot be assigned for this tour.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            let tourTitle = this.dataset.tour;
            let date = this.dataset.date;
            let orders = JSON.parse(this.dataset.orders);

            document.getElementById('modal_tour_title').textContent = tourTitle;
            document.getElementById('modal_date_display').textContent = date;
            document.getElementById('modal_date').value = date;

            let container = document.getElementById('order_list');
            container.innerHTML = '';

            let orderIds = [];
            let driverIds = [];

            orders.forEach(function(o) {
                orderIds.push(o.order_id);

                container.innerHTML += `
                <div style="border-bottom: 1px solid #eee; padding: 6px 0;">
                    <label style="cursor: pointer; width:100%;">
                        <input type="checkbox" class="order-checkbox" value="${o.order_id}" checked>

                        <a href="/admin/orders/${o.order_encrypt_id}/edit" target="_blank" style="font-weight:600;">
                            #${o.order_number}
                        </a>

                        - ${o.customer || 'N/A'}

                        <br>
                        <small>
                            👥 ${o.guest_count} pax |
                            🚗 ${o.driver_names?.length ? o.driver_names.join(', ') : ''}
                        </small>
                    </label>
                </div>
                `;

                if (o.driver_ids && o.driver_ids.length) {
                    driverIds.push(...o.driver_ids);
                }
            });

            document.getElementById('modal_order_ids').value = JSON.stringify(orderIds);

            let driverSelect = document.getElementById('driver_id');

            // reset
            Array.from(driverSelect.options).forEach(opt => opt.selected = false);

            let uniqueDrivers = [...new Set(driverIds)];

            Array.from(driverSelect.options).forEach(opt => {
                if (uniqueDrivers.includes(parseInt(opt.value))) {
                    opt.selected = true;
                }
            });

            $('.aiz-selectpicker').selectpicker('refresh');

            let modal = new bootstrap.Modal(document.getElementById('driverModal'));
            modal.show();
        });
    });

    // =========================
    // SELECT ALL ORDERS
    // =========================
    document.getElementById('select_all_orders').addEventListener('change', function() {
        document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // =========================
    // ASSIGN DRIVER
    // =========================
    document.getElementById('assignDriver').addEventListener('click', async function() {

        let btn = this;
        let date = document.getElementById('modal_date').value;

        let selectedDrivers = Array.from(document.getElementById('driver_id').selectedOptions)
            .map(o => parseInt(o.value));

        let selectedOrders = [];
        document.querySelectorAll('.order-checkbox:checked').forEach(cb => {
            selectedOrders.push(parseInt(cb.value));
        });

        try {
            btn.disabled = true;
            btn.innerText = 'Assigning...';

            let response = await fetch("{{ route('admin.assign.driver') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    order_ids: selectedOrders,
                    driver_ids: selectedDrivers,
                    date: date
                })
            });

            if (!response.ok) throw new Error();

            let modalEl = document.getElementById('driverModal');
            let instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();

            setTimeout(() => location.reload(), 300);

        } catch (e) {
            alert('Something went wrong');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Assign';
        }
    });

    // =========================
    // DRIVER FILTER + TOTAL UPDATE
    // =========================
    driverFilter.addEventListener('change', function() {

        let selectedDriver = parseInt(this.value);

        let totalMap = {};
        let assignedMap = {};

        document.querySelectorAll('.manifest-cell').forEach(function(cell) {

            if (!cell.classList.contains('has-orders')) return;

            let orders = JSON.parse(cell.dataset.orders);
            let date = cell.dataset.date;

            let visible = false;

            orders.forEach(o => {

                let matches = !selectedDriver ||
                    (o.driver_ids && o.driver_ids.includes(selectedDriver));

                if (matches) {
                    visible = true;

                    totalMap[date] = (totalMap[date] || 0) + o.guest_count;

                    if (o.driver_ids && o.driver_ids.length) {
                        assignedMap[date] = (assignedMap[date] || 0) + o.guest_count;
                    }
                }
            });

            cell.style.opacity = visible ? '1' : '0.2';
        });

        // UPDATE TOTAL ROW
        document.querySelectorAll('.total-pax').forEach(td => {
            let date = td.dataset.date;
            td.innerText = totalMap[date] || 0;
        });

        // UPDATE ASSIGNED ROW
        document.querySelectorAll('.assigned-pax').forEach(td => {
            let date = td.dataset.date;
            td.innerText = assignedMap[date] || 0;
        });

        // ✅ UPDATE EXPORT URL
        updateExportUrl();
    });

    // =========================
    // INITIAL EXPORT URL SET
    // =========================
    updateExportUrl();

});
</script>
@endsection
</x-admin>
