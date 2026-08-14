<x-admin>
    @section('title','Edit Role')
    <section class="content">
        <!-- Default box -->
        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-header create-supplier-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">Edit Role</h3>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <a href="{{ route('admin.role.index') }}" class="btn btn-sm btn-back">Back</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('admin.role.update', $data->id) }}" method="POST"
                class="needs-validation" novalidate="">
                @method('PUT')
                @csrf
                <input type="hidden" name="id" value="{{ $data->id }}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                             <div class="form-group">
                                <label for="name" class="form-label">Role Name</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    required="" value="{{ $data->name }}">
                                @if ($errors->has('name'))
                                    <div class="text-danger">{{ $errors->first('name') }}</div>
                                @endif
                                <div class="invalid-feedback">Role name field is required.</div>
                            </div>

                            <!--<div class="form-group">
                                <label for="roles" class="form-label"><strong>Permissions</strong></label>


                                <ul class="row p-0" style="list-style:none;line-height: 1.8em;">
                                    @forelse ($permissions as $permission)
                                    <li  class="col-md-3"><label for="permission_{{ $permission->id }}" style="font-weight:500;cursor:pointer;"><input type="checkbox" name="permissions[]" id="permission_{{ $permission->id }}" value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions ?? []) ? 'checked' : '' }} /> {{ ucwords(str_replace("_", " ", $permission->name)) }}</label></li>
                                    @empty
                                        <li>No permissions available</li>
                                    @endforelse
                                </ul>
                                @if ($errors->has('permissions'))
                                    <div class="text-danger">{{ $errors->first('permissions') }}</div>
                                @endif

                            </div> -->

                            <div class="form-group">
    <label class="form-label"><strong>Permissions</strong></label>

    @php

$permissionGroups = [];

$customGroups = [

    'dashboard' => 'Dashboard',
    'dashboard_reports' => 'Dashboard',

    'general_settings' => 'Settings',
    'smtp_settings' => 'Settings',
    'email_templates' => 'Settings',
    'payment_settings' => 'Settings',
    'third_party_settings' => 'Settings',

    'overview_report' => 'Reports',
    'revenue_report' => 'Reports',
    'invoice_report' => 'Reports',
    'tour_report' => 'Reports',
    'comparison_report' => 'Reports',

    'manage_manifest' => 'Manifests',

    'refund_order' => 'Orders',
    'cancel_order' => 'Orders',

    'activity_description' => 'Activity Logs',
    'order_logs' => 'Activity Logs',

];

foreach ($permissions as $permission) {

    $name = $permission->name;

    if (preg_match('/^(show|add|edit|delete)_(.+)$/', $name, $matches)) {

        $action = $matches[1];

        $module = ucwords(str_replace('_', ' ', Str::plural($matches[2])));

    } elseif (isset($customGroups[$name])) {

        $module = $customGroups[$name];

        $action = 'extra';

    } else {

        $module = 'Other';

        $action = 'extra';

    }

    $permissionGroups[$module][$action][] = $permission;
}

ksort($permissionGroups);

@endphp

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="thead-light">
                <tr>
                    <th width="25%">Module</th>
                    <th class="text-center">All</th>
                    <th class="text-center">View</th>
                    <th class="text-center">Add</th>
                    <th class="text-center">Edit</th>
                    <th class="text-center">Delete</th>
                    <th>Other Permissions</th>
                </tr>
            </thead>

            <tbody>

                @foreach($permissionGroups as $module => $actions)

                    @php
                        $slug = Str::slug($module);
                    @endphp

                    <tr>

                        <td>
                            <strong>{{ $module }}</strong>
                        </td>

                        <td class="text-center">
                            <input type="checkbox"
                                   class="toggle-row"
                                   data-row="{{ $slug }}">
                        </td>

                        @foreach(['show','add','edit','delete'] as $action)

                            <td class="text-center">

                                @if(isset($actions[$action]))

                                    @foreach($actions[$action] as $permission)

                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            class="permission row-{{ $slug }}"
                                            {{ in_array($permission->id,$rolePermissions ?? []) ? 'checked' : '' }}
                                        >

                                    @endforeach

                                @endif

                            </td>

                        @endforeach

                        <td>

                            @if(isset($actions['extra']))

                               @foreach($actions['extra'] as $permission)

                                    <div>

                                        <label>

                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->id }}"
                                                class="permission row-{{ $slug }}"
                                                {{ in_array($permission->id,$rolePermissions ?? []) ? 'checked' : '' }}
                                            >

                                            {{ ucwords(str_replace('_',' ',$permission->name)) }}

                                        </label>

                                    </div>

                                @endforeach

                            @endif

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>
    </div>

    @if ($errors->has('permissions'))
        <div class="text-danger">{{ $errors->first('permissions') }}</div>
    @endif

</div>



                        </div>
                        <div class="col-lg-12">
                            <div class="float-right">
                                <button type="submit" id="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </form>
        </div>
        <!-- /.card -->
    </section>
    @section('js')
    <script>
$(document).ready(function () {

    // Toggle all permissions in a row
    $('.toggle-row').on('change', function () {

        let row = $(this).data('row');

        $('.row-' + row).prop('checked', $(this).is(':checked'));

    });

    // Update row "All" checkbox
    $('.permission').on('change', function () {

        let rowClass = $(this).attr('class').split(' ').find(function(c){
            return c.startsWith('row-');
        });

        let total = $('.' + rowClass).length;
        let checked = $('.' + rowClass + ':checked').length;

        $('.toggle-row[data-row="' + rowClass.replace('row-', '') + '"]')
            .prop('checked', total === checked);

    });

    // Initialize row checkboxes on page load
    $('.toggle-row').each(function () {

        let row = $(this).data('row');

        let total = $('.row-' + row).length;
        let checked = $('.row-' + row + ':checked').length;

        $(this).prop('checked', total > 0 && total === checked);

    });

});
</script>
@endsection
</x-admin>
