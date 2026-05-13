<x-admin>
@section('title', 'Driver Manifest')


<div class="card-primary mb-3">
    <!-- <form method="GET" action="{{ route('admin.orders.tour.manifest') }}"> -->
        <div class="card-header order-manifest-head">
            <div class="d-flex justify-content-between align-items-center w-100 mb-manifest">
                <h3 class="card-title text-white">Driver Manifest</h3>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center left-btn" id="prev-date">
                        <i class="bi bi-chevron-left"></i>
                    </button> 
                    <input type="date" name="date" id="filter-date" class="form-control form-control-sm filterDate" value="{{ request('date', \Carbon\Carbon::today()->toDateString()) }}" />
                    <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center right-btn" id="next-date">
                        <i class="bi bi-chevron-right"></i>
                    </button> 
                </div>
                <div>
                <select id="driverFilter" class="form-control">
                <option value="">All Drivers</option>
                <option value="assigned">Has Driver</option>
                <option value="unassigned">No Driver</option>

                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>
            </div>
                <!-- <a href="{{ route('admin.orders.tour.manifest.download', ['date' => request('date')]) }}"
                   class="btn btn-success btn-sm">
                   <i class="bi bi-download"></i> Download Excel
                </a> -->
            </div>
        </div>
    <!-- </form> -->
</div>

<div class="card">
    <!-- <div class="card-header d-flex justify-content-between align-items-center">

        <h4>Driver Manifest</h4>

        <div class="d-flex gap-2">

            <input type="date" id="filter-date"
                value="{{ request('date', now()->toDateString()) }}"
                class="form-control">

            <select id="driverFilter" class="form-control">
                <option value="">All Drivers</option>
                <option value="assigned">Has Driver</option>
                <option value="unassigned">No Driver</option>

                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>

        </div>

    </div> -->

    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="driverModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Assign Driver</h5>
            </div>

            <div class="modal-body">

    <input type="hidden" id="event_date">

    <label class="mb-2">Select Driver</label>
    <select id="driver_id" class="form-control mb-3">
        <option value="">Select Driver</option>
        @foreach($drivers as $driver)
            <option value="{{ $driver->id }}">
                {{ $driver->name }}
            </option>
        @endforeach
    </select>

    <label class="mb-2">Select Orders</label>

    <div id="order_list" style="max-height:300px; overflow-y:auto; border:1px solid #ddd; padding:10px; border-radius:6px;">
        <!-- Orders will be injected here -->
    </div>

    <div class="mt-2">
        <label>
            <input type="checkbox" id="select_all_orders" checked>
            Apply to all
        </label>
    </div>

</div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="assignDriver">Assign</button>
            </div>

        </div>
    </div>
</div>
@section('js')

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let calendarEl = document.getElementById('calendar');
    let rawData = @json($calendar);

    let events = [];

    // 🔥 Convert slot time → proper datetime
    function convertToDateTime(date, timeStr) {
        let [time, modifier] = timeStr.split(' ');
        let [hours, minutes] = time.split(':');

        hours = parseInt(hours);

        if (modifier === 'PM' && hours !== 12) hours += 12;
        if (modifier === 'AM' && hours === 12) hours = 0;

        return `${date}T${String(hours).padStart(2,'0')}:${minutes}:00`;
    }

    Object.keys(rawData).forEach(date => {

        Object.keys(rawData[date]).forEach(slot => {

            let orders = rawData[date][slot];

            let totalGuests = 0;

            orders.forEach(o => {
                let match = o.guests?.match(/\d+/);
                totalGuests += match ? parseInt(match[0]) : 0;
            });

            // ✅ FIX: use proper datetime instead of just date
            let startDateTime = convertToDateTime(date, slot);

            events.push({
                title: `${slot} | ${orders.length} Orders | ${totalGuests} Pax`,
                start: startDateTime,
                allDay: false,
                extendedProps: {
                    orders: orders
                }
            });

        });

    });

    let calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'timeGridWeek',
        height: 'auto',

        // ❌ REMOVED DATE INPUT DEPENDENCY
        initialDate: "{{ $date ?? now()->toDateString() }}",

        slotMinTime: "06:00:00",
        slotMaxTime: "23:00:00",
        slotDuration: "00:15:00",

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,dayGridMonth'
        },

        events: events,

        eventClick: function(info) {

    let orders = info.event.extendedProps.orders;

    document.getElementById('event_date').value = info.event.startStr;

    let container = document.getElementById('order_list');
    container.innerHTML = '';

    let driverIds = [];

    orders.forEach(o => {

        if(o.driver_id) driverIds.push(o.driver_id);

        container.innerHTML += `
            <div style="border-bottom:1px solid #eee; padding:6px 0;">
                <label style="cursor:pointer;">
                    <input type="checkbox" class="order-checkbox" value="${o.order_id}" checked>
                    <strong>#${o.order_number}</strong> - ${o.customer}
                    <br>
                    <small>🚗 ${o.driver_names?.length ? o.driver_names.join(', ') : 'No Driver'}</small>
                </label>
            </div>
        `;
    });

    // auto-select driver if same
    let uniqueDrivers = [...new Set(driverIds)];
    document.getElementById('driver_id').value = uniqueDrivers.length === 1 ? uniqueDrivers[0] : '';

    new bootstrap.Modal(document.getElementById('driverModal')).show();
},

        eventContent: function(arg) {

		    let orders = arg.event.extendedProps.orders;

		    let html = `
		        <div>
		            <strong>${arg.event.title}</strong>
		            <div style="font-size:12px;">
		    `;

		    orders.forEach(o => {
		        html += `
		            <div style="margin-top:4px;">
		                #${o.order_number} - ${o.customer}
		                <br>
		                🚗 <b>${o.driver_name ?? 'No Driver'}</b>
		            </div>
		        `;
		    });

		    html += `</div></div>`;

		    return { html: html };
		}

    });

    calendar.render();

    // ✅ Assign Driver
    document.getElementById('assignDriver').addEventListener('click', async function(){

    let btn = this;

    let orderIds = document.getElementById('order_ids').value;
    let driverId = document.getElementById('driver_id').value;
    let date = document.getElementById('event_date').value;

    if (!orderIds || !driverId) {
        alert('Please select driver properly');
        return;
    }

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
                order_ids: JSON.parse(orderIds),
                driver_id: driverId,
                date: date
            })
        });

        if (!response.ok) {
            throw new Error('Request failed');
        }

        let data = await response.json();

        // Close modal
        let modal = bootstrap.Modal.getInstance(document.getElementById('driverModal'));
        if (modal) modal.hide();

        // Reload (keep for now)
        setTimeout(() => location.reload(), 500);

    } catch (error) {
        console.error(error);
        alert('Something went wrong while assigning driver');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Assign';
    }

});

    // ✅ Driver Filter (KEEP THIS)
    document.getElementById('driverFilter').addEventListener('change', function(){

    let value = this.value;

    calendar.getEvents().forEach(event => {

        let orders = event.extendedProps.orders;

        let visible = false;

        if(value === 'assigned'){
            visible = orders.some(o => o.driver_id); // at least one assigned
        }
        else if(value === 'unassigned'){
            visible = orders.every(o => !o.driver_id); // none assigned
        }
        else if(value){
            visible = orders.some(o => o.driver_id == value);
        }
        else{
            visible = true;
        }

        event.setProp('display', visible ? 'auto' : 'none');

    });

});

});
const dateInput = document.getElementById('filter-date');

// when date changes → reload page with query param
dateInput.addEventListener('change', function () {
    let selectedDate = this.value;
    window.location.href = "?date=" + selectedDate;
});
</script>

@endsection
</x-admin>