<x-admin>
    @section('title','Banners')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Banners</h3>
            <div class="card-tools">
                <a href="{{ route('admin.banners.create') }}" class="btn btn-sm btn-info">Create New</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="bannersTable">
                <thead>
                    <tr>
                        <th>Location ID</th>
                        <th>Heading</th>
                        <th>Sub Heading</th>
                        <!-- <th>Images</th> -->
                        <!-- <th>Videos</th> -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $item->location_id ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.banners.edit', encrypt($item->id)) }}" class="text-info text-sm">
                                    {{ $item->heading }}
                                </a>
                            </td>
                            <td>{{ $item->sub_heading }}</td>
                            
                            <td width="140">
                                <a href="{{ route('admin.banners.edit', encrypt($item->id)) }}" class="text-info text-sm btn btn-sm btn-danger ">
                                    {{ translate('Edit') }}
                                </a>
                                <a class="btn btn-sm btn-danger confirm-delete" 
                                   data-href="{{ route('admin.banners.destroy', encrypt($item->id)) }}">
                                   {{ translate('Delete') }}
                                </a>
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
            $('#bannersTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "responsive": true,
            });
        });
    </script>
    @endsection
</x-admin>
