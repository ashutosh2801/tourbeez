<x-admin>
@section('title', 'Driver Manifest')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">



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
            <a href="{{ route('admin.driver.manifest.export', ['date' => $date]) }}" 
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
                                <br><small class="text-muted">{{ $tourTimes[$tourTitle] }}</small>
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
                                style="cursor: {{ count($cellOrders) ? 'pointer' : 'default' }};">
                                @if(count($cellOrders))
                                    <strong>{{ $totalGuests }}</strong>
                                    @if($driverNames)
                                        <br><small class="text-success">{{ $driverNames }}</small>
                                    @else
                                        <br><small class="text-danger">No Driver</small>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($dateRange) + 1 }}" class="text-center text-muted">
                            No tours found for this week.
                        </td>
                    </tr>
                @endforelse
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
<script>

    
document.addEventListener('DOMContentLoaded', function() {

    // Week navigation
    document.getElementById('prev-week').addEventListener('click', function() {
        let dateInput = document.getElementById('filter-date');
        let current = new Date(dateInput.value);
        current.setDate(current.getDate() - 7);
        dateInput.value = current.toISOString().split('T')[0];
        window.location.href = "?date=" + dateInput.value;
    });

    document.getElementById('next-week').addEventListener('click', function() {
        let dateInput = document.getElementById('filter-date');
        let current = new Date(dateInput.value);
        current.setDate(current.getDate() + 7);
        dateInput.value = current.toISOString().split('T')[0];
        window.location.href = "?date=" + dateInput.value;
    });

    document.getElementById('filter-date').addEventListener('change', function() {
        window.location.href = "?date=" + this.value;
    });

    // Cell click → open modal
    document.querySelectorAll('.manifest-cell.has-orders').forEach(function(cell) {
        cell.addEventListener('click', function() {
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
                if (o.driver_id) driverIds.push(o.driver_id);

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
                            🚗 ${o.driver_names?.length ? o.driver_names.join(', ') : 'No Driver'}
                        </small>
                    </label>
                </div>
            `;
            });

            document.getElementById('modal_order_ids').value = JSON.stringify(orderIds);


            // let driverSelect = document.getElementById('driver_id');
// 
            let driverSelect = document.getElementById('driver_id');

// reset first
Array.from(driverSelect.options).forEach(opt => opt.selected = false);

// collect drivers from orders
// let driverIds = [];

orders.forEach(o => {
    if (o.driver_ids && o.driver_ids.length) {
        driverIds.push(...o.driver_ids);
    }
});

// unique drivers
let uniqueDrivers = [...new Set(driverIds)];

// select them
Array.from(driverSelect.options).forEach(opt => {
    if (uniqueDrivers.includes(parseInt(opt.value))) {
        opt.selected = true;
    }
});

// refresh UI (IMPORTANT for aiz-selectpicker)
$('.aiz-selectpicker').selectpicker('refresh');

let selectedDrivers = Array.from(document.getElementById('driver_id').selectedOptions).map(o => parseInt(o.value));


            // Auto-select driver if all same
            // let uniqueDrivers = [...new Set(driverIds)];
            // document.getElementById('driver_id').value = uniqueDrivers.length === 1 ? uniqueDrivers[0] : '';

            // new bootstrap.Modal(document.getElementById('driverModal')).show();

            let modalEl = document.getElementById('driverModal');
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    });

    // Select all checkbox
    document.getElementById('select_all_orders').addEventListener('change', function() {
        document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Assign driver
    document.getElementById('assignDriver').addEventListener('click', async function() {

    let btn = this;

    let date = document.getElementById('modal_date').value;

    // ✅ GET MULTIPLE DRIVERS (FIXED)
    let selectedDrivers = Array.from(document.getElementById('driver_id').selectedOptions)
        .map(o => parseInt(o.value));

    // ✅ GET MULTIPLE ORDERS
    let selectedOrders = [];
    document.querySelectorAll('.order-checkbox:checked').forEach(cb => {
        selectedOrders.push(parseInt(cb.value));
    });

    // if (!selectedOrders.length || !selectedDrivers.length) {
    //     alert('Please select driver(s) and at least one order');
    //     return;
    // }

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
                order_ids: selectedOrders,   // ✅ correct
                driver_ids: selectedDrivers, // ✅ FIXED
                date: date
            })
        });

        if (!response.ok) throw new Error('Request failed');

        // ✅ SAFE MODAL CLOSE (NO BOOTSTRAP VERSION ISSUE)
        let modalEl = document.getElementById('driverModal');
        if (window.bootstrap && bootstrap.Modal) {
            let instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
        } else {
            // fallback for Bootstrap 4
            $('#driverModal').modal('hide');
        }

        setTimeout(() => location.reload(), 300);

    } catch (error) {
        console.error(error);
        alert('Something went wrong while assigning driver');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Assign';
    }
});

    // Driver filter
    document.getElementById('driverFilter').addEventListener('change', function() {
        let value = this.value;

        document.querySelectorAll('.manifest-cell').forEach(function(cell) {
            if (!cell.classList.contains('has-orders')) return;

            let orders = JSON.parse(cell.dataset.orders);
            // let visible = !value || orders.some(o => o.driver_id == value);
            let visible = !value || orders.some(o => 
                o.driver_ids && o.driver_ids.includes(parseInt(value))
            );

            cell.style.opacity = visible ? '1' : '0.3';
        });
    });

});
document.getElementById('removeDriver').addEventListener('click', async function(){

    let selectedOrders = [];
    document.querySelectorAll('.order-checkbox:checked').forEach(cb => {
        selectedOrders.push(parseInt(cb.value));
    });

    let driverId = document.getElementById('driver_id').value;
    let date = document.getElementById('modal_date').value;

    if (!selectedOrders.length || !driverId) {
        alert('Select driver & orders');
        return;
    }

    await fetch("{{ route('admin.remove.driver') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            order_ids: selectedOrders,
            driver_id: driverId,
            date: date
        })
    });

    location.reload();
});
</script>
@endsection
</x-admin>
