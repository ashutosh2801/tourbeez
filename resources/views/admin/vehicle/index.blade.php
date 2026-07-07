<x-admin>

    @section('title', 'Vehicles')

    <div class="card-primary mb-3">
        <div class="card-header customer-head">
            <h3 class="card-title">Vehicles</h3>

            <div class="card-tools">
                <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-sm">
                    Add Vehicle
                </a>
            </div>
        </div>
    </div>

    <div class="card card-primary bg-white border rounded-lg-custom customer-edit-body">

        <form method="GET" class="p-3">

            <div class="row">

                <div class="col-md-3">
                    <input
                        type="text"
                        class="form-control"
                        name="code"
                        placeholder="Search Code"
                        value="{{ request('code') }}">
                </div>

                <div class="col-md-3">
                    <input
                        type="text"
                        class="form-control"
                        name="name"
                        placeholder="Search Name"
                        value="{{ request('name') }}">
                </div>

                <div class="col-md-3">

                    <select name="per_page" class="form-control">

                        @foreach([10,25,50,100] as $number)

                            <option
                                value="{{ $number }}"
                                {{ request('per_page',10)==$number?'selected':'' }}>
                                {{ $number }} per page
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">

                    <div class="d-flex column-gap-10">

                        <button class="btn btn-filter flex-fill">
                            Filter
                        </button>

                        <a href="{{ route('admin.vehicles.index') }}"
                           class="btn btn-secondary flex-fill">
                            Reset
                        </a>

                    </div>

                </div>

            </div>

        </form>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-striped">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Code</th>

                            <th>Name</th>

                            <th>No. Seats</th>

                            <th width="130">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($data as $vehicle)

                            <tr>

                                <td>{{ $vehicle->id }}</td>

                                <td>{{ $vehicle->code }}</td>

                                <td>{{ $vehicle->name }}</td>

                                <td>{{ $vehicle->no_of_seats }}</td>

                                <td>

                                    <a href="{{ route('admin.vehicles.edit', encrypt($vehicle->id)) }}"
                                       class="btn btn-edit btn-sm">
                                        <i class="far fa-edit"></i>
                                    </a>

                                    <form
                                        action="{{ route('admin.vehicles.destroy', encrypt($vehicle->id)) }}"
                                        method="POST"
                                        class="d-inline delete-form">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm delete-btn">

                                            <i class="fas fa-trash-alt"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="text-center">
                                    No vehicles found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="card-footer">

                {{ $data->links() }}

            </div>

        </div>

    </div>

@section('js')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.querySelectorAll('.delete-btn').forEach(function(btn){

    btn.addEventListener('click',function(){

        let form=this.closest('form');

        Swal.fire({

            title:'Are you sure?',

            text:'This action cannot be undone!',

            icon:'warning',

            showCancelButton:true,

            confirmButtonColor:'#d33',

            cancelButtonColor:'#3085d6',

            confirmButtonText:'Yes, delete it!'

        }).then((result)=>{

            if(result.isConfirmed){

                form.submit();

            }

        });

    });

});

</script>

@endsection

</x-admin>