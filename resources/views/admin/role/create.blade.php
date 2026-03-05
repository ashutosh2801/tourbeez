<x-admin>
    @section('title','Create Role')
    <div class="card card-primary bg-white border rounded-lg-custom">
        <div class="card-header create-supplier-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Create New Role</h3>
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
        <form action="{{ route('admin.role.store') }}" method="POST" class="needs-validation" novalidate="">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <!-- <label for="name" class="form-label"></label> -->
                            <input type="text" class="form-control" name="name" id="name"
                                required="" placeholder="Type Role Name" value="{{ old('name') }}">
                            <x-error>name</x-error>
                            <div class="invalid-feedback">Role name field is required.</div>
                        </div>

                        <div class="form-group">
                            <label for="roles" class="form-label"><strong>Permissions</strong></label>
                            <ul class="row p-0" style="list-style:none;line-height: 1.8em;">
                                @forelse ($permissions as $permission)
                                <li class="col-md-3"><label for="permission_{{ $permission->id }}" style="font-weight:500;cursor:pointer;"><input type="checkbox" name="permissions[]" id="permission_{{ $permission->id }}" value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions ?? []) ? 'checked' : '' }} /> {{ ucwords(str_replace("_", " ", $permission->name)) }}</label></li>
                                @empty
                                    <li>No permissions available</li>
                                @endforelse
                            </ul>
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
</x-admin>
