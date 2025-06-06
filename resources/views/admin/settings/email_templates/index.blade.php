<x-admin>
    @section('title')
        {{translate('Email Templates')}}
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
                    <h5 class="mb-0 h6">{{translate('Email Templates')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">

                                @foreach ($email_templates as $key => $email_template)
                                    <a title="{{ $email_template->description }}" class="nav-link @if($email_template->id == 1) active @endif" id="v-pills-tab-2" data-toggle="pill" href="#v-pills-{{ $email_template->id }}" role="tab" aria-controls="v-pills-profile" aria-selected="false">{{ translate(ucwords(str_replace('_', ' ', $email_template->identifier)))  }}</a>
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


                                @foreach ($email_templates as $key => $email_template)
                                    <div class="tab-pane fade show @if($email_template->id == 1) active @endif" id="v-pills-{{ $email_template->id }}" role="tabpanel" aria-labelledby="v-pills-tab-1">
                                        <form action="{{ route('admin.email-templates.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="identifier" value="{{ $email_template->identifier }}">
                                            @if($email_template->identifier != 'password_reset_email0000')

                                                <div class="form-group">
                                                    <h2 class="heading" style="font-size:21px; margin: 0 0 35px; font-weight:700">{{ $email_template->description }}</h2>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="col-md-2">
                                                        <label class="col-from-label">{{translate('Activation')}}</label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="aiz-switch aiz-switch-success mb-0">
                                                            <input value="1" name="status" type="checkbox" 
                                                            @if ($email_template->status == 1)
                                                                checked
                                                            @endif>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>

                                                    
                                                    <div class="col-md-3">

<button type="button"
    class="btn btn-sm btn-primary popover-btn"
    data-toggle="popover"
    title="{{ $email_template->subject }}"
    data-html="true"
    data-placement="left"
    data-content="{{ $email_template->parameters }}">Use variables from here</button>

<a class="btn btn-sm btn-danger email-preview" data-href="{{ route('admin.email-templates.preview', $email_template->identifier) }}">{{translate('Preview')}}</a>

                                                    </div>
                                                </div>
                                            @endif
                                            <div class="form-group row">
                                                <label class="col-md-12 col-form-label">{{translate('Subject')}}</label>
                                                <div class="col-md-12">
                                                    <input type="text" name="subject" value="{{ $email_template->subject }}" class="form-control" placeholder="{{translate('Subject')}}" required>
                                                    @error('subject')
                                                        <small class="form-text text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-12 col-form-label">{{translate('Email Header')}}</label>
                                                <div class="col-md-12">
                                                    <textarea name="header" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="300" required>{{ $email_template->header ? $email_template->header : get_setting('default_email_header') }}</textarea>
                                                    @error('header')
                                                        <small class="form-text text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-12col-form-label">{{translate('Email Body')}}</label>
                                                <div class="col-md-12">
                                                    <textarea name="body" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="500" required>{{ $email_template->body }}</textarea>
                                                    @error('body')
                                                        <small class="form-text text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-form-label">{{translate('Email Footer')}}</label>
                                                <div class="col-md-12">
                                                    <textarea name="footer" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="300" required>{{ $email_template->footer ? $email_template->footer : get_setting('default_email_footer') }}</textarea>
                                                    @error('footer')
                                                        <small class="form-text text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-form-label">{{translate('Email Parameters')}}</label>
                                                <div class="col-md-12">
                                                    <textarea name="parameters" class="form-control" placeholder="Type.." rows="8" required>{{ $email_template->parameters }}</textarea>
                                                    @error('parameters')
                                                        <small class="form-text text-danger">{{ $message }}</small>
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
<div id="email-preview-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Email Preview') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <div id="email_preview"></div>
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

$(".email-preview").click(function (e) {
    e.preventDefault();
    var url = $(this).data("href");
    $("#email-preview-modal").modal("show");
    $('#email_preview').html('Loading preview ...');

    $.post(url, {
        _token: '{{ csrf_token() }}'
    }, function(data) {
        $('#email_preview').html(data);
    });
});
</script>

@endsection
</x-admin>