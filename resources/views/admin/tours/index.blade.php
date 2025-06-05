<x-admin>
    @section('title','Tours')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ translate('Tours') }}</h3>
            @can('add_tour') 
            <div class="card-tools">
                <a href="{{ route('admin.tour.create') }}" class="btn btn-sm btn-info">Create New Tour</a>
            </div>
            @endcan
        </div>
        <div class="card-body">
            <table class="table table-striped" id="tourTable">
                <thead>
                    <tr>
                        <th>{{ translate('Image') }}</th>
                        <th>{{ translate('Title') }} </th>
                        <th width="80">{{ translate('Price') }}</th>
                        <th width="80">{{ translate('Code') }}</th>
                        <th width="250">{{ translate('Category') }}</th>
                        <th width="100">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                   
                    @foreach ($data as $tour)
                        <tr>
                            <td>{!! main_image_html($tour->main_image?->id) !!}</td>
                            <td>
                                @can('edit_tour')     
                                <a class="text-info text-hover" href="{{ route('admin.tour.edit', encrypt($tour->id)) }}">{{ $tour->title }}</a>
                                <br> Written By : {{ $users->name }}
                               </td>
                                @else
                                {{ $tour->title }}
                               
                                @endcan
                            <td>{{ price_format($tour->price) }}</td>
                            <td>{{ $tour->unique_code }}</td>
                            <td>{{ $tour->category_names ?: 'No categories' }}</td>
                            <td>
                                @can('clone_tour')   
                                <a class="btn btn-sm btn-success confirm-clone" data-href="{{ route('admin.tour.clone', encrypt($tour->id)) }}">Clone</a>
                                @endcan
                                @can('delete_tour')  
                                <a class="btn btn-sm btn-danger confirm-delete" data-href="{{ route('admin.tour.destroy', encrypt($tour->id)) }}">{{translate('Delete')}}</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@section('modal')
<!-- clone modal -->
<div id="clone-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Clone Confirmation') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mt-1">{{ translate('Are you sure want to clone?') }}</p>
                <button type="button" class="btn btn-light mt-2" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <a id="clone-link" class="btn btn-success mt-2">{{ translate('Yes') }}</a>
            </div>
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
</div><!-- /.modal -->
@endsection
@section('js')
<script>
$(function() {
    $('#tourTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "responsive": true,
    });

    $(".confirm-clone").click(function (e) {
        e.preventDefault();
        var url = $(this).data("href");
        $("#clone-modal").modal("show");
        $("#clone-link").attr("href", url);
    });
});
</script>
@endsection
</x-admin>
