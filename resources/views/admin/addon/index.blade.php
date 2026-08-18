<x-admin>
    @section('title','Extra')
    <div class="extra-header card card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Extra</h3>
                </div>
                <div class="col-md-4 col-6">
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
                <div class="p-3 pb-0">@include('admin.partials.table-search', ['tableId' => 'categoryTable', 'title' => 'extras', 'placeholder' => 'Name, description, price or type'])</div>
                <table class="table table-striped" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th width="150">Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Selling Price</th>
                            <th width="80">Price Type</th>
                            <th class="text-center">Actions</th>
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
                                <td>{{ price_format_with_currency($item->price, $item->currency) }}</td>
                                <td>{{ price_format_with_currency($item->selling_price, $item->currency) }}</td>
                                <td>{{ $item->customer_choice }}</td>
                                <td width="60" class="text-center admin-table-actions">
                                    <!-- <a href="{{ route('admin.addon.edit', encrypt($item->id)) }}"
                                        class="btn btn-sm btn-primary">Edit</a> -->
                                    <a href="{{ route('admin.addon.destroy', encrypt($item->id)) }}"
                                        class="btn btn-sm btn-danger btn-delete admin-table-action"><i class="fas fa-trash-alt"></i></a>
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
