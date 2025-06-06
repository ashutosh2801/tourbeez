<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Scheduling</h3>            
        </div>
        <form class="needs-validation" novalidate action="{{ route('admin.tour.schedule_update', $data->id) }}" method="POST"
    enctype="multipart/form-data" autocomplete="off">
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
                    <label for="minimum_notice_num" class="form-label col-lg-2">Minimum notice</label>
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

                <div class="mb-4">
                    <div class="row">
                        <label for="session_start_date" class="form-label col-lg-2">Next available session *</label>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <div class="input-group-prepend" >
                                    <span class="input-group-text" id="basic-addon-from" style="width:70px;">Form</span>
                                </div>
                                <input type="text" placeholder="Date" name="session_start_date" id="session_start_date" 
                                value="{{ old('session_start_date', $schedule->session_start_date) }}" class="aiz-date-range form-control" data-single="true" 
                                data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
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
                                    <span class="input-group-text" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                </div>                       
                            </div>
                            @error('session_start_time')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="session_end_date" class="form-label col-lg-2"></label>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon-from"  style="width:70px;">To</span>
                                </div>
                                <input type="text" placeholder="Date" name="session_end_date" id="session_end_date" 
                                value="{{ old('session_end_date', $schedule->session_end_date) }}" class="aiz-date-range form-control" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
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
                                    <span class="input-group-text" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
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
                                <option {{ old('repeat_period_unit', $schedule->repeat_period_unit) === $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }}</option>                            
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
                    <div class="col-lg-3 d-none not-repeat-period not-repeat-period3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-until">Until</span>
                            </div>
                            <input type="text" placeholder="Date" name="until_date" id="until_date" 
                            value="{{ old('until_date', $schedule->until_date) }}" 
                            class="aiz-date-range form-control" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
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
        
        </form>
    </div>
</div>

@section('js')
@parent
<script>
function setValueTime( value ) {

    let step10 = step1 = '';
    for(i=10; i<91; i+=5) 
    step10+= `<option value="${i}">${i}</option>`;

    for(i=1; i<32; i++) 
    step1+= `<option value="${i}">${i}</option>`;

    $('#repeat_period_unit').html(step1);
    $('#repeat_period_unit').val( {{ old('num') ?? 1 }} );
    if( value == 'MINUTELY' ) {
        $('#repeat_period_unit').html(step10);
        $('#repeat_period_unit').val({{ old('num') ?? 10 }});
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
        $('.not-repeat-period2').addClass('d-none');

        if(repeat_period_val == 'DAILY')
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
        //console.log("Selected date:", selectedDate);
        $(this).val(selectedDate); // If needed

        $('#session_end_date').val(selectedDate);
        $('#session_start_time, .start_time').val('09:00 AM');
        $('#session_end_time, .end_time').val('05:00 PM');
    });
});
</script>
@endsection
