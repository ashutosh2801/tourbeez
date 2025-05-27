<x-admin>
    @section('title','Edit Role')
    <section class="content">
        <!-- Default box -->
        <div class="d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Edit Role</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.role.index') }}"
                                class="btn btn-sm btn-dark">Back</a>
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

                                    <div class="form-group">
                                        <label for="roles" class="form-label"><strong>Permissions</strong></label>
                                        <select class="form-control @error('permissions') is-invalid @enderror" multiple aria-label="Permissions" id="permissions" name="permissions[]" style="height: 210px;">
                                            @forelse ($permissions as $permission)
                                                <option style="line-height: 1.9em;" value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions ?? []) ? 'selected' : '' }}>
                                                    {{ ucwords(str_replace("_", " ", $permission->name)) }}
                                                </option>
                                            @empty
                                                <option value="" disabled>No permissions available</option>
                                            @endforelse
                                        </select>
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
