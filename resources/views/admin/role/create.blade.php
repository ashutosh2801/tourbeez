<x-admin>
    @section('title','Create Role')
    <section class="content">
        <!-- Default box -->
        <div class="d-flex justify-content-center">
            <div class="col-lg-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Create New Role</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.role.index') }}"
                                class="btn btn-sm btn-dark">Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form action="{{ route('admin.role.store') }}" method="POST"
                        class="needs-validation" novalidate="">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Role Name</label>
                                        <input type="text" class="form-control" name="name" id="name"
                                            required="" value="{{ old('name') }}">
                                        <x-error>name</x-error>
                                        <div class="invalid-feedback">Role name field is required.</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="roles" class="form-label"><strong>Permissions</strong></label>
                                        <ul class="my-0 py-0 mx-0 px-0" style="list-style:none;">
                                            @forelse ($permissions as $permission)
                                            <li style="list-style:none; display:inline-block; width:24.5%"><label for="permission_{{ $permission->id }}"><input type="checkbox" name="permissions[]" id="permission_{{ $permission->id }}" value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions ?? []) ? 'checked' : '' }} /> {{ ucwords(str_replace("_", " ", $permission->name)) }}</label></li>
                                            @empty
                                                <li>No permissions available</li>
                                            @endforelse
                                        </ul>
                                        @if ($errors->has('permissions'))
                                            <div class="text-danger">{{ $errors->first('permissions') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer float-end float-right">
                            <button type="submit" id="submit"
                                class="btn btn-primary float-end float-right">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- /.card -->

    </section>
</x-admin>
