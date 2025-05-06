<div class="card">
    <div class="card-warnig">
        <div class="card-header">
            <h3 class="card-title">Payment Request</h3>            
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
        <form class="needs-validation" novalidate action="{{ route('admin.tour.followup_update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <!-- Emails -->
                <div class="row">                   
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <label for="IsPurchasedAsAGift" class="form-label col-2">Emails</label>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="payment_request_reminder" id="payment_request_reminder" {{ old('payment_request_reminder') || $data->detail?->payment_request_reminder ? 'checked' : '' }} />  Send a follow-up Review Request</label>
                                    <div id="paymentrequestreminder" class="hid den">
                                        <div class="form-group" style="max-width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <input type="text" class="form-control" id="payment_request_reminder_delay" name="payment_request_reminder_delay" placeholder="7" value="{{ old('payment_request_reminder_delay') ?? $data->detail?->email1_reminder_num }}">
                                                </div>
                                                <select class="form-control" id="payment_request_reminder_delayUnit" name="payment_request_reminder_delayUnit">
                                                    <option {{ (old('payment_request_reminder_delayUnit', $data->detail?->payment_request_reminder_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                    <option {{ (old('payment_request_reminder_delayUnit', $data->detail?->payment_request_reminder_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                    <option {{ (old('payment_request_reminder_delayUnit', $data->detail?->payment_request_reminder_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">before the session start time</span>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea name="payment_request_reminder_text" id="payment_request_reminder_text" class="form-control  aiz-text-editor" >{{ old('payment_request_reminder_text') }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="payment_request_reminder_req" id="payment_request_reminder_req" {{ old('payment_request_reminder_req') || $data->detail?->payment_request_reminder_req ? 'checked' : '' }} /> Send a follow-up Recommendation Request</label>
                                    <div id="paymentrequestreminderreq" class="hid den">
                                        <div class="form-group" style="max-width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <input type="text" class="form-control" id="payment_request_reminder_req_delay" name="payment_request_reminder_req_delay" placeholder="7" value="{{ old('payment_request_reminder_req_delay') ?? $data->detail?->payment_request_reminder_req_delay }}">
                                                </div>
                                                <select class="form-control" id="payment_request_reminder_req_delayUnit" name="payment_request_reminder_req_delayUnit">
                                                    <option {{ (old('payment_request_reminder_req_delayUnit', $data->detail?->payment_request_reminder_req_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                    <option {{ (old('payment_request_reminder_req_delayUnit', $data->detail?->payment_request_reminder_req_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                    <option {{ (old('payment_request_reminder_req_delayUnit', $data->detail?->payment_request_reminder_req_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">before the session start time</span>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea name="email2_reminder_text" id="email2_reminder_text" class="form-control  aiz-text-editor" >{{ old('email2_reminder_text') }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="email3_reminder" id="email3_reminder" {{ old('email3_reminder') || $data->detail?->email3_reminder ? 'checked' : '' }} /> Send a follow-up Coupon</label>
                                    <div id="email3reminder" class="hidden">
                                        <div class="form-group" style="max-width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <input type="text" class="form-control" id="email3_reminder_delay" name="email3_reminder_delay" placeholder="7" value="{{ old('email3_reminder_delay') ?? $data->detail?->email3_reminder_num }}">
                                                </div>
                                                <select class="form-control" id="email3_reminder_delayUnit" name="email3_reminder_delayUnit">
                                                    <option {{ (old('email3_reminder_delayUnit', $data->detail?->email3_reminder_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                    <option {{ (old('email3_reminder_delayUnit', $data->detail?->email3_reminder_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                    <option {{ (old('email3_reminder_delayUnit', $data->detail?->email3_reminder_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">before the session start time</span>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea name="email3_reminder_text" id="email3_reminder_text" class="form-control  aiz-text-editor" >{{ old('email3_reminder_text') }}</textarea>
                                    </div>
                                </div>
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
                                    <label style="font-weight:400"><input type="checkbox" name="sms_reminder_customer" id="sms_reminder_customer" {{ old('sms_reminder_customer') || $data->detail?->sms_reminder_customer ? 'checked' : '' }} /> Send a follow up SMS to the customer
                                    </label>

                                    <div class="form-group" style="max-width: 600px;">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                            <input type="text" class="form-control" id="sms_reminder_delay" name="sms_reminder_delay" placeholder="7" value="{{ old('sms_reminder_delay') ?? $data->detail?->sms_reminder_num }}">
                                            </div>
                                            <select class="form-control" id="sms_reminder_delayUnit" name="sms_reminder_delayUnit">
                                                <option {{ (old('sms_reminder_delayUnit', $data->detail?->sms_reminder_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                <option {{ (old('sms_reminder_delayUnit', $data->detail?->sms_reminder_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                <option {{ (old('sms_reminder_delayUnit', $data->detail?->sms_reminder_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
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

            <div class="card-footer">
                <button type="submit" id="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.tour.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@section('js')
@parent
<script>
$('#pricing').on('change', function() {
    if ($(this).val() == 'PER_PERSON') {
        $('.quantity_used').removeClass('hidden');
    } else {
        $('.quantity_used').addClass('hidden');
        $('.priceOptionsWra').html('');
        priceOptionCount = 1;
    }
});

$('#IsTermsAndConditions').on('click', function() {
    if ($(this).is(':checked')) {
        $('#terms_and_conditions_wra').removeClass('hidden');
    } else {
        $('#terms_and_conditions_wra').addClass('hidden');
    }
});
$('#IsPurchasedAsAGift').on('click', function() {
    if ($(this).is(':checked')) {
        $('#IsPurchasedAsAGift_show').removeClass('hidden');
    } else {
        $('#IsPurchasedAsAGift_show').addClass('hidden');
    }
});
$('#IsExpiryDays').on('click', function() {
    if ($(this).is(':checked')) {
        $('#expiry_days_wra').removeClass('hidden');
    } else {
        $('#expiry_days_wra').addClass('hidden');
    }
});
$('#IsExpiryDate').on('click', function() {
    if ($(this).is(':checked')) {
        $('#expiry_date_wra').removeClass('hidden');
    } else {
        $('#expiry_date_wra').addClass('hidden');
    }
});
</script>
@endsection