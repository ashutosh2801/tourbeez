<div class="card">
    <div class="card card-primary">
        <form class="needs-validation" novalidate action="{{ route('admin.tour.schedule_update', $data->id) }}" method="POST"
    enctype="multipart/form-data" autocomplete="off">
        <div class="card-header">
            <h3 class="card-title">Scheduling</h3>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-unstyled">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif            
            @method('PUT')
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">

                <div class="row mb-4">
                    <label for="minimum_notice_num" class="form-label col-lg-2">Minimum notice *</label>
                    <div class="col-lg-3">
                        <input type="text" name="minimum_notice_num" id="minimum_notice_num" 
                        value="{{ old('minimum_notice_num', $schedule?->minimum_notice_num) }}"
                            class="form-control " placeholder="Before session start time">
                        @error('minimum_notice_num')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control " name="minimum_notice_unit" id="minimum_notice_unit">
                            <option {{ old('minimum_notice_unit', $schedule?->minimum_notice_unit) === 'MINUTES' ? 'selected' : '' }} value="MINUTES">Minutes</option>
                            <option {{ old('minimum_notice_unit', $schedule?->minimum_notice_unit) === 'HOURS' ? 'selected' : '' }} value="HOURS">Hours</option>
                        </select>
                        @error('minimum_notice_unit')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label for="estimated_duration_num" class="form-label col-lg-2">Estimated duration *</label>
                    <div class="col-lg-3">
                        <input type="text" name="estimated_duration_num" id="estimated_duration_num" 
                        value="{{ old('estimated_duration_num', $schedule?->estimated_duration_num) }}"
                            class="form-control " placeholder="Session time">
                        @error('estimated_duration_num')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control " name="estimated_duration_unit" id="estimated_duration_unit">
                            <option {{ old('estimated_duration_unit', $schedule?->estimated_duration_unit) === 'MINUTES' ? 'selected' : '' }} value="MINUTES">Minutes</option>
                            <option {{ old('estimated_duration_unit', $schedule?->estimated_duration_unit) === 'HOURS' ? 'selected' : '' }} value="HOURS">Hours</option>
                            <option {{ old('estimated_duration_unit', $schedule?->estimated_duration_unit) === 'DAYS' ? 'selected' : '' }} value="DAYS">Days</option>
                        </select>
                        @error('mestimated_duration_unit')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
               
                <div class="mb-4">
                    <div class="row">
                        <label for="session_start_date" class="form-label col-lg-2">Next available session *</label>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-prepend" >
                                    <span class="input-group-text" id="basic-addon-from" style="width:70px;">Form</span>
                                </div>
                                <input type="text" placeholder="Date" name="session_start_date" id="session_start_date" 
                                value="{{ old('session_start_date', $schedule->session_start_date) }}" class="form-control aiz-date-range" data-single="true" 
                                data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                                <div class="input-group-prepend">
                                    <span class="input-group-text calendar-icon-start" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                </div>                       
                            </div>
                            @error('session_start_date')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-lg-3 not-all-date">
                            <div class="input-group">
                                <input type="text" placeholder="Time" name="session_start_time" id="session_start_time" 
                                value="{{ old('session_start_time', $schedule->session_start_time) }}" class="form-control aiz-time-picker"> 
                                <div class="input-group-prepend">
                                    <span class="input-group-text time-icon-start" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                </div>                       
                            </div>
                            @error('session_start_time')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="session_end_date" class="form-label col-lg-2"></label>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon-from"  style="width:70px;">To</span>
                                </div>
                                <input type="text" placeholder="Date" name="session_end_date" id="session_end_date" 
                                value="{{ old('session_end_date', $schedule->session_end_date) }}" class="form-control aiz-date-range" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                                <div class="input-group-prepend">
                                    <span class="input-group-text calendar-icon-end" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                </div>                       
                            </div>
                            @error('session_end_date')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-lg-3 not-all-date">
                            <div class="input-group">
                                <input type="text" placeholder="Time" name="session_end_time" id="session_end_time" 
                                value="{{ old('session_end_time', $schedule->session_end_time) }}" class="form-control aiz-time-picker"> 
                                <div class="input-group-prepend">
                                    <span class="input-group-text time-icon-end" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                </div>                       
                            </div>
                            @error('session_end_time')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-5">
                        <label for="session_start_date" class="form-label col-lg-2"></label>
                        <div class="col-lg-6">
                            <label><input {{ old('sesion_all_day', $schedule->sesion_all_day) ? 'checked' : '' }} 
                            type="checkbox" name="sesion_all_day" id="sesion_all_day" style="width:17px;height:17px"> All day</label>
                        </div>                    
                    </div>
                </div>
                
                <div class="row mb-5">
                    <label for="minimum_notice" class="form-label col-lg-2">Repeat *</label>
                    <div class="col-lg-3">
                        <select class="form-control" name="repeat_period" id="repeat_period">
                            <option {{ old('repeat_period', $schedule->repeat_period) === 'NONE' ? 'selected' : '' }} value="NONE">Do not repeat</option>
                            <option {{ old('repeat_period', $schedule->repeat_period) === 'MINUTELY' ? 'selected' : '' }} value="MINUTELY">Repeat minute-by-minute</option>
                            <option {{ old('repeat_period', $schedule->repeat_period) === 'HOURLY' ? 'selected' : '' }} value="HOURLY">Repeat hourly</option>
                            <option {{ old('repeat_period', $schedule->repeat_period) === 'DAILY' ? 'selected' : '' }} value="DAILY">Repeat daily</option>
                            <option {{ old('repeat_period', $schedule->repeat_period) === 'WEEKLY' ? 'selected' : '' }} value="WEEKLY">Repeat weekly</option>
                            <option {{ old('repeat_period', $schedule->repeat_period) === 'MONTHLY' ? 'selected' : '' }} value="MONTHLY">Repeat monthly</option>
                            <option {{ old('repeat_period', $schedule->repeat_period) === 'YEARLY' ? 'selected' : '' }} value="YEARLY">Repeat yearly</option>
                        </select>
                        @error('repeat_period')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-lg-3 d-none not-repeat-period not-repeat-period3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-from"  style="width:70px;">Every</span>
                            </div>
                            <select class="form-control" name="repeat_period_unit" id="repeat_period_unit">
                                @for ($i=1; $i<=31; $i++)
                                <option {{ old('repeat_period_unit', $schedule->repeat_period_unit) == $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }}</option>                            
                                @endfor
                            </select>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-repeat_period_unit">minutes</span>
                            </div>                       
                        </div>
                        @error('repeat_period_unit')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-lg-4 d-none not-repeat-period not-repeat-period3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-until">Until</span>
                            </div>
                            <input type="text" placeholder="Date" name="until_date" id="until_date" 
                            value="{{ old('until_date', $schedule->until_date) }}" 
                            class="form-control aiz-date-range" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text calendar-icon-util" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                            </div>                       
                        </div>
                        @error('until_date')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                @php
                $i = 0;
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                //$schedules = $schedule->repeats()->pluck('');
                @endphp

                @foreach ($days as $day)
                @php
                $repeat = [];
                $repeat = $schedule->repeats?->firstWhere('day', $day);
                @endphp
                <div class="row mb-3 d-none not-repeat-period not-repeat-period2">
                    <input type="hidden" name="Repeat[{{ $i }}][day]" id="Repeat_{{ $i }}_day" value="{{ $day }}" />
                    <label for="Repeat_{{ $i }}_num" class="form-label col-lg-2">{{ $day }}</label>
                    <div class="col-lg-1">
                        <input type="checkbox" {{ old("Repeat.$i.num", isset($repeat['day'])) ? 'checked' : '' }} name="Repeat[{{ $i }}][num]" id="Repeat_{{ $i }}_num" style="width:17px;height:17px">
                    </div> 
                    <div class="col-lg-3 not-repeat-weekly">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-until">From</span>
                            </div>
                            <input type="text" placeholder="Time" name="Repeat[{{ $i }}][start_time]" id="Repeat_{{ $i }}_start_time" 
                            class="form-control start_time aiz-time-picker" value="{{ old("Repeat.$i.start_time", isset($repeat) ? $repeat['start_time'] : '') }}" > 
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                            </div>                       
                        </div>
                    </div>
                    <div class="col-lg-3 not-repeat-weekly">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-until">To</span>
                            </div>
                            <input type="text" placeholder="Time" name="Repeat[{{ $i }}][end_time]" id="Repeat_{{ $i }}_end_time" 
                            class="form-control end_time aiz-time-picker" value="{{ old("Repeat.$i.end_time", isset($repeat) ? $repeat['end_time'] : '') }}" > 
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                            </div>                       
                        </div>
                    </div>
                </div>
                @php
                $i++;
                @endphp
                @endforeach


        </div>
        <div class="card-footer" style="display:block">
            <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
            <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
            <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.location', encrypt($data->id)) }}" class="btn btn-primary">Next</a>
        </div>
        </form>
    </div>
</div>

@section('js')
@parent
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
function setValueTime( value ) {
    let step10 = step1 = '';
    for(i=10; i<91; i+=5) 
    step10+= `<option value="${i}">${i}</option>`;

    for(i=1; i<32; i++) 
    step1+= `<option value="${i}">${i}</option>`;

   
    if( value == 'MINUTELY' ) {
 
        $('#basic-repeat_period_unit').text('minutes');
    }
    else if( value == 'HOURLY' ) {
        $('#basic-repeat_period_unit').text('hours');
    }
    else if( value == 'DAILY' ) {
        $('#basic-repeat_period_unit').text('days');
    }
    else if( value == 'WEEKLY' ) {
        $('#basic-repeat_period_unit').text('weeks');
    }
    else if( value == 'MONTHLY' ) {
        $('#basic-repeat_period_unit').text('months');
    }
    else if( value == 'YEARLY' ) {
        $('#basic-repeat_period_unit').text('years');
    }
}   

function repeat_period(){

    let repeat_period_val = $('#repeat_period').val();

    setValueTime(repeat_period_val)
    if(repeat_period_val == 'NONE') {
        $('.not-repeat-period').addClass('d-none');
    }
    else if(repeat_period_val == 'MONTHLY' || repeat_period_val == 'YEARLY' || repeat_period_val == 'DAILY') {
        console.log(23423);
        $('.not-repeat-period2').addClass('d-none');

        // if(repeat_period_val == 'DAILY')
        $('.not-repeat-period3').removeClass('d-none');
    }
    else {
        if(repeat_period_val == 'MINUTELY' || repeat_period_val == 'HOURLY' || repeat_period_val == 'WEEKLY')
            $('.not-repeat-period').removeClass('d-none');

        if(repeat_period_val == 'WEEKLY')
            $('.not-repeat-weekly').addClass('d-none');
        else 
            $('.not-repeat-weekly').removeClass('d-none');
    }
};

$(document).ready(function(){
    $('#sesion_all_day').click(function(){
        $('.not-all-date').toggleClass('hidden');
    });

    repeat_period(); 

    $('#repeat_period').change(function(){
        repeat_period();
    });
    
    $('.aiz-date-range').on('apply.daterangepicker', function(ev, picker) {
        var selectedDate = picker.startDate.format('YYYY-MM-DD');
        var endDate = picker.endDate.format('YYYY-MM-DD');
      //  $(this).val(selectedDate); // If needed
    // Update visible input with selected range
        $(this).val(endDate);

       // $('#session_end_date').val(endDate);
        $('#until_date').val(endDate); // Applies the end date to the until_date input

       // $('#session_end_date').val(selectedDate);

        // $('#session_start_time, .start_time').val('09:00 AM');
        // $('#session_end_time, .end_time').val('05:00 PM');
    });
    
});

$(document).ready(function() {
    $('#estimated_duration_num, #estimated_duration_unit, #session_start_time').on('input', function() {
        let durationNum = parseInt($('#estimated_duration_num').val());
        let durationUnit = $('#estimated_duration_unit').val();
        let sessionStartTime = $('#session_start_time').val(); // e.g., "9:00 AM"

        if (!durationNum || !sessionStartTime) return;

        // Parse "9:00 AM" (or "9:00 AM YYYY-MM-DD" if you add dates)
        let timeParts = sessionStartTime.match(/(\d+):(\d+)\s?(AM|PM)/i);
        if (!timeParts) return;

        let hours = parseInt(timeParts[1]);
        let minutes = parseInt(timeParts[2]);
        let period = timeParts[3].toUpperCase();

        // Convert to 24-hour time
        if (period === 'PM' && hours < 12) hours += 12;
        if (period === 'AM' && hours === 12) hours = 0;

        // Create Date object for today
        let date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes);
        date.setSeconds(0);

        // Add based on durationUnit
        if (durationUnit === 'HOURS') {
            date.setHours(date.getHours() + durationNum);
        } else if (durationUnit === 'MINUTES') {
            date.setMinutes(date.getMinutes() + durationNum);
        } else if (durationUnit === 'DAYS') {
            date.setDate(date.getDate() + durationNum);
        }

        // Format to 12-hour with AM/PM
        let newHours = date.getHours();
        let newMinutes = date.getMinutes();
        let newPeriod = newHours >= 12 ? 'PM' : 'AM';

        newHours = newHours % 12;
        newHours = newHours ? newHours : 12; // Handle midnight
        newMinutes = newMinutes < 10 ? '0' + newMinutes : newMinutes;

        let formattedTime = newHours + ':' + newMinutes + ' ' + newPeriod;

        // Optional: include date
        let month = date.getMonth() + 1;
        let day = date.getDate();
        let year = date.getFullYear();
        let formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        
        
        let fullFormatted = formattedTime;
        
        $('#session_end_time').val(fullFormatted);
        $('#session_end_date').val(formattedDate);
    });
});     
$(document).ready(function() {
        $('.calendar-icon-start').on('click', function () {
            
        const startdate = $('#session_start_date').data('daterangepicker');
        if (!startdate.isShowing) {
            $('#session_start_date').focus();
        }
     //   $('#session_start_date').focus(); // focus input
       // $('#session_end_date').focus(); // focus input
        
    });
          $('.calendar-icon-end').on('click', function () {
        const enddate = $('#session_end_date').data('daterangepicker');
        if (!enddate.isShowing) {
            $('#session_end_date').focus();
        }
     //   $('#session_start_date').focus(); // focus input
       // $('#session_end_date').focus(); // focus input
        
    });
    $('.calendar-icon-util').on('click', function () {
        const enddate = $('#until_date').data('daterangepicker');
        if (!enddate.isShowing) {
            $('#until_date').focus();
        }
     //   $('#session_start_date').focus(); // focus input
       // $('#session_end_date').focus(); // focus input
        
    });
});   
 $(document).ready(function () {
  // Initialize flatpickr and save instance in a variable
  const startpickr = flatpickr("#session_start_time", {
  enableTime: true,
  noCalendar: true,
  dateFormat: "h:i K",     // Format shown to user
  time_24hr: false,        // 12-hour format
    // Set default time
});

  // On icon click, open the picker ONLY if it's not already open
  $('.time-icon-start').on('click', function () {
    if (!startpickr.isOpen) {
      startpickr.open();
    }
    // Focus input as well, optional
    $('#session_start_time').focus();
  });
  const endpickr = flatpickr("#session_end_time", {
  enableTime: true,
  noCalendar: true,
  dateFormat: "h:i K",     // Format shown to user
  time_24hr: false,        // 12-hour format
   // Set default time
});
  // On icon click, open the picker ONLY if it's not already open
  $('.time-icon-end').on('click', function () {
    if (!endpickr.isOpen) {
      endpickr.open();
    }
    // Focus input as well, optional
    $('#session_end_time').focus();
  });
});


function updateEndDateTime() {
    
    let durationNum = parseInt($('#estimated_duration_num').val());
    let durationUnit = $('#estimated_duration_unit').val();
    let sessionStartTime = $('#session_start_time').val(); // e.g., "9:00 AM"
    let sessionStartDate = $('#session_start_date').val(); // e.g., "2025-08-10"
    
    if (!durationNum || !sessionStartTime || !sessionStartDate) return;
    
    // Parse time: "9:00 AM"
    let timeParts = sessionStartTime.match(/(\d+):(\d+)\s?(AM|PM)/i);
    if (!timeParts) return;

    let hours = parseInt(timeParts[1]);
    let minutes = parseInt(timeParts[2]);
    let period = timeParts[3].toUpperCase();

    if (period === 'PM' && hours < 12) hours += 12;
    if (period === 'AM' && hours === 12) hours = 0;

    // Create Date object with both date and time
    let date = new Date(`${sessionStartDate}T00:00:00`);
    date.setHours(hours);
    date.setMinutes(minutes);
    date.setSeconds(0);

    // Add duration
    if (durationUnit === 'HOURS') {
        date.setHours(date.getHours() + durationNum);
    } else if (durationUnit === 'MINUTES') {
        date.setMinutes(date.getMinutes() + durationNum);
    } else if (durationUnit === 'DAYS') {
        date.setDate(date.getDate() + durationNum);
    }

    // Format time to 12-hour AM/PM
    let newHours = date.getHours();
    let newMinutes = date.getMinutes();
    let newPeriod = newHours >= 12 ? 'PM' : 'AM';

    newHours = newHours % 12;
    newHours = newHours ? newHours : 12;
    newMinutes = newMinutes < 10 ? '0' + newMinutes : newMinutes;

    let formattedTime = newHours + ':' + newMinutes + ' ' + newPeriod;

    // Format date to yyyy-mm-dd
    let month = date.getMonth() + 1;
    let day = date.getDate();
    let year = date.getFullYear();
    let formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    
    // Update inputs
    $('#session_end_time').val(formattedTime);
    $('#session_end_date').val(formattedDate);
    $('#session_end_date').data('daterangepicker').setStartDate(formattedDate);
    $('#session_end_date').data('daterangepicker').setEndDate(formattedDate);
}


$(document).ready(function () {
    // When duration, time, or unit changes
    $('#estimated_duration_num, #estimated_duration_unit, #session_start_time').on('input change', updateEndDateTime);

    // When session_start_date changes via AIZ datepicker
    $(document).on('apply.daterangepicker', '#session_start_date', updateEndDateTime);

    // Also fallback for manual input
    $('#session_start_date').on('change input blur', updateEndDateTime);
});


</script>

<script>
    $(document).ready(function () {
        if ($('.aiz-date-range').length > 0) {
            $('.aiz-date-range').each(function () {
                // reinitialize the date picker with fresh settings if needed
                var $input = $(this);
                $input.daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    minDate: $input.data('min-date'),
                    locale: {
                        format: 'YYYY-MM-DD'
                    }
                });
            });
        }
    });
</script>

@endsection
