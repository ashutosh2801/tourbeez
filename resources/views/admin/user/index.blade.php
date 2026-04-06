<x-admin>
    @section('title', 'Users')
    <div class="card-primary mb-3">
        <div class="card-header suppliers-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">User Table</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.user.create') }}" class="btn btn-sm btn-success"> + Add Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-primary bg-white border rounded-lg-custom customer-edit-body">
        <form method="GET" class="p-3">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Search Name">
                </div>

                <div class="col-md-3">
                    <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Search Email">
                </div>

                <div class="col-md-3">
                    <select name="per_page" class="form-control">
                        @foreach ([10, 25, 50, 100] as $number)
                            <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                {{ $number }} per page
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="d-flex column-gap-10">
                        <button class="btn btn-filter flex-fill">Filter</button>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-secondary flex-fill">Reset</a>
                    </div>
                </div>
            </div>
        </form>
        <div class="card-body p-0">
            <table class="table table-striped" id="userTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
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
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.user.edit', encrypt($user->id)) }}" class="btn btn-sm btn-edit"><i class="far fa-edit"></i></a>
                            </td>
                            <td>
                                <form action="{{ route('admin.user.destroy', encrypt($user->id)) }}" method="POST"
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
            <div class="card-footer">
                {{ $data->links() }}
            </div>
        </div>
    </div>
    @section('js')
        <!-- <script>
            $(function() {
                $('#userTable').DataTable({
                    "paging": false,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                });
            });
        </script> -->
    @endsection
</x-admin>
