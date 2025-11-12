<x-admin>
    @section('title','Pickups')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Pickups</h3>
            <div class="card-tools">
                <a href="{{ route('admin.pickups.create') }}" class="btn btn-sm btn-info">Create New</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="pickupTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>From and To locations</th>
                        <th width="150">Total locations</th>
                        <th width="120">Used by</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)

                        <tr>
                            <td><a href="{{ route('admin.pickups.edit', encrypt($item->id)) }}" class="text-info text-sm">{{ $item->name }}</a></td>
                            <td>
                                @foreach ($item->locations as $location)
                                    <p class="m-0 text-sm text-gray-100">{{ $location->location }}, {{ $location->address }}</p>
                                @endforeach
                            </td>
                            <td>{{ count($item->locations) }}</td>
                            <td>{{ count($item->locations) }}</td>
                            <td>{{ price_format_with_currency($item->price) }}</td>
                            <td width="60">
                                <a class="btn btn-sm btn-danger confirm-delete" data-href="{{ route('admin.pickup.destroy', encrypt($item->id)) }}">
                                {{translate('Delete')}}</a>
                            </td>                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
            "paging": true,
            "searching": true,
            "ordering": true,
            "responsive": true,
        });
    });
</script>
@endsection
</x-admin>
