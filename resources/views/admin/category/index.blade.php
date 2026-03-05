<x-admin>
    @section('title','Categories')
    @section('css')
    <style>
        .alink{color: #27bcf1; }
        .alink:hover{text-decoration: underline;}
    </style>
    @endsection
    <div class="card-primary mb-3">
        <div class="card-header categories-header">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Categories</h3>
                </div>
                @can('add_category')
                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <a href="{{ route('admin.category.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
    <div class="card-primary bg-white border rounded-lg-custom category-main-body">
        <div class="card-body p-0">
            <div class="table-viewport">
                <table class="table table-striped" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Tours</th>
                            <th>Action</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $cat)
                            <tr>
                                <td>
                                @can('edit_category')
                                <a href="{{ route('admin.category.edit', encrypt($cat->id)) }}"
                                    class="btn btn-name">{{ $cat->name }}</a>
                                @else
                                {{ $cat->name }}    
                                @endcan
                                </td>
                                <td>
                                @foreach($cat->tours as $tour)
                                    <div><a class="alink" href="{{ $tour->slug }}" title="{{ $tour->title }}"> <i class="fas fa-chevron-right"></i> {{ $tour->title }}</a></div>
                                @endforeach
                                </td>
                                <td width="60">
                                    @can('edit_category')
                                    <a href="{{ route('admin.category.edit', encrypt($cat->id)) }}"
                                        class="btn btn-sm btn-edit"> <i class="far fa-edit"></i> </a>
                                    @endcan
                                </td>
                                <td  width="60">
                                    @can('destroy_category')
                                    <form action="{{ route('admin.category.destroy', encrypt($cat->id)) }}" method="POST"
                                        onsubmit="return confirm('Are sure want to delete?')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger confirm-delete"> <i class="fas fa-trash-alt"></i> </button>
                                    </form>
                                    @endcan
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
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                });
            });
        </script>
    @endsection
</x-admin>
