<x-admin>
    @section('title','Tours')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ translate('Tours') }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.tour.create') }}" class="btn btn-sm btn-info">Create New Tour</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="productTable">
                <thead>
                    <tr>
                        <th>{{ translate('Image') }}</th>
                        <th>{{ translate('Title') }}</th>
                        <th width="80">{{ translate('Price') }}</th>
                        <th width="80">{{ translate('Code') }}</th>
                        <th width="200">{{ translate('Category') }}</th>
                        <th width="100">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $tour)
                        <tr>
                            <td>{{ $tour->main_image_html(150) }}</td>
                            <td><a class="text-info" href="{{ route('admin.tour.edit', encrypt($tour->id)) }}">{{ $tour->title }}</a></td>
                            <td>{{ price_format($tour->price) }}</td>
                            <td>{{ $tour->unique_code }}</td>
                            <td>{{ $tour->category_names ?: 'No categories' }}</td>
                            <td>
                                <a class="btn btn-sm btn-success" href="{{ route('admin.tour.clone', encrypt($tour->id)) }}">Clone</a>
                                <a class="btn btn-sm btn-danger confirm-delete" data-href="{{ route('admin.tour.destroy', encrypt($tour->id)) }}">{{translate('Delete')}}</a>
                            </td>
                            <!-- <td width="60">
                                <form action="{{ route('admin.tour.destroy', encrypt($tour->id)) }}" method="POST" onsubmit="return confirm('Are sure want to delete?')">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td> -->
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
</div><!-- /.modal -->
    @section('js')
        <script>
            $(function() {
                $('#productTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "responsive": true,
                });
            });
        </script>
    @endsection
</x-admin>
