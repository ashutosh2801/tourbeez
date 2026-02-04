<x-admin>
    @section('title','Roles')
    <div class="card-primary mb-3">
        <div class="card-header roles-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Roles</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.role.create') }}" class="btn btn-sm btn-success"> + Add Roles</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-primary bg-white border rounded-lg-custom customer-edit-body">
        <div class="card-body p-0">
            <table class="table table-striped" id="roleTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Created</th>
                        <th>Action</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.role.edit',encrypt($role->id)) }}" class="btn btn-sm btn-edit"><i class="far fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <form action="{{ route('admin.role.destroy',encrypt($role->id)) }}" method="POST" onclick="confirm('Are you sure')">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-danger confirm-delete"><i class="fas fa-trash-alt"></i></button>
                            </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @section('js')
        <script>
            $(function() {
                $('#roleTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                });
            });
        </script>
    @endsection
</x-admin>
