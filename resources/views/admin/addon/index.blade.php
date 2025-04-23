<x-admin>
    @section('title','Addons')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Addons</h3>
            <div class="card-tools">
                <a href="{{ route('admin.addon.create') }}" class="btn btn-sm btn-info">Create New</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="categoryTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th width="150">Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th width="80">Price Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td><img src="{{ asset('addon/'.$item->image) }}" alt="{{ $item->name }}" width="150" /></td>
                            <td><a href="{{ route('admin.addon.edit', encrypt($item->id)) }}" class="text-info">{{ $item->name }}</a></td>
                            <td>{{ $item->description }}</td>
                            <td>{{ price_format($item->price) }}</td>
                            <td>{{ $item->customer_choice }}</td>
                            <td width="60">
                                <!-- <a href="{{ route('admin.addon.edit', encrypt($item->id)) }}"
                                    class="btn btn-sm btn-primary">Edit</a> -->
                                <a href="{{ route('admin.addon.destroy', encrypt($item->id)) }}"
                                    class="btn btn-sm btn-danger">Delete</a>
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
