<style>
    .bootstrap-datetimepicker-widget {
        display: none !important;
    }

</style>


<div class="card">
    <div class="card card-primary">
        <form class="needs-validation" novalidate action="{{ route('admin.tour.schedule_update', $data->id) }}" method="POST"
    enctype="multipart/form-data" autocomplete="off">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Scheduling</h3>
                <!-- <button type="button" id="add-schedule" class="btn btn-sm btn-success">+ Add Schedule</button> -->
                <div>
                    <a class="btn btn-sm btn-success" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}">{{translate('Scheduling')}}</a>

                    <a type="button" href="{{ route('admin.tour.edit.schedule-calendar', $data->id) }}" class="btn btn-sm btn-success">Schedule Calendar</a>
                </div>
            </div>
        </div>
        <div class="card-body">


            
             <div class="controls">
                <input type="date" id="datePicker" value="{{ $selectedDate }}">
               
            </div>
            <div id="calendar"></div>

        </div>
    </form>
</div>


<div class="modal fade" id="slotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="slotForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Slot Details</h5>
          
        </div>

        <div class="modal-body">
          <input type="hidden" name="tour_id" id="modalTourId" value="{{ $data->id }}">
          <input type="hidden" name="schedule_id" id="modalScheduleId">
          <input type="hidden" name="slot_id" id="modalSlotId">

          <div class="mb-3">
            <label>Date</label>
            <input type="date" name="slot_date" id="modalDate" class="form-control" required disabled>
          </div>

          <div class="mb-3">
            <label>Start Time</label>
            <input type="time" name="slot_start_time" id="slotStartTime" class="form-control" required disabled>
          </div>

          <div class="mb-3">
            <label>End Time</label>
            <input type="time" name="slot_end_time" id="slotEndTime" class="form-control" required disabled>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-danger delete-slot-btn" data-type="all">All Session</button>
          <button type="button" class="btn btn-danger delete-slot-btn" data-type="after">After This Session</button>
          <button type="button" class="btn btn-danger delete-slot-btn" data-type="single">This Session Only</button>
        </div>
      </div>
    </form>
  </div>
</div>


    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
    var selectedDate = "{{ $selectedDate }}";

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            initialDate: selectedDate,
            headerToolbar: false,
            nowIndicator: true,
            allDaySlot: false,

            events: function(fetchInfo, successCallback, failureCallback) {
                let url = "{{ route('admin.tour.edit.schedule-calendar-event', $data->id) }}";
                url += "?date=" + selectedDate;
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // 👇 Format events to only show time (not date) in calendar
                        const formatted = data.map(e => {
                            return {
                                ...e,
                                display: 'block',
                                title: new Date(e.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) +
                                       (e.end ? ' - ' + new Date(e.end).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '')
                            };
                        });
                        successCallback(formatted);
                    })
                    .catch(error => failureCallback(error));
            },


                // Delete button
            eventClick: function(info) {
                const startDate = new Date(info.event.start);
                const endDate   = info.event.end ? new Date(info.event.end) : null;

                // Fill modal inputs
      
                document.getElementById('modalSlotId').value = info.event.id;
                document.getElementById('modalDate').value = startDate.toISOString().slice(0,10);
                document.getElementById('slotStartTime').value = startDate.toTimeString().slice(0,5);
                document.getElementById('slotEndTime').value = endDate ? endDate.toTimeString().slice(0,5) : '';

                document.getElementById('modalScheduleId').value = info.event.extendedProps.schedule_id || "";

                $('#slotModal').modal('show');

            }
        });

        calendar.render();

        document.getElementById('datePicker').addEventListener('change', function (e) {
            var newDate = e.target.value || new Date().toISOString().slice(0, 10);
            window.location.href = "{{ route('admin.tour.edit.schedule-calendar', $data->id) }}?date=" + newDate;
        });
// Save slot via AJAX
        document.querySelectorAll('.delete-slot-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            fetch("{{ route('admin.tour.delete-slots.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    tour_id: document.getElementById('modalTourId').value,
                    slot_date: document.getElementById('modalDate').value,
                    slot_start_time: document.getElementById('slotStartTime').value,
                    slot_end_time: document.getElementById('slotEndTime').value,
                    delete_type: this.dataset.type
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // calendar.refetchEvents();
                    // calendar.render();
                    setTimeout(() => {
                        location.reload();
                    }, 500);

                    $('#slotModal').modal('hide');

                } else {
                    alert("Failed to save slot");
                }
            });
        });
        });
    });
</script>



