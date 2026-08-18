<x-admin>
    @section('title','Pickups')
    <div class="card-primary mb-3">
        <div class="card-header pickups-header">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title">Pickups</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.pickups.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-primary bg-white border rounded-lg-custom">
        <div class="card-body p-0">
            <div class="p-3 pb-0">@include('admin.partials.table-search', ['tableId' => 'pickupTable', 'title' => 'pickups', 'placeholder' => 'Name, location or address'])</div>
            <div class="table-viewport">
                <table class="table table-striped" id="pickupTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>From and To locations</th>
                            <th width="150">Total locations</th>
                            <!-- <th width="120">Used by</th> -->
                            <!-- <th>Price</th> -->
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)

                            <tr>
                                <td><a href="{{ route('admin.pickups.edit', encrypt($item->id)) }}" class="text-info text-sm">{{ $item->name }}</a></td>
                                <td>
                                    @foreach ($item->locations as $location)
                                        <p class="m-0 text-sm text-gray-100 border-b border-gray-600 p-2">{{ $location->location }}, {{ $location->address }} - <span class="font-bold">{{ price_format_with_currency($location->pickup_charge) }}</span></p>
                                    @endforeach
                                </td>
                                <td>{{ count($item->locations) }}</td>
                                <!-- <td>{{ count($item->locations) }}</td>
                                <td>{{ price_format_with_currency($item->pickup_charge) }}</td> -->
                                <td width="60" class="text-center admin-table-actions">
                                    <a class="btn btn-sm btn-danger confirm-delete admin-table-action" title="Delete pickup" data-href="{{ route('admin.pickup.destroy', encrypt($item->id)) }}"> <i class="fas fa-trash-alt"></i></a>
                                </td>                            
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- delete Modal -->
    <div id="delete-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ translate('Delete Confirmation') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mt-1">{{ translate('Are you sure to delete this?') }}</p>
                    <button type="button" class="btn btn-light mt-2" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <a id="delete-link" class="btn btn-danger mt-2">{{ translate('Delete') }}</a>
                </div>
            </div>
        </div>
    </div>

@section('js')
<script>
    $(function() {
        $('#pickupTable').DataTable({
            "paging": false,
            "searching": true,
            "ordering": true,
            "responsive": true,
            "info": false,
            "dom": "rt"
        });
        $('[data-table-search="pickupTable"]').on('input', 'input', function(){ $('#pickupTable').DataTable().search(this.value).draw(); }).on('submit', function(e){ e.preventDefault(); });
    });
</script>
@endsection
</x-admin>
