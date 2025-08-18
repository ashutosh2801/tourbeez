<style>
    /* Match the height, border, font-size, and padding */
/* Main select2 container styling to match default dropdown */
.select2-container--default .select2-selection--single {
    border: 1px solid #ced4da !important; /* Match Bootstrap or standard form control border */
    background-color: #fff !important;
    border-radius: 4px !important;
    padding: 6px 12px !important;
    padding-top: 9px !important;
    height: 39px !important;
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #495057;
    box-shadow: none !important;
    widows: 150px !important;
}

/* Ensure focus style also matches */
.select2-container--default .select2-selection--single:focus,
.select2-container--default .select2-selection--single:hover {
    border: 1px solid #86b7fe !important; /* Same as focused input */
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25) !important; /* Optional */
}

/* Adjust the dropdown arrow positioning */
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 10px;
    top: 0;
    transform: none;
}

/* Prevent double borders or overlaps */
.select2-selection {
    border: none !important;
}

.drag-handle {
    cursor: move;
    font-size: 18px;
    color: #888;
}

.select2-selection__clear{
    /*display: none;*/
    height: 0px !important;
}



</style>

<x-admin>
    @section('title','Tours')
    <div class="card">

        <!-- Search Form (GET) -->
        <form method="GET" action="{{ route('admin.tour.index') }}">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                    <h3 class="card-title mb-0">{{ translate('Tours') }}</h3>

                    <div class="d-flex gap-2 flex">

                        {{-- City Dropdown --}}

                    <select name="city" id="city-select" class="form-control form-control-sm">
                        @if(request('city'))
                            <option value="{{ request('city') }}" selected>{{ ucwords(optional(\App\Models\City::find(request('city')))->name) }}</option>
                        @endif
                    </select>


                        {{-- Search Input --}}
                        <input type="text" name="search" class="ml-2 form-control form-control-sm mr-2" placeholder="Search tour" value="{{ request('search') }}" />

                        {{-- Category Dropdown --}}

                        <select name="category" class="form-control form-control-sm mr-2 select-searchable">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Per Page Dropdown --}}
                        <select name="per_page" class="ml-2 form-control form-control-sm mr-2">
                            @foreach (['All',10, 25, 50, 100] as $number)
                                <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                    {{ $number }} per page
                                </option>
                            @endforeach
                        </select>

                        <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="">All Status</option>
                                <option value="0" {{ request('staus') === 0 ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="1" {{ request('staus') === 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                            
                        </select>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        <!-- <a href="{{ route('admin.tour.index')}}" class="btn btn-outline-secondary btn-sm">Clear</a> -->
                    </div>
                </div>
            </div>
        </form>
        
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.tour.bulkDelete') }}">
        @csrf
        @method('DELETE')
         </form>
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3 class="card-title mb-0"></h3>
                <div class="card-tools">

                    <button id="saveTourCoupon" type="button" class="btn btn-sm btn-info">Create Discount</button>
                    <button id="saveSortOrder" type="button" class="btn btn-sm btn-primary">Save Sort Order</button>

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
                        <th >{{ translate('Order') }}</th>
                        <th >{{ translate('Image') }}</th>
                        <th>{{ translate('Title') }}</th>
                        <th width="150">{{ translate('Price') }}</th>
                        <th width="150">{{ translate('Code') }}</th>
                        <th width="150">{{ translate('Category') }}</th>
                        <th width="150">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="sortable-tours">
                    @foreach ($tours as $tour)
                        <tr data-id="{{ $tour->id }}">
                            <td><input style="width:15px; height:15px;" type="checkbox" name="ids[]" value="{{ $tour->id }}"></td>

                            <td>
                                  <input type="hidden" name="tour_ids[]" value="{{ $tour->id }}">
                                  <input style="width:35px; height:35px;" type="text" name="sort_order[{{ $tour->id }}]" value="{{ $tour->sort_order }}">
                                </td>


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
                            <td>{{ price_format_with_currency($tour->price) }}</td>
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
        width: '100%' // or 'resolve'
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





</script>

@endsection
</x-admin>
