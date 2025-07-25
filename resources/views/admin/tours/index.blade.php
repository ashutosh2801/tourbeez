<x-admin>
    @section('title','Tours')
    <div class="card">

        <!-- Search Form (GET) -->
        <form method="GET" action="{{ route('admin.tour.index') }}">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                    <h3 class="card-title mb-0">{{ translate('Tours') }}</h3>

                    <div class="d-flex gap-2 flex">

                        {{-- Search Input --}}
                        <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search tour" value="{{ request('search') }}" />

                        {{-- Category Dropdown --}}
                        <select name="category" class="form-control form-control-sm mr-2">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Per Page Dropdown --}}
                        <select name="per_page" class="form-control form-control-sm mr-2">
                            @foreach ([10, 25, 50, 100] as $number)
                                <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                    {{ $number }} per page
                                </option>
                            @endforeach
                        </select>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    </div>
                </div>
            </div>
        </form>

        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.order.bulkDelete') }}">
        @csrf
        @method('DELETE')

        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3 class="card-title mb-0"></h3>
                <div class="card-tools">
                    @can('add_tour') 
                    <a href="{{ route('admin.tour.create') }}" class="btn btn-sm btn-info">Create New Tour</a>
                    @endcan
                    @can('delete_tour') 
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete selected orders?')">
                        Delete Selected
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-striped" id="tourTable">
                <thead>
                    <tr>
                        <th><input style="width:15px; height:15px;" type="checkbox" id="checkAll" /></th>
                        <th>{{ translate('Image') }}</th>
                        <th>{{ translate('Title') }}</th>
                        <th width="100">{{ translate('Price') }}</th>
                        <th width="150">{{ translate('Code') }}</th>
                        <th width="250">{{ translate('Category') }}</th>
                        <th width="150">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tours as $tour)
                        <tr>
                            <td><input style="width:15px; height:15px;" type="checkbox" name="ids[]" value="{{ $tour->id }}"></td>
                            <td>{!! main_image_html($tour->main_image?->id) !!}</td>
                            <td>
                                @can('edit_tour')     
                                <a class="text-info text-hover" href="{{ route('admin.tour.edit', encrypt($tour->id)) }}">{{ $tour->title }}</a>
                                @else
                                {{ $tour->title }}
                                @endcan

                                <div class="text-sm">{!! tour_status($tour->status) !!} | {{ ($tour->location?->city->name) }} | {{ ($tour->detail?->booking_type?? 'Other') }}</div>
                                <div class="text-sm text-gray-500"><i style="font-size:11px">By: {{ $tour->user->name }}</i></div>
                            </td>    
                            <td>USD&nbsp;{{ price_format_with_currency($tour->price) }}</td>
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
        <div class="card-footer">
            {{ $tours->links() }}
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
document.getElementById('checkAll').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});
</script>
<script>
$(function() {
    // $('#tourTable').DataTable({
    //     "paging": true,
    //     "searching": true,
    //     "ordering": true,
    //     "responsive": true,
    // });

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
