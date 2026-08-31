<x-admin>

    @section('title', 'Create Vehicle')

    <form action="{{ route('admin.vehicles.store') }}" method="POST">
        @csrf

        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-header vehicle-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">Create Vehicle</h3>
                    </div>

                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-sm btn-back">
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-primary bg-white border rounded-lg-custom customer-edit-body">
            <div class="card-header">
                <h3 class="card-title">Vehicle Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            
                            <label>Vehicle Code <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="code"
                                class="form-control"
                                value="{{ old('code') }}"
                                required>

                            <x-error>code</x-error>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Vehicle Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name') }}"
                                required>

                            <x-error>name</x-error>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>No. of Seats</label>
                            <input
                                type="number"
                                min="1"
                                name="no_of_seats"
                                class="form-control"
                                value="{{ old('no_of_seats') }}">

                            <x-error>no_of_seats</x-error>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-success float-right">
                    <i class="fas fa-save"></i>
                    Save
                </button>
            </div>
        </div>
    </form>

</x-admin>