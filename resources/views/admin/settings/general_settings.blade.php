<x-admin>
    @section('title')
        {{ 'General Settings' }}
    @endsection
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ __('General Settings') }}</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ __('System Name') }}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="site_name">
                                <input type="text" name="site_name" class="form-control" value="{{ env('APP_NAME') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ __('System Logo') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="system_logo">
                                    <input type="hidden" name="system_logo" value="{{ get_setting('system_logo') }}"
                                        class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ __('System Timezone') }}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="timezone">
                                <select name="timezone" class="form-control aiz-selectpicker" data-live-search="true">
                                    @foreach (timezones() as $key => $value)
                                        <option value="{{ $value }}" @if (env('APP_TIMEZONE') == $value)
                                            selected
                                    @endif>{{ $key }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ __('Admin login page background') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ __('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ __('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="admin_login_background">
                                    <input type="hidden" name="admin_login_background"
                                        value="{{ get_setting('admin_login_background') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                       
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ __('Activation') }}</h1>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-8 col-from-label">{{ __('HTTPS Activation') }}</label>
                        <div class="col-sm-4">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="updateSettings(this, 'FORCE_HTTPS')" <?php if (env('FORCE_HTTPS') == 'On') {
    echo 'checked';
} ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-8 col-from-label">{{ __('Maintenance Mode Activation') }}</label>
                        <div class="col-sm-4">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="updateSettings(this, 'maintenance_mode')"
                                    <?php if (\App\Models\Setting::where('type', 'maintenance_mode')->first()->value == 1) {
                                        echo 'checked';
                                    } ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>                    

                    <div class="form-group row">
                        <div class="col-sm-8">
                            <label class="col-from-label">{{ __('Email Verification') }}
                                <i>
                                    <code>({{ __('You need to configure SMTP correctly to enable this feature.') }}
                                        <a
                                            href="{{ route('admin.email_settings') }}">{{ __('Configure Now') }}</a>)</code>
                                </i>
                            </label>
                        </div>
                        <div class="col-sm-4">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="updateSettings(this, 'email_verification')"
                                    <?php if (get_setting('email_verification') == 1) {
                                        echo 'checked';
                                    } ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        </div>
                    
                    
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{__('Default Email')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-12 col-form-label">{{translate('Default Email Header')}}</label>
                            <div class="col-md-12">
                                <input type="hidden" name="types[]" value="default_email_header">
                                <textarea name="default_email_header" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="300" required>{{ get_setting('default_email_header') }}</textarea>
                                @error('default_email_header')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>                            
                        </div>

                        <div class="form-group row">
                            <label class="col-md-12 col-form-label">{{translate('Default Email Footer')}}</label>
                            <div class="col-md-12">
                                <input type="hidden" name="types[]" value="default_email_footer">
                                <textarea name="default_email_footer" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="300" required>{{ get_setting('default_email_footer') }}</textarea>
                                @error('default_email_footer')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>                            
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@section('js')
    <script type="text/javascript">
        function updateSettings(el, type) {
            if ($(el).is(':checked')) {
                var value = 1;
            } else {
                var value = 0;
            }
            $.post('{{ route('admin.settings.activation.update') }}', {
                _token: '{{ csrf_token() }}',
                type: type,
                value: value
            }, function(data) {
                if (data == '1') {
                    AIZ.plugins.notify('success', '{{ __('Settings updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ __('Something went wrong') }}');
                }
            });
        }
    </script>
@endsection
</x-admin>
