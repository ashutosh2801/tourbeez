<x-admin>
    @section('title','Tour types')
    <div class="card-primary mb-3">
        <div class="card-header tour-type-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Tour types</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.tour_type.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-primary bg-white border rounded-lg-custom tour-type-body">
        <div class="card-body p-0">
            <div class="p-3 pb-0">@include('admin.partials.table-search', ['tableId' => 'categoryTable', 'title' => 'tour types', 'placeholder' => 'Name, slug or description'])</div>
            <div class="table-viewport">
                <table class="table table-striped" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $cat)
                            <tr>
                                <td>{{ $cat->name }}</td>
                                <td>{{ $cat->slug }}</td>
                                <td>{{ $cat->description }}</td>
                                <td class="text-center admin-table-actions"><a href="{{ route('admin.tour_type.edit', encrypt($cat->id)) }}"
                                        class="btn btn-sm btn-edit admin-table-action"><i class="far fa-edit"></i> </a>
                                    <form action="{{ route('admin.tour_type.destroy', encrypt($cat->id)) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Are sure want to delete?')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger confirm-delete admin-table-action"><i class="fas fa-trash-alt"></i></button>
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
                $('#categoryTable').DataTable({
                    "paging": false,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                    "info": false,
                    "dom": "rt"
                });
                $('[data-table-search="categoryTable"]').on('input', 'input', function(){ $('#categoryTable').DataTable().search(this.value).draw(); }).on('submit', function(e){ e.preventDefault(); });
            });
        </script>
    @endsection
</x-admin>
