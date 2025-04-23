<x-admin>
    @section('title')
        {{ 'Mail Settings' }}
    @endsection
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{__('Mail Settings')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.env_key_update.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MAIL_DRIVER">
                            <label class="col-md-3 col-form-label">{{__('Type')}}</label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="MAIL_DRIVER" onchange="checkMailDriver()">
                                    <option value="sendmail" @if (env('MAIL_DRIVER') == "sendmail") selected @endif>{{ __('Sendmail') }}</option>
                                    <option value="smtp" @if (env('MAIL_DRIVER') == "smtp") selected @endif>{{ __('SMTP') }}</option>
                                    <option value="mailgun" @if (env('MAIL_DRIVER') == "mailgun") selected @endif>{{ __('Mailgun') }}</option>
                                </select>
                            </div>
                        </div>
                        <div id="smtp">
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAIL_HOST">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAIL HOST')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAIL_HOST" value="{{  env('MAIL_HOST') }}" placeholder="{{ __('MAIL HOST') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAIL_PORT">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAIL PORT')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAIL_PORT" value="{{  env('MAIL_PORT') }}" placeholder="{{ __('MAIL PORT') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAIL_USERNAME">
                                <div class="col-md-3">
                                        <label class="col-from-label">{{__('MAIL USERNAME')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAIL_USERNAME" value="{{  env('MAIL_USERNAME') }}" placeholder="{{ __('MAIL USERNAME') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAIL_PASSWORD">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAIL PASSWORD')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAIL_PASSWORD" value="{{  env('MAIL_PASSWORD') }}" placeholder="{{ __('MAIL PASSWORD') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAIL_ENCRYPTION">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAIL ENCRYPTION')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAIL_ENCRYPTION" value="{{  env('MAIL_ENCRYPTION') }}" placeholder="{{ __('MAIL ENCRYPTION') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAIL_FROM_ADDRESS">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAIL FROM ADDRESS')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAIL_FROM_ADDRESS" value="{{  env('MAIL_FROM_ADDRESS') }}" placeholder="{{ __('MAIL FROM ADDRESS') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAIL_FROM_NAME">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAIL FROM NAME')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAIL_FROM_NAME" value="{{  env('MAIL_FROM_NAME') }}" placeholder="{{ __('MAIL FROM NAME') }}">
                                </div>
                            </div>
                        </div>
                        <div id="mailgun">
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAILGUN_DOMAIN">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAILGUN DOMAIN')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAILGUN_DOMAIN" value="{{  env('MAILGUN_DOMAIN') }}" placeholder="{{ __('MAILGUN DOMAIN') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" name="types[]" value="MAILGUN_SECRET">
                                <div class="col-md-3">
                                    <label class="col-from-label">{{__('MAILGUN SECRET')}}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="MAILGUN_SECRET" value="{{  env('MAILGUN_SECRET') }}" placeholder="{{ __('MAILGUN SECRET') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3 text-right">
                            <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{__('Test SMTP configuration')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.test.mail') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" placeholder="{{ __('Enter your email address') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">{{ __('Send test email') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@section('script')

    <script type="text/javascript">
        $(document).ready(function(){
            checkMailDriver();
        });
        function checkMailDriver(){
            if($('select[name=MAIL_DRIVER]').val() == 'mailgun'){
                $('#mailgun').show();
                $('#smtp').hide();
            }
            else{
                $('#mailgun').hide();
                $('#smtp').show();
            }
        }
    </script>

@endsection
</x-admin>