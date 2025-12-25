<x-admin>
    @section('title','Extra')
    <div class="extra-header card card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="card-title">Extra</h3>
                </div>
                <div class="col-md-4">
                    <div class="card-tools">
                        <a href="{{ route('admin.addon.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                    </div>
                </div>
            </div>            
        </div>
    </div>
    <div class="extra-addon-body">
        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-body p-0">
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
                                <!-- <td><img src="{{ asset('addon/'.$item->image) }}" alt="{{ $item->name }}" width="150" /></td> -->
                                <td>
                                    <img class="img-md" src="{{ uploaded_asset($item->image) }}" height="150"  alt="{{translate('photo')}}">
                                </td>
                                <td><a href="{{ route('admin.addon.edit', encrypt($item->id)) }}" class="text-info">{{ $item->name }}</a></td>
                                <td>{{ $item->description }}</td>
                                <td>{{ price_format($item->price) }}</td>
                                <td>{{ $item->customer_choice }}</td>
                                <td width="60">
                                    <!-- <a href="{{ route('admin.addon.edit', encrypt($item->id)) }}"
                                        class="btn btn-sm btn-primary">Edit</a> -->
                                    <a href="{{ route('admin.addon.destroy', encrypt($item->id)) }}"
                                        class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash-alt"></i></a>
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
