<div class="card">
    <div class="card-info">
        <div class="card-header">
            <h3 class="card-title">Message Reminders</h3>            
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="list-unstyled">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="needs-validation" novalidate action="{{ route('admin.tour.reminders_update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="card-body">
                <!-- Emails -->
                <div class="row">                   
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <label for="IsPurchasedAsAGift" class="form-label col-2">Emails</label>
                            <div class="col-lg-10">
                                
                                @php
                                    $reminders = [1, 2, 3];
                                @endphp

                                @foreach ($reminders as $i)
                                    @php
                                        $reminderKey = "email{$i}_reminder";
                                        $showhideKey = "email{$i}reminder";
                                        $delayKey = "email{$i}_reminder_delay";
                                        $unitKey = "email{$i}_reminder_delayUnit";
                                        $textKey = "email{$i}_reminder_text";
                                    @endphp

                                    <div class="form-group">
                                        <label style="font-weight:400">
                                            <input type="checkbox" name="Meta[{{ $reminderKey }}]" id="{{ $reminderKey }}"
                                                {{ old("Meta.$reminderKey", !empty($metaData[$reminderKey])) ? 'checked' : '' }}
                                            /> Send a {{ ordinal($i) }} reminder
                                        </label>

                                        <div id="{{ $showhideKey }}" class="hidden">
                                            <div class="form-group" style="max-width: 600px;">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="{{ $delayKey }}"
                                                        name="Meta[{{ $delayKey }}]" placeholder="7"
                                                        value="{{ old("Meta.$delayKey", $metaData[$delayKey] ?? '') }}">

                                                    <select class="form-control" id="{{ $unitKey }}" name="Meta[{{ $unitKey }}]">
                                                        @foreach (['MINUTES', 'HOURS', 'DAYS'] as $unit)
                                                            <option value="{{ $unit }}"
                                                                {{ old("Meta.$unitKey", $metaData[$unitKey] ?? '') === $unit ? 'selected' : '' }}>
                                                                {{ strtolower($unit) }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <div class="input-group-append">
                                                        <span class="input-group-text">before the session start time</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <textarea name="Meta[{{ $textKey }}]" id="Meta[{{ $textKey }}]" class="form-control aiz-text-editor">
                                                {{ old("Meta.$textKey", $metaData[$textKey] ?? '') }}
                                            </textarea>
                                        </div>
                                    </div>


                                    @section('js')
                                    @parent
                                    <script>
                                    function {{ $reminderKey }}() {
                                        if ($('#{{ $reminderKey }}').is(':checked')) {
                                            $('#{{ $showhideKey }}').removeClass('hidden');
                                        } else {
                                            $('#{{ $showhideKey }}').addClass('hidden');
                                        }
                                    }
                                    {{ $reminderKey }}();
                                    $('#{{ $reminderKey }}').on('change', {{ $reminderKey }});
                                    </script>
                                    @endsection

                                @endforeach


                            </div>
                        </div>
                    </div>
                </div>
                <hr />
                <!-- SMS -->
                <div class="row">                   
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <label for="IsPurchasedAsAGift" class="form-label col-2">SMS</label>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="Meta[sms_reminder_customer]" id="sms_reminder_customer" {{ old('meta.sms_reminder_customer', !empty($metaData['sms_reminder_customer']) ? 'checked' : '') }} /> Send a reminder SMS to the customer
                                    </label>

                                    <div id="smsremindercustomer" class="form-group hidden" style="max-width: 600px;">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <input type="text" class="form-control" id="sms_reminder_delay" name="Meta[sms_reminder_delay]" placeholder="7" value="{{ old('meta.sms_reminder_delay', $metaData['sms_reminder_delay'] ?? '') }}">
                                            </div>
                                            <select class="form-control" id="sms_reminder_delayUnit" name="Meta[sms_reminder_delayUnit]">
                                                <option {{ (old('meta.sms_reminder_delayUnit', $metaData['sms_reminder_delayUnit'] ?? '')=='MINUTES') ? 'selected' : '' }} value="MINUTES">minutes</option>
                                                <option {{ (old('meta.sms_reminder_delayUnit', $metaData['sms_reminder_delayUnit'] ?? '')=='HOURS') ? 'selected' : '' }} value="HOURS">hours</option>
                                                <option {{ (old('meta.sms_reminder_delayUnit', $metaData['sms_reminder_delayUnit'] ?? '')=='DAYS') ? 'selected' : '' }} value="DAYS">days</option>
                                            </select>
                                            <div class="input-group-append">
                                                <span class="input-group-text">before the session start time</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer" style="display:block">                
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.message.notification', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.message.followup', encrypt($data->id)) }}" class="btn btn-primary">Next</a>   
            </div>
        </form>
    </div>
</div>

@section('js')
@parent
<script>
function sms_reminder_customer() {
    if ($('#sms_reminder_customer').is(':checked')) {
        $('#smsremindercustomer').removeClass('hidden');
    } else {
        $('#smsremindercustomer').addClass('hidden');
    }
}
sms_reminder_customer();
$('#sms_reminder_customer').on('change', sms_reminder_customer);

function email_notification() {
    if ($('#email_notification').is(':checked')) {
        $('#emailnotification').removeClass('hidden');
    } else {
        $('#emailnotification').addClass('hidden');
    }
}
email_notification();
$('#email_notification').on('change', email_notification);
</script>
@endsection