<x-admin>
    @section('title','Create Permission')
    <section class="content">
        <!-- Default box -->
        <div class="card card-primary bg-white border rounded-lg-custom mb-0">
                <div class="card card-primary mb-0">
                    <div class="card-header create-supplier-head">
                        <div class="row">
                            <div class="col-md-8 col-12">
                                <h3 class="card-title">Create New Permission</h3>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="card-tools">
                                    <a href="{{ route('admin.permission.index') }}" class="btn btn-sm btn-back">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form action="{{ route('admin.permission.store') }}" method="POST"
                        class="needs-validation" novalidate="">
                        @csrf
                        <div class="card-body create-role-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Permission Name</label>
                                        <input type="text" class="form-control" name="name" id="name"
                                            required="" placeholder="Type Permission Name" value="{{ old('name') }}">
                                            <x-error>name</x-error>
                                        <div class="invalid-feedback">Permission name field is required.</div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="float-right">
                                        <button type="submit" id="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </form>
                </div>
        </div>
        <!-- /.card -->

    </section>
</x-admin>
