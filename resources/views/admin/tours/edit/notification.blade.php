<div class="card">
    <div class="card-info">
        <div class="card-header sub-heading">
            <h3 class="card-title">Message Notification</h3>            
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
        <form class="needs-validation" novalidate action="{{ route('admin.tour.notification_update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="card-body">
                <!-- Emails -->
                <div class="row">                   
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <label for="IsPurchasedAsAGift" class="form-label col-2">Emails</label>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="Meta[email_info]" id="email_info" {{ old('meta.email_info', !empty($metaData['email_info']) ? 'checked' : '') }} /> Add information to confirmation emails</label>
                                    <div id="emailinfotext" class="hidden">
                                        <textarea name="Meta[email_info_text]" id="email_info_text" class="form-control  aiz-text-editor" >{{ old('meta.email_info_text', $metaData['email_info_text'] ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="Meta[email_attachment]" id="email_attachment" {{ old('meta.email_attachment', !empty($metaData['email_attachment']) ? 'checked' : '') }} /> Add attachment to emails</label>
                                    <div id="emailattachmentfile" class="hidden">
                                        <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                                            </div>
                                            <div class="form-control file-amount">{{translate('Choose File')}}</div>
                                            <input type="hidden" name="Meta[email_attachment_file]" class="selected-files" value="{{ old('meta.email_attachment_file', $metaData['email_attachment_file'] ?? '') }}">
                                        </div>
                                        <div class="file-preview box sm"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="Meta[email_notification]" id="email_notification" {{ old('meta.email_notification', !empty($metaData['email_notification']) ? 'checked' : '') }} /> Also send supplier notification email to these recipient(s)</label>
                                    <div id="emailnotification" class="hidden">
                                        <input type="text" name="Meta[email_notification_emails]" class="form-control" value="{{ old('meta.email_notification_emails', $metaData['email_notification_emails'] ?? '') }}" placeholder="john@doe.com; tony@doe.com">
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
                                    <label style="font-weight:400"><input type="checkbox" name="Meta[sms_send_me]" id="sms_send_me" {{ old('meta.sms_send_me', !empty($metaData['sms_send_me']) ? 'checked' : '') }} /> Send me an SMS when I get a booking
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label style="font-weight:400"><input type="checkbox" name="Meta[sms_send_customer]" id="sms_send_customer" {{ old('meta.sms_send_customer', !empty($metaData['sms_send_customer']) ? 'checked' : '') }} /> Send an SMS to the customer to confirm their order</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer" style="display:block">
                <div class="row">
                    <div class="col-md-6">
                        <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                    </div>
                    <div class="col-md-6 align-buttons">
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.gallery', encrypt($data->id)) }}" class="btn btn-secondary"> <i class="fas fa-chevron-left"></i> Back</a>
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.message.reminder', encrypt($data->id)) }}" class="btn btn-secondary">Next <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@section('js')
@parent
<script>
function email_info() {
    if ($('#email_info').is(':checked')) {
        $('#emailinfotext').removeClass('hidden');
    } else {
        $('#emailinfotext').addClass('hidden');
    }
}
email_info();
$('#email_info').on('change', email_info);

function email_attachment() {
    if ($('#email_attachment').is(':checked')) {
        $('#emailattachmentfile').removeClass('hidden');
    } else {
        $('#emailattachmentfile').addClass('hidden');
    }
}
email_attachment();
$('#email_attachment').on('change', email_attachment);

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