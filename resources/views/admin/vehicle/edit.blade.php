<x-admin>

    @section('title', 'Edit Vehicle')

    <form action="{{ route('admin.vehicles.update', encrypt($vehicle->id)) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-header customer-edit-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">Edit Vehicle</h3>
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

        <div class="card card-primary bg-white border rounded-lg-custom customer-edit-body">

            <div class="card-body">

                <div class="row">

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Vehicle Code <span class="text-danger">*</span></label>

                            <input
                                type="text"
                                name="code"
                                class="form-control"
                                value="{{ old('code', $vehicle->code) }}"
                                required>

                            <x-error>code</x-error>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Vehicle Name <span class="text-danger">*</span></label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $vehicle->name) }}"
                                required>

                            <x-error>name</x-error>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>No. of Seats</label>

                            <input
                                type="number"
                                min="1"
                                name="no_of_seats"
                                class="form-control"
                                value="{{ old('no_of_seats', $vehicle->no_of_seats) }}">

                            <x-error>no_of_seats</x-error>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="card-footer bg-white border rounded-lg-custom">
            <div class="float-right">
                <button class="btn btn-success m-0" type="submit">
                    <i class="fas fa-save"></i>
                    Save
                </button>
            </div>
        </div>

    </form>

</x-admin>