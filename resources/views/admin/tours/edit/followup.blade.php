<div class="card">
    <div class="card-warnig">
        <div class="card-header">
            <h3 class="card-title">Followups</h3>            
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
                                    <label style="font-weight:400"><input type="checkbox" name="email_followup_rev" id="email_followup_rev" {{ old('email_followup_rev') || $data->detail?->email_followup_rev ? 'checked' : '' }} />  Send a follow-up Review Request</label>
                                    <div id="emailfollowup" class="hid den">
                                        <div class="form-group" style="max-width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <input type="text" class="form-control" id="email_followup_rev_delay" name="email_followup_rev_delay" placeholder="7" value="{{ old('email_followup_rev_delay') ?? $data->detail?->email_followup_rev_delay }}">
                                                </div>
                                                <select class="form-control" id="email_followup_rev_delayUnit" name="email_followup_rev_delayUnit">
                                                    <option {{ (old('email_followup_rev_delayUnit', $data->detail?->email_followup_rev_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                    <option {{ (old('email_followup_rev_delayUnit', $data->detail?->email_followup_rev_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                    <option {{ (old('email_followup_rev_delayUnit', $data->detail?->email_followup_rev_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">before the session start time</span>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea name="email1_reminder_text" id="email1_reminder_text" class="form-control  aiz-text-editor" >{{ old('email1_reminder_text') }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="email_followup_rec" id="email_followup_rec" {{ old('email_followup_rec') || $data->detail?->email_followup_rec ? 'checked' : '' }} /> Send a follow-up Recommendation Request</label>
                                    <div id="emailfollowuprec" class="hid den">
                                        <div class="form-group" style="max-width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <input type="text" class="form-control" id="email_followup_rec_delay" name="email_followup_rec_delay" placeholder="7" value="{{ old('email_followup_rec_delay') ?? $data->detail?->email2_reminder_num }}">
                                                </div>
                                                <select class="form-control" id="email_followup_rec_delayUnit" name="email_followup_rec_delayUnit">
                                                    <option {{ (old('email_followup_rec_delayUnit', $data->detail?->email_followup_rec_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                    <option {{ (old('email_followup_rec_delayUnit', $data->detail?->email_followup_rec_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                    <option {{ (old('email_followup_rec_delayUnit', $data->detail?->email_followup_rec_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
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
                                    <label style="font-weight:400"><input type="checkbox" name="email_followup_coupon" id="email_followup_coupon" {{ old('email_followup_coupon') || $data->detail?->email_followup_coupon ? 'checked' : '' }} /> Send a follow-up Coupon</label>
                                    <div id="email3reminder" class="hidden">
                                        <div class="form-group" style="max-width: 600px;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <input type="text" class="form-control" id="email_followup_coupon_delay" name="email_followup_coupon_delay" placeholder="7" value="{{ old('email_followup_coupon_delay') ?? $data->detail?->email3_reminder_num }}">
                                                </div>
                                                <select class="form-control" id="email_followup_coupon_delayUnit" name="email_followup_coupon_delayUnit">
                                                    <option {{ (old('email_followup_coupon_delayUnit', $data->detail?->email_followup_coupon_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                    <option {{ (old('email_followup_coupon_delayUnit', $data->detail?->email_followup_coupon_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                    <option {{ (old('email_followup_coupon_delayUnit', $data->detail?->email_followup_coupon_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
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
                                    <label style="font-weight:400"><input type="checkbox" name="sms_followup_customer" id="sms_followup_customer" {{ old('sms_followup_customer') || $data->detail?->sms_followup_customer ? 'checked' : '' }} /> Send a follow up SMS to the customer
                                    </label>

                                    <div class="form-group" style="max-width: 600px;">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                            <input type="text" class="form-control" id="sms_followup_delay" name="sms_followup_delay" placeholder="7" value="{{ old('sms_followup_delay') ?? $data->detail?->sms_followup_delay }}">
                                            </div>
                                            <select class="form-control" id="sms_followup_delayUnit" name="sms_followup_delayUnit">
                                                <option {{ (old('sms_followup_delayUnit', $data->detail?->sms_followup_delayUnit)=='MINUTES') ? 'checked' : '' }} value="MINUTES">minutes</option>
                                                <option {{ (old('sms_followup_delayUnit', $data->detail?->sms_followup_delayUnit)=='HOURS') ? 'checked' : '' }} value="HOURS">hours</option>
                                                <option {{ (old('sms_followup_delayUnit', $data->detail?->sms_followup_delayUnit)=='DAYS') ? 'checked' : '' }} value="DAYS">days</option>
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