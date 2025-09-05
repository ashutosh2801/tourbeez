<style>
    .bootstrap-datetimepicker-widget {
        display: none !important;
    }

</style>


<div class="card">
    <div class="card card-primary">
        <form class="needs-validation" novalidate action="{{ route('admin.tour.schedule_update', $data->id) }}" method="POST"
    enctype="multipart/form-data" autocomplete="off">
        <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Scheduling</h3>
                <!-- <button type="button" id="add-schedule" class="btn btn-sm btn-success">+ Add Schedule</button> -->
                <a class="btn btn-sm btn-success" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}">{{translate('Scheduling')}}</a>

                <a type="button" href="{{ route('admin.tour.edit.schedule-calendar', $data->id) }}" class="btn btn-sm btn-success">Schedule Calendar</a>
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
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Slot Details</h5>
        
      </div>
      <div class="modal-body">
        <p><strong>Title:</strong> <span id="slotTitle"></span></p>
        <p><strong>Time:</strong> <span id="slotTime"></span></p>
      </div>
      <div class="modal-footer">
        <button id="deleteSlotBtn" class="btn btn-danger">Delete Slot</button>
        <!-- <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
      </div>
    </div>
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

            eventClick: function(info) {
                const startDate = new Date(info.event.start);
                const endDate   = info.event.end ? new Date(info.event.end) : null;

                // 👇 Show date in modal title
                document.getElementById('slotTitle').textContent = startDate.toLocaleDateString();

                // 👇 Show only time in slotTime
                document.getElementById('slotTime').textContent =
                    startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) +
                    (endDate ? ' - ' + endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '');

                // Delete button
                document.getElementById('deleteSlotBtn').onclick = function () {
                    if (confirm("Are you sure you want to delete this slot?")) {
                        fetch("#", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                slot_id: info.event.id
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                info.event.remove();
                                bootstrap.Modal.getInstance(document.getElementById('slotModal')).hide();
                            } else {
                                alert("Failed to delete slot");
                            }
                        });
                    }
                };

                var modal = new bootstrap.Modal(document.getElementById('slotModal'));
                modal.show();
            }
        });

        calendar.render();

        document.getElementById('datePicker').addEventListener('change', function (e) {
            var newDate = e.target.value || new Date().toISOString().slice(0, 10);
            window.location.href = "{{ route('admin.tour.edit.schedule-calendar', $data->id) }}?date=" + newDate;
        });
    });
</script>



