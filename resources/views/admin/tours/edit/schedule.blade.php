<div class="card">
    <form class="needs-validation" novalidate action="{{ route('admin.tour.schedule_update', $data->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3 class="card-title mb-0">Scheduling</h3>
                <div>
                    <button type="button" id="add-schedule" class="btn btn-sm btn-success">
                        + Add Schedule
                    </button>
                    <a href="{{ route('admin.tour.edit.schedule-calendar', $data->id) }}" class="btn btn-sm btn-success">
                        Schedule Calendar
                    </a>
                </div>
            </div>
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

            <div class="accordion" id="scheduleAccordion">
                @foreach($data->schedules as $index => $schedule)
                <div class="card mb-3 schedule-card" data-index="{{ $index }}">
                    <div class="card-primary" id="heading{{ $index }}" style="background-color:#343a41; cursor: pointer; padding-right:15px;">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{ $index }}" aria-expanded="true" aria-controls="collapse{{ $index }}" style="color:#fff;">
                                    Schedule #{{ $index+1 }}
                                </button>
                            </h5>
                            @if ($index > 0)
                            <button type="button" class="btn btn-danger btn-sm remove-schedule">Remove</button>
                            @endif
                        </div>
                    </div>

                    <div id="collapse{{ $index }}" class="card-body collapse {{ $index==0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-parent="#scheduleAccordion">

                            <div class="row mb-4">
                                <label for="minimum_notice_num_{{ $index }}" class="form-label col-lg-2">Minimum notice *</label>
                                <div class="col-lg-3">
                                    <input type="text" name="schedules[{{ $index }}][minimum_notice_num]" id="minimum_notice_num_{{ $index }}" 
                                    value="{{ old("schedules.$index.minimum_notice_num", $schedule?->minimum_notice_num) }}"
                                        class="form-control " placeholder="Before session start time">
                                    @if($errors->has("schedules.$index.minimum_notice_num"))
                                        <small class="form-text text-danger">{{ $errors->first("schedules.$index.minimum_notice_num") }}</small>
                                    @else
                                        @error('minimum_notice_num')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-lg-3">
                                    <select class="form-control minimum_notice_unit" name="schedules[{{ $index }}][minimum_notice_unit]" id="minimum_notice_unit_{{ $index }}">
                                        <option {{ old("schedules.$index.minimum_notice_unit", $schedule?->minimum_notice_unit) === 'MINUTES' ? 'selected' : '' }} value="MINUTES">Minutes</option>
                                        <option {{ old("schedules.$index.minimum_notice_unit", $schedule?->minimum_notice_unit) === 'HOURS' ? 'selected' : '' }} value="HOURS">Hours</option>
                                    </select>
                                    @if($errors->has("schedules.$index.minimum_notice_unit"))
                                        <small class="form-text text-danger">{{ $errors->first("schedules.$index.minimum_notice_unit") }}</small>
                                    @else
                                        @error('minimum_notice_unit')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    @endif
                                </div>
                                
                            </div>

                            <div class="row mb-4">
                                <label for="estimated_duration_num_{{ $index }}" class="form-label col-lg-2">Estimated duration *</label>
                                <div class="col-lg-3">
                                    <input type="text" name="schedules[{{ $index }}][estimated_duration_num]" id="estimated_duration_num_{{ $index }}" 
                                    value="{{ old("schedules.$index.estimated_duration_num", $schedule?->estimated_duration_num) }}"
                                        class="form-control " placeholder="Session time">
                                    @if($errors->has("schedules.$index.estimated_duration_num"))
                                        <small class="form-text text-danger">{{ $errors->first("schedules.$index.estimated_duration_num") }}</small>
                                    @else
                                        @error('estimated_duration_num')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-lg-3">
                                    <select class="form-control estimated_duration_unit" name="schedules[{{ $index }}][estimated_duration_unit]" id="estimated_duration_unit_{{ $index }}">
                                        <option {{ old("schedules.$index.estimated_duration_unit", $schedule?->estimated_duration_unit) === 'MINUTES' ? 'selected' : '' }} value="MINUTES">Minutes</option>
                                        <option {{ old("schedules.$index.estimated_duration_unit", $schedule?->estimated_duration_unit) === 'HOURS' ? 'selected' : '' }} value="HOURS">Hours</option>
                                        <option {{ old("schedules.$index.estimated_duration_unit", $schedule?->estimated_duration_unit) === 'DAYS' ? 'selected' : '' }} value="DAYS">Days</option>
                                    </select>
                                    @if($errors->has("schedules.$index.estimated_duration_unit"))
                                        <small class="form-text text-danger">{{ $errors->first("schedules.$index.estimated_duration_unit") }}</small>
                                    @else
                                        @error('mestimated_duration_unit')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="row">
                                    <label for="session_start_date_{{ $index }}" class="form-label col-lg-2">Next available session *</label>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-prepend" >
                                                <span class="input-group-text" id="basic-addon-from" style="width:70px;">Form</span>
                                            </div>
                                            <input type="text" placeholder="Date" name="schedules[{{ $index }}][session_start_date]" id="session_start_date_{{ $index }}" 
                                            value="{{ old("schedules.$index.session_start_date", $schedule?->session_start_date) }}" class="form-control aiz-date-range" data-single="true" 
                                            data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                                            <div class="input-group-prepend">
                                                <span class="input-group-text calendar-icon-start" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                            </div>                       
                                        </div>
                                        @if($errors->has("schedules.$index.session_start_date"))
                                            <small class="form-text text-danger">{{ $errors->first("schedules.$index.session_start_date") }}</small>
                                        @else
                                            @error('session_start_date')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        @endif
                                    </div>
                                    <div class="col-lg-3 not-all-date">
                                        <div class="input-group">
                                            <input type="text" placeholder="Time" name="schedules[{{ $index }}][session_start_time]" id="session_start_time_{{ $index }}" 
                                            value="{{ old("schedules.$index.session_start_time", $schedule?->session_start_time) }}" class="form-control aiz-time-picker"> 
                                            <div class="input-group-prepend">
                                                <span class="input-group-text time-icon-start" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                            </div>                       
                                        </div>
                                        @if($errors->has("schedules.$index.session_start_time"))
                                            <small class="form-text text-danger">{{ $errors->first("schedules.$index.session_start_time") }}</small>
                                        @else
                                            @error('session_start_time')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="session_end_date_{{ $index }}" class="form-label col-lg-2"></label>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon-from"  style="width:70px;">To</span>
                                            </div>
                                            <input type="text" placeholder="Date" name="schedules[{{ $index }}][session_end_date]" id="session_end_date_{{ $index }}" 
                                            value="{{ old("schedules.$index.session_end_date", $schedule?->session_end_date) }}" class="form-control aiz-date-range" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                                            <div class="input-group-prepend">
                                                <span class="input-group-text calendar-icon-end" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                            </div>                       
                                        </div>
                                        @if($errors->has("schedules.$index.session_end_date"))
                                            <small class="form-text text-danger">{{ $errors->first("schedules.$index.session_end_date") }}</small>
                                        @else
                                            @error('session_end_date')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        @endif
                                    </div>
                                    <div class="col-lg-3 not-all-date">
                                        <div class="input-group">
                                            <input type="text" placeholder="Time" name="schedules[{{ $index }}][session_end_time]" id="session_end_time_{{ $index }}" 
                                            value="{{ old("schedules.$index.session_end_time", $schedule?->session_end_time) }}" class="form-control aiz-time-picker"> 
                                            <div class="input-group-prepend">
                                                <span class="input-group-text time-icon-end" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                            </div>                       
                                        </div>
                                        @if($errors->has("schedules.$index.session_end_time"))
                                            <small class="form-text text-danger">{{ $errors->first("schedules.$index.session_end_time") }}</small>
                                        @else
                                            @error('session_end_time')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-5">
                                    <label for="session_start_date_{{ $index }}" class="form-label col-lg-2"></label>
                                    <div class="col-lg-6">
                                        <label>
                                            <input type="checkbox" class="sesion_all_day" name="schedules[{{ $index }}][sesion_all_day]" id="sesion_all_day_{{ $index }}" value="1" {{ old("schedules.$index.sesion_all_day", $schedule?->sesion_all_day) ? 'checked' : '' }} style="width:17px;height:17px"> All day
                                        </label>
                                    </div>                    
                                </div>
                            </div>

                            <div class="row mb-5">
                                <label for="repeat_period_{{ $index }}" class="form-label col-lg-2">Repeat *</label>
                                <div class="col-lg-3">
                                    <select class="form-control repeat_period" name="schedules[{{ $index }}][repeat_period]" id="repeat_period_{{ $index }}">
                                        <option {{ old("schedules.$index.repeat_period", $schedule?->repeat_period) === 'NONE' ? 'selected' : '' }} value="NONE">Do not repeat</option>
                                        <option {{ old("schedules.$index.repeat_period", $schedule?->repeat_period) === 'MINUTELY' ? 'selected' : '' }} value="MINUTELY">Repeat minute-by-minute</option>
                                        <option {{ old("schedules.$index.repeat_period", $schedule?->repeat_period) === 'HOURLY' ? 'selected' : '' }} value="HOURLY">Repeat hourly</option>
                                        <option {{ old("schedules.$index.repeat_period", $schedule?->repeat_period) === 'DAILY' ? 'selected' : '' }} value="DAILY">Repeat daily</option>
                                        <option {{ old("schedules.$index.repeat_period", $schedule?->repeat_period) === 'WEEKLY' ? 'selected' : '' }} value="WEEKLY">Repeat weekly</option>
                                        <option {{ old("schedules.$index.repeat_period", $schedule?->repeat_period) === 'MONTHLY' ? 'selected' : '' }} value="MONTHLY">Repeat monthly</option>
                                        <option {{ old("schedules.$index.repeat_period", $schedule?->repeat_period) === 'YEARLY' ? 'selected' : '' }} value="YEARLY">Repeat yearly</option>
                                    </select>
                                    @if($errors->has("schedules.$index.repeat_period"))
                                        <small class="form-text text-danger">{{ $errors->first("schedules.$index.repeat_period") }}</small>
                                    @else
                                        @error('repeat_period')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    @endif
                                </div>

                                <div class="col-lg-3 d-none not-repeat-period not-repeat-period3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon-from"  style="width:70px;">Every</span>
                                        </div>
                                        <select class="form-control repeat_period_unit" name="schedules[{{ $index }}][repeat_period_unit]" id="repeat_period_unit_{{ $index }}">
                                            @for ($i=1; $i<=31; $i++)
                                            <option {{ old("schedules.$index.repeat_period_unit", $schedule?->repeat_period_unit) == $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }}</option>                            
                                            @endfor
                                        </select>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text basic-repeat_period_unit" id="basic-repeat_period_unit_{{ $index }}">minutes</span>
                                        </div>                       
                                    </div>
                                    @if($errors->has("schedules.$index.repeat_period_unit"))
                                        <small class="form-text text-danger">{{ $errors->first("schedules.$index.repeat_period_unit") }}</small>
                                    @else
                                        @error('repeat_period_unit')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-lg-4 d-none not-repeat-period not-repeat-period3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon-until">Until</span>
                                        </div>
                                        <input type="text" placeholder="Date" name="schedules[{{ $index }}][until_date]" id="until_date_{{ $index }}" 
                                        value="{{ old("schedules.$index.until_date", $schedule?->until_date) }}" 
                                        class="form-control aiz-date-range" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}"> 
                                        <div class="input-group-prepend">
                                            <span class="input-group-text calendar-icon-util" id="basic-addon-from"><i class="fa fa-calendar"></i></span>
                                        </div>                       
                                    </div>
                                    @if($errors->has("schedules.$index.until_date"))
                                        <small class="form-text text-danger">{{ $errors->first("schedules.$index.until_date") }}</small>
                                    @else
                                        @error('until_date')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            @php
                            $i = 0;
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            @endphp

                            @foreach ($days as $day)
                            @php
                            $repeat = [];
                            $repeat = $schedule->repeats?->firstWhere('day', $day);
                            @endphp
                            <div class="row mb-3 d-none not-repeat-period not-repeat-period2">
                                <input type="hidden" name="schedules[{{ $index }}][Repeat][{{ $i }}][day]" id="Repeat_{{ $index }}_{{ $i }}_day" value="{{ $day }}" />
                                <label for="Repeat_{{ $index }}_{{ $i }}_num" class="form-label col-lg-2">{{ $day }}</label>
                                <div class="col-lg-1">
                                    <input type="checkbox" {{ old("schedules.$index.Repeat.$i.num", isset($repeat['day'])) ? 'checked' : '' }} name="schedules[{{ $index }}][Repeat][{{ $i }}][num]" id="Repeat_{{ $index }}_{{ $i }}_num" style="width:17px;height:17px">
                                </div> 
                                <div class="col-lg-3 not-repeat-weekly">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon-until">From</span>
                                        </div>
                                        <input type="text" placeholder="Time" name="schedules[{{ $index }}][Repeat][{{ $i }}][start_time]" id="Repeat_{{ $index }}_{{ $i }}_start_time" 
                                        class="form-control start_time aiz-time-picker" value="{{ old("schedules.$index.Repeat.$i.start_time", isset($repeat) ? $repeat['start_time'] : '') }}" > 
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
                                        <input type="text" placeholder="Time" name="schedules[{{ $index }}][Repeat][{{ $i }}][end_time]" id="Repeat_{{ $index }}_{{ $i }}_end_time" 
                                        class="form-control end_time aiz-time-picker" value="{{ old("schedules.$index.Repeat.$i.end_time", isset($repeat) ? $repeat['end_time'] : '') }}" > 
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
                </div>
                @endforeach
            </div>

        </div>
        <div class="card-footer" style="display:block">
            <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
            <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
            <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.location', encrypt($data->id)) }}" class="btn btn-primary">Next</a>
        </div>
    </div>
    </form>
</div>

{{-- Hidden template for new schedule (use __INDEX__ placeholder) --}}
<template id="schedule-template">
    <div class="card mb-3 schedule-card" data-index="__INDEX__">

        <div class="card-primary" id="heading__INDEX__" style="background-color:#343a41; cursor: pointer; padding-right:15px;">
            <div class="d-flex justify-content-between align-items-center w-100">


        <!-- <div class="card-header d-flex justify-content-between"  style="background-color:#343a41; cursor: pointer; padding-right:15px;"> -->
            <h5 class="mb-0">
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse__INDEX__" aria-expanded="true" style="color:#fff;">Schedule #__INDEX__</button>
            </h5>
            <button type="button" class="btn btn-danger btn-sm remove-schedule">Remove</button>
        </div>
    </div>
        <div id="collapse__INDEX__" class="collapse" data-parent="#scheduleAccordion">
            <div class="card-body">

   <!--              <div class="row mb-4">

                    <label class="form-label col-lg-2">Schedule Price</label>
                    <div class="col-lg-3">
                        <input type="text" name="schedules[__INDEX__][schedule_price]" id="schedule_price___INDEX__" class="form-control " placeholder="Schedule Price">

                    </div>

                </div> -->


                <div class="row mb-4">
                    <label class="form-label col-lg-2">Minimum notice *</label>
                    <div class="col-lg-3">
                        <input type="text" name="schedules[__INDEX__][minimum_notice_num]" id="minimum_notice_num___INDEX__" class="form-control" placeholder="Before session start time">
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control minimum_notice_unit" name="schedules[__INDEX__][minimum_notice_unit]" id="minimum_notice_unit___INDEX__">
                            <option value="MINUTES">Minutes</option>
                            <option value="HOURS">Hours</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="form-label col-lg-2">Estimated duration *</label>
                    <div class="col-lg-3">
                        <input type="text" name="schedules[__INDEX__][estimated_duration_num]" id="estimated_duration_num___INDEX__" class="form-control" placeholder="Session time">
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control estimated_duration_unit" name="schedules[__INDEX__][estimated_duration_unit]" id="estimated_duration_unit___INDEX__">
                            <option value="MINUTES">Minutes</option>
                            <option value="HOURS">Hours</option>
                            <option value="DAYS">Days</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="row">
                        <label class="form-label col-lg-2">Next available session *</label>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-prepend" >
                                    <span class="input-group-text" style="width:70px;">Form</span>
                                </div>
                                <input type="text" name="schedules[__INDEX__][session_start_date]" id="session_start_date___INDEX__" class="form-control aiz-date-range" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}" placeholder="Date">
                                <div class="input-group-prepend">
                                    <span class="input-group-text calendar-icon-start"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 not-all-date">
                            <div class="input-group">
                                <input type="text" name="schedules[__INDEX__][session_start_time]" id="session_start_time___INDEX__" class="form-control aiz-time-picker" placeholder="Time">
                                <div class="input-group-prepend">
                                    <span class="input-group-text time-icon-start"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="form-label col-lg-2"></label>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="width:70px;">To</span>
                                </div>
                                <input type="text" name="schedules[__INDEX__][session_end_date]" id="session_end_date___INDEX__" class="form-control aiz-date-range" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}" placeholder="Date">
                                <div class="input-group-prepend">
                                    <span class="input-group-text calendar-icon-end"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 not-all-date">
                            <div class="input-group">
                                <input type="text" name="schedules[__INDEX__][session_end_time]" id="session_end_time___INDEX__" class="form-control aiz-time-picker" placeholder="Time">
                                <div class="input-group-prepend">
                                    <span class="input-group-text time-icon-end"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <label class="form-label col-lg-2"></label>
                        <div class="col-lg-6">
                            <label><input type="checkbox" class="sesion_all_day" name="schedules[__INDEX__][sesion_all_day]" id="sesion_all_day___INDEX__" value="1" style="width:17px;height:17px"> All day</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <label class="form-label col-lg-2">Repeat *</label>
                    <div class="col-lg-3">
                        <select class="form-control repeat_period" name="schedules[__INDEX__][repeat_period]" id="repeat_period___INDEX__">
                            <option value="NONE">Do not repeat</option>
                            <option value="MINUTELY">Repeat minute-by-minute</option>
                            <option value="HOURLY">Repeat hourly</option>
                            <option value="DAILY">Repeat daily</option>
                            <option value="WEEKLY">Repeat weekly</option>
                            <option value="MONTHLY">Repeat monthly</option>
                            <option value="YEARLY">Repeat yearly</option>
                        </select>
                    </div>

                    <div class="col-lg-3 d-none not-repeat-period not-repeat-period3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="width:70px;">Every</span>
                            </div>
                            <select class="form-control repeat_period_unit" name="schedules[__INDEX__][repeat_period_unit]" id="repeat_period_unit___INDEX__">
                                @for ($i=1; $i<=31; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <div class="input-group-prepend">
                                <span class="input-group-text basic-repeat_period_unit">minutes</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-none not-repeat-period not-repeat-period3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon-until">Until</span>
                            </div>
                            <input type="text" placeholder="Date" name="schedules[__INDEX__][until_date]" id="until_date___INDEX__" class="form-control aiz-date-range" data-single="true" data-show-dropdown="true" data-min-date="{{ get_max_date() }}">
                            <div class="input-group-prepend">
                                <span class="input-group-text calendar-icon-util"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                $i = 0;
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                @endphp

                @foreach ($days as $day)
                <div class="row mb-3 d-none not-repeat-period not-repeat-period2">
                    <input type="hidden" name="schedules[__INDEX__][Repeat][{{ $i }}][day]" value="{{ $day }}" />
                    <label class="form-label col-lg-2">{{ $day }}</label>
                    <div class="col-lg-1">
                        <input type="checkbox" name="schedules[__INDEX__][Repeat][{{ $i }}][num]" id="Repeat___INDEX___{{ $i }}_num" style="width:17px;height:17px">
                    </div>
                    <div class="col-lg-3 not-repeat-weekly">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">From</span>
                            </div>
                            <input type="text" placeholder="Time" name="schedules[__INDEX__][Repeat][{{ $i }}][start_time]" id="Repeat___INDEX___{{ $i }}_start_time" class="form-control start_time aiz-time-picker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 not-repeat-weekly">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">To</span>
                            </div>
                            <input type="text" placeholder="Time" name="schedules[__INDEX__][Repeat][{{ $i }}][end_time]" id="Repeat___INDEX___{{ $i }}_end_time" class="form-control end_time aiz-time-picker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                @php $i++; @endphp
                @endforeach

            </div>
        </div>
    </div>
</template>

@section('js')
@parent

<!-- Flatpickr CSS -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" crossorigin="anonymous"> -->

<!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
<script>
    function setValueTimeLocal($card, value) {
        const $unitLabel = $card.find('.basic-repeat_period_unit');
        if (! $unitLabel.length) return;
        if (value == 'MINUTELY') $unitLabel.text('minutes');
        else if (value == 'HOURLY') $unitLabel.text('hours');
        else if (value == 'DAILY') $unitLabel.text('days');
        else if (value == 'WEEKLY') $unitLabel.text('weeks');
        else if (value == 'MONTHLY') $unitLabel.text('months');
        else if (value == 'YEARLY') $unitLabel.text('years');
    }

    function repeat_period_local($card) {
        const val = $card.find('.repeat_period').val();
        setValueTimeLocal($card, val);

        if (val == 'NONE') {
            $card.find('.not-repeat-period').addClass('d-none');
        } else if (val == 'MONTHLY' || val == 'YEARLY' || val == 'DAILY') {
            $card.find('.not-repeat-period2').addClass('d-none');
            $card.find('.not-repeat-period3').removeClass('d-none');
        } else {
            if (val == 'MINUTELY' || val == 'HOURLY' || val == 'WEEKLY')
                $card.find('.not-repeat-period').removeClass('d-none');

            if (val == 'WEEKLY')
                $card.find('.not-repeat-weekly').addClass('d-none');
            else
                $card.find('.not-repeat-weekly').removeClass('d-none');
        }
    }

    function initDateRangeFor($card) {
        $card.find('.aiz-date-range').each(function() {
            var $input = $(this);
            if ($input.data('daterangepicker')) {
                $input.data('daterangepicker').remove();
            }
            $input.daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minDate: $input.data('min-date'),
                locale: { format: 'YYYY-MM-DD' }
            });
        });
    }

    function initFlatpickrFor($card) {
        // start time
        // $card.find('.aiz-time-picker').each(function(){
        //     if (this._flatpickr) this._flatpickr.destroy();
        //     const inst = flatpickr(this, {
        //         enableTime: true,
        //         noCalendar: true,
        //         dateFormat: "h:i K",
        //         time_24hr: false
        //     });
        //     const $icon = $(this).closest('.input-group').find('.time-icon-start');
        //     $icon.off('click').on('click', () => {
        //         if (!inst.isOpen) inst.open();
        //         $(this).focus();
        //     });
        // });

        // end time
        // $card.find('.aiz-time-picker').each(function(){
        //     if (this._flatpickr) this._flatpickr.destroy();
        //     const inst = flatpickr(this, {
        //         enableTime: true,
        //         noCalendar: true,
        //         dateFormat: "h:i K",
        //         time_24hr: false
        //     });
        //     const $icon = $(this).closest('.input-group').find('.time-icon-end');
        //     $icon.off('click').on('click', () => {
        //         if (!inst.isOpen) inst.open();
        //         $(this).focus();
        //     });
        // });
    }

    function updateEndDateTimeLocal($card) {
        const idx = $card.data('index');
        const durationNum = parseInt($card.find('#estimated_duration_num_' + idx).val());
        const durationUnit = $card.find('#estimated_duration_unit_' + idx).val();
        const sessionStartTime = $card.find('#session_start_time_' + idx).val();
        const sessionStartDate = $card.find('#session_start_date_' + idx).val();

        if (!durationNum || !sessionStartTime || !sessionStartDate) return;

        const timeParts = sessionStartTime.match(/(\d+):(\d+)\s?(AM|PM)/i);
        if (!timeParts) return;

        let hours = parseInt(timeParts[1]);
        let minutes = parseInt(timeParts[2]);
        let period = timeParts[3].toUpperCase();

        if (period === 'PM' && hours < 12) hours += 12;
        if (period === 'AM' && hours === 12) hours = 0;

        let date = new Date(sessionStartDate + 'T00:00:00');
        date.setHours(hours);
        date.setMinutes(minutes);
        date.setSeconds(0);

        if (durationUnit === 'HOURS') date.setHours(date.getHours() + durationNum);
        else if (durationUnit === 'MINUTES') date.setMinutes(date.getMinutes() + durationNum);
        else if (durationUnit === 'DAYS') date.setDate(date.getDate() + durationNum);

        let newHours = date.getHours();
        let newMinutes = date.getMinutes();
        let newPeriod = newHours >= 12 ? 'PM' : 'AM';

        newHours = newHours % 12 || 12;
        newMinutes = newMinutes < 10 ? '0' + newMinutes : newMinutes;

        const formattedTime = newHours + ':' + newMinutes + ' ' + newPeriod;
        const formattedDate = date.toISOString().slice(0,10);

        $card.find('#session_end_time_' + idx).val(formattedTime);
        $card.find('#session_end_date_' + idx).val(formattedDate);

        const dr = $card.find('#session_end_date_' + idx).data('daterangepicker');
        if (dr) dr.setStartDate(formattedDate).setEndDate(formattedDate);
    }

    function initScheduleCard($card) {
        const idx = $card.data('index');
        if (typeof idx === 'undefined') return;

        initDateRangeFor($card);
        initFlatpickrFor($card);

        $card.find('.repeat_period').off('change').on('change', () => repeat_period_local($card));
        repeat_period_local($card);

        $card.find('.sesion_all_day').off('click').on('click', () => $card.find('.not-all-date').toggleClass('hidden'));

        $card.find('.calendar-icon-start').off('click').on('click', () => {
            const input = $card.find('#session_start_date_' + idx);
            const dr = input.data('daterangepicker');
            if (dr && !dr.isShowing) input.focus();
        });

        $card.find('.calendar-icon-end').off('click').on('click', () => {
            const input = $card.find('#session_end_date_' + idx);
            const dr = input.data('daterangepicker');
            if (dr && !dr.isShowing) input.focus();
        });

        $card.find('#estimated_duration_num_' + idx + ', #estimated_duration_unit_' + idx + ', #session_start_time_' + idx)
            .off('input change').on('input change', () => updateEndDateTimeLocal($card));

        $card.find('#session_start_date_' + idx).off('change input blur')
            .on('change input blur', () => updateEndDateTimeLocal($card));
    }

    $(document).ready(function(){
        // init existing cards
        $('.schedule-card').each(function(){ initScheduleCard($(this)); });

        // add new card
        $('#add-schedule').on('click', function(){
            const index = $('.schedule-card').length + 1;
            let tpl = $('#schedule-template').html().replace(/__INDEX__/g, index);
            $('#scheduleAccordion').append(tpl);
            // $('#schedule .aiz-time-picker').last().flatpickr({
            //     enableTime: true,
            //     noCalendar: true,
            //     dateFormat: "H:i",
            // });

                $('#scheduleAccordion .schedule-card:last .aiz-time-picker').each(function () {
                    var $this = $(this);
                    var minuteStep = $this.data("minute-step") || 5;
                    var defaultTime = $this.data("default") || "00:00";

                    $this.timepicker({
                        template: "dropdown",
                        minuteStep: minuteStep,
                        defaultTime: defaultTime,
                        icons: {
                            up: "las la-angle-up",
                            down: "las la-angle-down",
                        },
                        showInputs: false,
                    });
                });
            const $new = $('#scheduleAccordion .schedule-card').last();
            $new.attr('data-index', index);
            initScheduleCard($new);

              // 🔹 Expand it if inside accordion (Bootstrap example)
            $new.find('.collapse').collapse('show');

            // 🔹 Scroll into view smoothly
            $('html, body').animate({
                scrollTop: $new.offset().top - 100
            }, 500);

            // 🔹 Focus on first input inside new schedule
            $new.find('input, textarea, select').filter(':visible:first').focus();
        });

        // remove card
        $(document).on('click', '.remove-schedule', function(){
            if ($('.schedule-card').length > 1) {
                $(this).closest('.schedule-card').remove();
            } else {
                alert('At least one schedule is required.');
            }
        });
    });

    // fallback init for daterangepicker
    $(document).ready(function () {
        $('.aiz-date-range').each(function () {
            var $input = $(this);
            if (!$input.data('daterangepicker')) {
                $input.daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    minDate: $input.data('min-date'),
                    locale: { format: 'YYYY-MM-DD' }
                });
            }
        });
    });
</script>





@endsection
