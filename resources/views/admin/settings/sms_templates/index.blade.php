<x-admin>
    @section('title')
        {{translate('SMS Templates')}}
    @endsection
    <style>
        .popover {
            max-width: 382px;
        }
        .popover-body ul, .popover-body ul li {
            list-style: none; padding: 0; margin: 0;
        }
        .popover-body ul li {
            padding: 5px 0;border-bottom: 1px solid #e9e8e8;
        }
        .popover-body ul li span {
            display: inline-block; width: 135px;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('SMS Templates')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">

                                @foreach ($sms_templates as $key => $sms_template)
                                    <a title="{{ $sms_template->identifier }}" class="nav-link @if($sms_template->id == 1) active @endif" id="v-pills-tab-2" data-toggle="pill" href="#v-pills-{{ $sms_template->id }}" role="tab" aria-controls="v-pills-profile" aria-selected="false">{{ translate(ucwords(str_replace('_', ' ', $sms_template->identifier)))  }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="tab-content" id="v-pills-tabContent">

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="list-unstyled">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif


                                @foreach ($sms_templates as $key => $sms_template)
                                    <div class="tab-pane fade show @if($sms_template->id == 1) active @endif" id="v-pills-{{ $sms_template->id }}" role="tabpanel" aria-labelledby="v-pills-tab-1">
                                        <form action="{{ route('admin.sms-templates.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="identifier" value="{{ $sms_template->identifier }}">
                                            @if($sms_template->identifier != 'password_reset_email0000')

                                                <div class="form-group">
                                                    <h2 class="heading" style="font-size:21px; margin: 0 0 35px; font-weight:700">{{ $sms_template->identifier }}</h2>
                                                </div>
                                                    <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <label class="col-from-label">{{translate('Activation')}}</label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input value="1" name="status" type="checkbox" 
                                                            @if ($sms_template->status == 1)
                                                                checked
                                                            @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>

                                                    
                                                    <div class="col-md-3">

                                                        <button type="button"
                                                            class="btn btn-sm btn-primary popover-btn"
                                                            data-toggle="popover"
                                                            title="{{ $sms_template->identifier }}"
                                                            data-html="true"
                                                            data-placement="left"
                                                            data-content="{{ $sms_template->parameters }}">Use variables from here</button>

                                                        <a class="btn btn-sm btn-danger sms-preview" data-href="{{ route('admin.sms-templates.preview', $sms_template->identifier) }}">{{translate('Preview')}}</a>
                                                    </div>
                                            </div>
                                            
                                            @endif
                                            <div class="form-group row">
                                                <label class="col-md-12col-form-label">{{translate('Message')}}</label>
                                                <div class="col-md-12">
                                                    <textarea name="message" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="500" required>{{ $sms_template->message }}</textarea>
                                                    @error('body')
                                                        <small class="form-text text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                             <div class="form-group row">
                                                <label class="col-form-label">{{translate('SMS Parameters')}}</label>
                                                <div class="col-md-12">
                                                    <textarea name="parameters" class="form-control aiz-text-editor" placeholder="Type.." rows="8" required>{{ $sms_template->parameters }}</textarea>
                                                    @error('parameters')
                                                        <small class="form-text text-danger">{{ $parameters }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="form-group mb-3 text-right">
                                                <button type="submit" class="btn btn-primary">{{translate('Update Settings')}}</button>
                                            </div>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- delete Modal -->
<div id="sms-preview-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('SMS Preview') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <div id="sms_preview"></div>
            </div>
        </div>
    </div>
</div>
@section('js')
<script>
$(document).ready(function () {
    // Initialize all popovers
    $('[data-toggle="popover"]').popover({
        html: true,
        trigger: 'click'
    });

    // Close other popovers when one is clicked
    $('.popover-btn').on('click', function (e) {
        $('.popover-btn').not(this).popover('hide');
        e.stopPropagation();
    });

    // Optional: Close when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.popover, .popover-btn').length) {
            $('.popover-btn').popover('hide');
        }
    });
});

$(".sms-preview").click(function (e) {
    e.preventDefault();
    var url = $(this).data("href");
    $("#sms-preview-modal").modal("show");
    $('#sms_preview').html('Loading preview ...');

    $.post(url, {
        _token: '{{ csrf_token() }}'
    }, function(data) {
        $('#sms_preview').html(data);
    });
});

</script>

@endsection
</x-admin>