<x-admin>
    @section('title','Categories')
    @section('css')
    <style>
        .alink{color: #27bcf1; }
        .alink:hover{text-decoration: underline;}
    </style>
    @endsection
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Categories</h3>
            @can('add_category')
            <div class="card-tools">
                <a href="{{ route('admin.category.create') }}" class="btn btn-sm btn-info">Create New</a>
            </div>
            @endcan
        </div>
        <div class="card-body">
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
                                class="btn btn-sm btn-primary">{{ $cat->name }}</a>
                            @else
                            {{ $cat->name }}    
                            @endcan
                            </td>
                            <td>
                            @foreach($cat->tours as $tour)
                                <div><a class="alink" href="{{ $tour->slug }}" title="{{ $tour->title }}">{{ $tour->title }}</a></div>
                            @endforeach
                            </td>
                            <td width="60">
                                @can('edit_category')
                                <a href="{{ route('admin.category.edit', encrypt($cat->id)) }}"
                                    class="btn btn-sm btn-primary">Edit</a>
                                @endcan
                            </td>
                            <td  width="60">
                                @can('destroy_category')
                                <form action="{{ route('admin.category.destroy', encrypt($cat->id)) }}" method="POST"
                                    onsubmit="return confirm('Are sure want to delete?')">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                @endcan
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
