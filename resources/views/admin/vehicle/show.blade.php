<x-admin>

    @section('title', 'Vehicle Details')

    <div class="card card-primary bg-white border rounded-lg-custom">

        <div class="card-header customer-edit-head">

            <div class="row">

                <div class="col-md-8 col-6">
                    <h3 class="card-title">
                        Vehicle Details
                    </h3>
                </div>

                <div class="col-md-4 col-6">

                    <div class="card-tools">

                        <a href="{{ route('admin.vehicles.index') }}"
                           class="btn btn-sm btn-back">
                            Back
                        </a>

                        <a href="{{ route('admin.vehicles.edit', encrypt($vehicle->id)) }}"
                           class="btn btn-sm btn-primary">
                            Edit
                        </a>

                    </div>

                </div>

            </div>

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="220">Vehicle ID</th>
                    <td>{{ $vehicle->id }}</td>
                </tr>

                <tr>
                    <th>Vehicle Code</th>
                    <td>{{ $vehicle->code }}</td>
                </tr>

                <tr>
                    <th>Vehicle Name</th>
                    <td>{{ $vehicle->name }}</td>
                </tr>

                <tr>
                    <th>No. of Seats</th>
                    <td>{{ $vehicle->no_of_seats ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Created At</th>
                    <td>{{ optional($vehicle->created_at)->format('d M Y h:i A') }}</td>
                </tr>

                <tr>
                    <th>Updated At</th>
                    <td>{{ optional($vehicle->updated_at)->format('d M Y h:i A') }}</td>
                </tr>

            </table>

        </div>

    </div>

</x-admin>