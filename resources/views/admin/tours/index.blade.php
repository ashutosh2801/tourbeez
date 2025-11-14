<x-admin>
    @section('title','Tours')
    <div class="card rounded-lg-custom border">

        <!-- Search Form (GET) -->
        <form class="my-0" id="filterForm" method="GET" action="{{ route('admin.tour.index') }}">
            <div class="card-header">
                <div class="search-options">
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <input type="text" name="search" class="form-control" placeholder="Search tour" value="{{ request('search') }}" />
                        </div>
                        <div class="col-md-4 col-6">
                            <select name="category" class="form-control select-searchable">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="city" id="city-select" class="form-control">
                                @if(request('city'))
                                    <option value="{{ request('city') }}" selected>{{ ucwords(optional(\App\Models\City::find(request('city')))->name) }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="author" class="form-control aiz-selectpicker" data-live-search="true">
                                <option value="">Select Author</option>
                                    @foreach ($users as $author)
                                        <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                                            {{ $author->name }}
                                        </option>
                                    @endforeach
                                
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                    <option value="0" {{ request('staus') === 0 ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="1" {{ request('staus') === 1 ? 'selected' : '' }}>
                                        Active
                                    </option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="special_deposit" class="form-control">
                                <option value="">Special Deposit</option>
                                @foreach (['Active','Not_Active'] as $special_deposit)
                                    <option value="{{ strtolower($special_deposit) }}" {{ request('special_deposit') == strtolower($special_deposit) ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', $special_deposit) }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="schedule" class="form-control">
                                <option value="">Schedule</option>
                                @foreach (['Active','Not_Active'] as $schedule)
                                    <option value="{{ strtolower($schedule) }}" {{ request('schedule') == strtolower($schedule) ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', $schedule) }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="trustpilot_review" class="form-control" onchange="this.form.submit()">
                                <option value="">TrustPilot Review</option>
                                    <option value="0" {{ request('trustpilot_review') === 0 ? 'selected' : '' }}>
                                        No
                                    </option>
                                    <option value="1" {{ request('trustpilot_review') === 1 ? 'selected' : '' }}>
                                        Yes
                                    </option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="schedule_expiry" class="form-control">
                                <option value="">Schedule Expiry</option>
                                <option value="today" {{ request('schedule_expiry') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="last_7" {{ request('schedule_expiry') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="last_15" {{ request('schedule_expiry') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                                <option value="this_week" {{ request('schedule_expiry') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="upcoming_15" {{ request('schedule_expiry') == 'upcoming_15' ? 'selected' : '' }}>Upcoming 15 Days</option>
                                <option value="expired" {{ request('schedule_expiry') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="per_page" class="form-control">
                                @foreach (['All',10, 25, 50, 100] as $number)
                                    <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                        {{ $number }} per page
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <button type="submit" class="btn btn-search"> <i class="fas fa-search"></i> Search</button>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('admin.tour.index')}}" class="btn-clear"> <i class="fas fa-times"></i> Clear Search</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        {{-- <form id="bulkDeleteForm" method="POST" action="{{ route('admin.tour.bulkDelete') }}">
        @csrf
        @method('DELETE')
         </form> --}}
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div class="card-tools">
                    
                    <a href="#" onclick="exportFilteredTours()" class="btn btn-selected"> <i class="fas fa-file-export"></i> Export Tours</a>
                    <button type="button" class="btn btn-selected" data-toggle="modal" data-target="#importPriceModal">
                        <i class="fas fa-file-import"></i> Import Price
                    </button>
                    <button type="button" class="btn btn-selected" data-toggle="modal" data-target="#markReviewModal">
                        <i class="fas fa-star"></i> Mark Review
                    </button>

                    <button id="enableDisableTour" type="button" class="btn btn-selected"> <i class="fas fa-sync"></i> Enable/Disable</button>

                    <button id="saveTourCoupon" type="button" class="btn btn-selected"> <i class="fas fa-tags"></i> Create Discount</button>
                    <button id="saveSortOrder" type="button" class="btn btn-selected"> <i class="fas fa-save"></i> Save Sort Order</button>

                    @can('add_tour') 
                    <a href="{{ route('admin.tour.create') }}" class="btn btn-selected"> <i class="fas fa-calendar-plus"></i> Create New Tour</a>
                    @endcan
                    @can('delete_tour') 
                    <button type="submit" class="btn btn-selected" onclick="return confirm('Are you sure to delete selected orders?')">
                        <i class="fas fa-trash-alt"></i> Delete Selected
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body p-0 tour-table">
            <div class="table-viewport">
                <table class="table table-striped" id="tourTable">
                    <thead>
                        <tr>
                            <th><input style="width:20px; height:20px;" type="checkbox" id="checkAll" /></th>
                            <th >{{ translate('Order') }}</th>
                            <th >{{ translate('Image') }}</th>
                            <th>{{ translate('Title') }}</th>
                            <th width="150">{{ translate('Price') }}</th>
                            <th width="150">{{ translate('SKU') }}</th>
                            <th width="10">{{ translate('Trustpilot Review') }}</th>
                            <th width="200">{{ translate('Category') }}</th>
                            <th width="150">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-tours">
                        @foreach ($tours as $tour)
                            <tr data-id="{{ $tour->id }}">
                                <td><input style="width:20px; height:20px;" type="checkbox" name="ids[]" value="{{ $tour->id }}"></td>

                                <td>
                                    <input type="hidden" name="tour_ids[]" value="{{ $tour->id }}" class="form-control">
                                    <input style="width:35px; height:35px;" class="form-control check-box" type="text" name="sort_order[{{ $tour->id }}]" value="{{ $tour->sort_order }}">
                                </td>

                                <td>{!! main_image_html($tour->main_image?->id) !!}</td>
                                <td>
                                    <div class="mb-2">
                                        {!! tour_status($tour->status) !!}
                                    </div>
                                    @can('edit_tour')     
                                    <a target="_blank" class="tour-heading" href="{{ $tour->parent_id ? route('admin.tour.sub-tour.edit', encrypt($tour->id)) : route('admin.tour.edit', encrypt($tour->id)) }}">{{ $tour->title }}</a>
                                    @else
                                    {{ $tour->title }}
                                    @endcan

                                    <div class="text-sm mt-2"> {{ ($tour->location?->city?->name) }} | {{ ($tour->detail?->booking_type?? 'Other') }} | <a href="https://tourbeez.com/tour/{{ $tour->slug }}" class="text-success text-hover" target="_blank">{{translate('View Online')}}</a> | <a href="{{ route('admin.tour.sub-tour.index', encrypt($tour->id)) }}" class="text-success text-hover" target="_blank">{{ $tour->subTours()->exists() ? translate('View Sub Tours') : translate('Create Sub Tours')}}</a></div>
                                    <div class="text-sm text-gray-500 mt-2"><i style="font-size:11px">By: {{ $tour->user->name }} </i></div>
                                </td>    
                                <td>{{ price_format_with_currency($tour->price) }}</td>
                                <td>{{ $tour->unique_code }}</td>
                                <td class="text-center">{{ $tour->trustpilot_review ? 'Yes' : 'No' }}</td>
                                <td>{{ $tour->category_names ?: 'No categories' }}</td>
                                <td>
                                    @can('clone_tour')   
                                    <a class="btn btn-sm btn-success confirm-clone" data-href="{{ route('admin.tour.clone', encrypt($tour->id)) }}"><i class="fas fa-clone"></i></a>
                                    @endcan
                                    @can('delete_tour')  
                                    <a class="btn btn-sm btn-danger confirm-delete" data-href="{{ route('admin.tour.destroy', encrypt($tour->id)) }}"><i class="fas fa-trash-alt"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="card-footer">
                    {{ $tours->links() }}
                </div>
            </div>
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
</div>

<!-- Tour Coupon Modal -->
<!-- Tour Coupon Modal -->
<div id="tour-coupon-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Create Discount') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>

            <div class="form-group">
                <label class="ml-3 mt-2">{{ translate('Selected Tours') }}</label>
                <div id="selected_tour_list" class="border p-2 rounded bg-light">
                    <!-- dynamic list will appear here -->
                </div>
            </div>

            <form id="tourCouponForm" method="POST" action="{{ route('admin.tour.saveCoupon') }}">
                @csrf
                <input type="hidden" name="selected_tours" id="selected_tours">


                <div class="modal-body">
                    <!-- Coupon Type -->
                    <div class="form-group">
                        <label for="coupon_type">{{ translate('Coupon Type') }}</label>
                        <select name="coupon_type" id="coupon_type" class="form-control">
                            <option value="percentage">{{ translate('Percentage') }}</option>
                            <option value="fixed">{{ translate('Fixed Amount') }}</option>
                        </select>
                    </div>

                    <!-- Coupon Value -->
                    <div class="form-group">
                        <label for="coupon_value">{{ translate('Value') }}</label>
                        <input type="number" step="0.01" name="coupon_value" id="coupon_value" class="form-control" placeholder="Enter value">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ translate('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Enable/Disable Tour Modal -->
<!-- Enable/Disable Tour Modal -->
<div id="enable-disable-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Enable/Disable Tours') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>

            <div class="form-group">
                <label class="ml-3 mt-2">{{ translate('Selected Tours') }}</label>
                <div id="enable_disable_tour_list" class="border p-2 rounded bg-light">
                    <!-- dynamic list will appear here -->
                </div>
            </div>

            <form id="enableDisableForm" method="POST" action="{{ route('admin.tour.toggleStatus') }}">
                @csrf
                <input type="hidden" name="selected_tours" id="enable_disable_selected_tours">
                <input type="hidden" name="bulk_status" id="bulk_status">

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-success" onclick="$('#bulk_status').val(1)">{{ translate('Enable') }}</button>
                    <button type="submit" class="btn btn-danger" onclick="$('#bulk_status').val(2)">{{ translate('Disable') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Price Modal -->
<div id="importPriceModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Import Tour Prices') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>

            <form method="POST" action="{{ route('admin.tours.importPrice') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    
                    <p>Upload a Excel file with columns: <strong>SKU</strong>, <strong>price</strong></p>

                    <div class="form-group">
                        <label for="file">Select File</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".csv,.xlsx,.xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="downloadSample">
                        <i class="fas fa-file-excel"></i> Download Sample Excel
                    </button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ translate('Import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark Review Modal -->
<div id="markReviewModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title h6">{{ translate('Mark Trustpilot Review') }}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
      </div>

      <form method="POST" action="{{ route('admin.tours.markReview') }}">
        @csrf
        <div class="modal-body">
          <p>Enter one or more <strong>SKUs (comma-separated)</strong> to mark them as reviewed.</p>
          <div class="form-group">
            <textarea name="skus" class="form-control" rows="4" placeholder="e.g., TOUR123, TOUR456, TOUR789" required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
          <button type="submit" class="btn btn-success">{{ translate('Mark Reviewed') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>










<!-- /.modal -->

@endsection
@section('js')

{{-- Include Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- Include Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


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
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
    // $(document).ready(function() {
    //     $('.select2').select2({
    //         placeholder: 'Select a city',
    //         allowClear: true,
    //         width: '100%' // ensures it matches Bootstrap input width
    //     });
    // });


    //  $(document).ready(function () {
    //     $('#city-select').select2({
    //         placeholder: 'Select a city',
    //         width: 'style'
    //     });
    // });



$(document).ready(function () {
    $('#city-select').select2({
        placeholder: 'Select a city',
        ajax: {
            url: '{{ route("admin.city.search") }}',
            dataType: 'json',
            delay: 300,
            width: '250px',
            dropdownAutoWidth: true,
            dropdownParent: $('#city-select').parent(),
            data: function (params) {
                return { term: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        minimumInputLength: 2,
    });
});
</script>

<script>
    $(document).ready(function() {
        $('.select-searchable').select2({
            placeholder: "Select a category",
            allowClear: true,
            width: 'resolve'
        });
    });
</script>


<script>
    $(function () {
        $("#sortable-tours").sortable({
            handle: "td", // You can change this to a specific handle like ".handle"
            update: function () {
                let order = [];
                $("#sortable-tours tr").each(function () {
                    order.push($(this).data("id"));
                });
                console.log(order);
                $.ajax({
                    url: "{{ route('admin.tour.reorder') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        order: order
                    },
                    success: function () {
                        alert('Sort order updated!');
                        console.log('Order updated');
                    },
                    error: function () {
                        alert('Failed to update tour order.');
                    }
                });
            }
        });
    });
</script>

<script>
    $('#saveSortOrder').click(function () {
        let sortedData = [];

        $('#sortable-tours tr').each(function () {
            const tourId = $(this).data('id');
            const sortOrder = $(this).find('input[name^="sort_order"]').val();
            sortedData[sortOrder] = tourId;

        });


        $.ajax({
            url: "{{ route('admin.tour.reorder') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                order: sortedData
            },
            success: function (response) {
                alert('Sort order updated!');
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error saving sort order.');
            }
        });
    });



</script>

<script>
    

// $(document).on("click", "#saveTourCoupon", function (e) {
//     e.preventDefault(); // 🔥 Prevent form submission
    
//     let selected = [];
//     let selectedNames = [];

//     $('input[name="ids[]"]:checked').each(function () {
//         selected.push($(this).val());
//         selectedNames.push($(this).closest('tr').find('td:nth-child(4)').text().trim()); // Get tour title
//     });

//     if (selected.length === 0) {
//         alert("Please select at least one tour.");
//         return;
//     }

//     // Pass selected tours into hidden field
//     $("#selected_tours").val(selected.join(","));

//     // Show selected tours in modal
//     let listHtml = "<ul>";
//     selectedNames.forEach(name => listHtml += `<li>${name}</li>`);
//     listHtml += "</ul>";
//     $("#selected_tour_list").html(listHtml);

//     $("#tour-coupon-modal").modal("show");
// });


    $(document).on("click", "#saveTourCoupon", function (e) {
        e.preventDefault(); 
        
        let selected = [];
        let selectedNames = [];

        $('input[name="ids[]"]:checked').each(function () {
            selected.push($(this).val());
            selectedNames.push({
                id: $(this).val(),
                name: $(this).closest('tr').find('td:nth-child(4)').text().trim()
            });
        });

        if (selected.length === 0) {
            alert("Please select at least one tour.");
            return;
        }

        // Show selected tours in modal (with remove option)
        let listHtml = `
    <div style="max-height:250px; overflow-y:auto;">
        <ul class="list-group">`;
        selectedNames.forEach(item => {
            listHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${item.id}">
                    <span class"mr-3" style="margin-right : 10px">${item.name}</span>
                    <button type="button" class="btn btn-sm btn-danger remove-tour" data-id="${item.id}">&times;</button>
                </li>`;
        });
        listHtml += `</ul> </div>`;


        $("#selected_tour_list").html(listHtml);

        // Save IDs in hidden input
        $("#selected_tours").val(selected.join(","));

        // Show modal
        $("#tour-coupon-modal").modal("show");
    });

    // 🔹 Handle remove click
    $(document).on("click", ".remove-tour", function () {
        let id = $(this).data("id");

        // Remove item from list
        $(this).closest("li").remove();

        // Update hidden field
        let remaining = [];
        $("#selected_tour_list li").each(function () {
            remaining.push($(this).data("id"));
        });
        $("#selected_tours").val(remaining.join(","));
    });


    $(document).on("click", "#enableDisableTour", function (e) {
        e.preventDefault(); 
        
        let selected = [];
        let selectedNames = [];

        $('input[name="ids[]"]:checked').each(function () {
            selected.push($(this).val());
            selectedNames.push($(this).closest('tr').find('td:nth-child(4)').text().trim());
        });

        if (selected.length === 0) {
            alert("Please select at least one tour.");
            return;
        }

        // Show selected tours in modal
       let listHtml = `
    <div style="max-height:250px; overflow-y:auto;">
        <ul class="list-group">`;
        selectedNames.forEach(name => {
            listHtml += `<li class="list-group-item">${name}</li>`;
        });
        listHtml += `</ul> </div>`;

        $("#enable_disable_tour_list").html(listHtml);

        // Save IDs in hidden input
        $("#enable_disable_selected_tours").val(selected.join(","));

        // Show modal
        $("#enable-disable-modal").modal("show");
    });





</script>
<script>
function exportFilteredTours() {
    const form = document.getElementById('filterForm');
    const params = new URLSearchParams(new FormData(form)).toString();
    window.location.href = "{{ route('admin.tours.export') }}?" + params;
}
</script>
<script>
    document.getElementById('downloadSample').addEventListener('click', function () {
        window.location.href = "{{ route('admin.tours.sample.download') }}";
    });
</script>

@endsection
</x-admin>
