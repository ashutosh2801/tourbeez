<x-admin>
    @section('title', 'Customers')
    <div class="card-primary mb-3">
        <div class="card-header customer-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Customers</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-success"> + Add New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary bg-white border rounded-lg-custom customer-body">
        <div class="card-body p-0">
            <div class="table-viewport">
                <table class="table table-striped" id="userTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <!-- <th>Created</th> -->
                            <th>Action</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phonenumber }}</td>
                                <!-- <td>{{ $user->created_at }}</td> -->
                                <td>
                                    <a href="{{ route('admin.customers.edit', encrypt($user->id)) }}"
                                        class="btn btn-sm btn-edit"> <i class="far fa-edit"></i> </a>
                                </td>
                                <td>
                                    <form action="{{ route('admin.customers.destroy', encrypt($user->id)) }}" method="POST"
                                        onsubmit="return confirm('Are sure want to delete?')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger confirm-delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @section('js')
        <script>
            $(function() {
                $('#userTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                });
            });
        </script>
    @endsection
</x-admin>
