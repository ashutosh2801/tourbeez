<x-admin>
    @section('title','Banners')
    <div class="card-primary mb-3">
        <div class="card-header banner-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h5 class="card-title">Banners</h5>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <a href="{{ route('admin.banners.create') }}" class="btn btn-sm btn-success">+ Create New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-primary bg-white border rounded-lg-custom banner-main-body">
        <div class="card-body p-0">
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
                            <td><span class="btn-name">{{ $item->location_id ?? '-' }}</span></td>
                            <td>
                                <a href="{{ route('admin.banners.edit', encrypt($item->id)) }}" class="text-info text-sm">
                                    {{ $item->heading }}
                                </a>
                            </td>
                            <td>{{ $item->sub_heading }}</td>
                            
                            <td width="140">
                                <a href="{{ route('admin.banners.edit', encrypt($item->id)) }}" class="btn btn-sm btn-edit">
                                    <i class="far fa-edit"></i>
                                </a>
                                <a class="btn btn-sm btn-danger confirm-delete" 
                                   data-href="{{ route('admin.banners.destroy', encrypt($item->id)) }}">
                                   <i class="fas fa-trash-alt"></i>
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
