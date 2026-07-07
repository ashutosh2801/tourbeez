
<x-admin>

<style>

.tour-action-btn{
    background:#2563eb !important;
    color:#ffffff !important;
    border:none !important;
    padding:6px 14px;
    font-size:13px;
    font-weight:500;
    border-radius:6px;
    cursor:pointer;
    transition:all .2s ease;
}

.tour-action-btn i{
    margin-right:4px;
}

.tour-action-btn:hover{
    background:#1d4ed8 !important;
    box-shadow:0 3px 8px rgba(37,99,235,0.25);
}

.tour-action-btn:active{
    transform:scale(0.97);
}


/* Expand row */

.tour-expand-row{
    display:none;
}


/* Expanded panel */

.tour-actions{
    background:#f8fafc;
    padding:16px;
    border-top:1px solid #e5e7eb;

    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(140px,1fr));
    gap:10px;
}


/* Links */

.tour-actions a{
    display:flex;
    align-items:center;
    gap:8px;

    padding:8px 10px;
    font-size:13px;

    background:#eef2ff;      /* light blue default */
    border:1px solid #c7d2fe;
    border-radius:6px;

    text-decoration:none;
    color:#3730a3;

    transition:all .15s ease;
}

.tour-actions a:hover{
    background:#e0e7ff;
    border-color:#6366f1;
    color:#312e81;
}


.tour-modal{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.45);
display:none;
align-items:center;
justify-content:center;
z-index:9999;
}

.tour-modal-content{
background:#fff;
width:700px;
max-width:90%;
border-radius:8px;
overflow:hidden;
}

.tour-modal-header{
display:flex;
justify-content:space-between;
align-items:center;
padding:14px 18px;
border-bottom:1px solid #eee;
font-weight:600;
}

.tour-modal-header button{
border:none;
background:none;
font-size:18px;
cursor:pointer;
}

.tour-actions{
padding:20px;

display:grid;
grid-template-columns:repeat(auto-fill,minmax(150px,1fr));
gap:10px;
}

.tour-actions a{
    display:flex;
    align-items:center;
    gap:8px;

    padding:8px 10px;
    font-size:13px;

    background:#eef2ff;      /* light blue default */
    border:1px solid #c7d2fe;
    border-radius:6px;

    text-decoration:none;
    color:#3730a3;

    transition:all .15s ease;
}

.tour-actions a:hover{
    background:#e0e7ff;
    border-color:#6366f1;
    color:#312e81;
}

</style>
  
@section('title','Tours')
<div class="tour-main-body card rounded-lg-custom border">

    <!-- Search Form (GET) -->
    <form class="my-0" id="filterForm" method="GET" action="{{ route('admin.tour.index') }}">
        <div class="card-header">
            <div class="search-options">
                <div class="row">
                    <div class="col-md-2 col-6">
                        <input type="text" name="search" class="form-control" placeholder="Search tour" value="{{ request('search') }}" />
                    </div>                        
                    <div class="col-md-2 col-6">
                        <input placeholder="date range" class="form-control datarange-pickur" type="text" />
                    </div>
                    <div class="col-md-2 col-6">
                        <select name="city" id="city-select" class="form-control">
                            @if(request('city'))
                                <option value="{{ request('city') }}" selected>{{ ucwords(optional(\App\Models\City::find(request('city')))->name) }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select name="category" id="category-select" class="form-control">
                            @if(request('category'))
                                <option value="{{ request('category') }}" selected>{{ ucwords(optional(\App\Models\Category::find(request('category')))->name) }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select name="author" class="form-control aiz-selectpicker" data-live-search="true">
                            <option value="">Select Author</option>
                                @foreach ($users as $author)
                                    <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                                        {{ ucwords($author->name) }}
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
                        <select name="last_updated" class="form-control">
                            <option value="">Last updated</option>
                            <option value="today" {{ request('last_updated') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="last_7" {{ request('last_updated') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_15" {{ request('last_updated') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                            <option value="this_week" {{ request('last_updated') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="upcoming_15" {{ request('last_updated') == 'upcoming_15' ? 'selected' : '' }}>Upcoming 15 Days</option>
                            <option value="expired" {{ request('last_updated') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 col-6">
                        <select name="has_sub_tour" class="form-control">
                            <option value="">Has Sub Tour</option>
                            @foreach (['Yes','No'] as $hasSubTour)
                                <option value="{{ strtolower($hasSubTour) }}" {{ request('has_sub_tour') == strtolower($hasSubTour) ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', $hasSubTour) }} 
                                </option>
                            @endforeach
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
                        <button type="submit" class="btn btn-search mb-2"> <i class="fas fa-search"></i> Search</button>
                    </div>
                    <div class="col-md-2 col-6">
                        <a href="{{ route('admin.tour.index')}}" class="btn-clear"> <i class="fas fa-times"></i> Clear Search</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="card-tools btn-options">
                @can('add_tour') 
                <a href="{{ route('admin.tour.create') }}" class="btn btn-success btn-create"> <i class="fas fa-calendar-plus"></i> Create New Tour</a>
                @endcan
                
                <button type="button" class="btn btn-warning btn-UpdatePrice" id="updatePrice">
                    <i class="fas fa-dollar-sign"></i> Update Price
                </button>
                <button type="button" class="btn btn-MarkReview" data-toggle="modal" data-target="#markReviewModal">
                    <i class="fas fa-star"></i> Mark Review
                </button>

                <button id="enableDisableTour" type="button" class="btn btn-enable"> <i class="fas fa-sync"></i> Enable/Disable</button>

                <button id="saveTourCoupon" type="button" class="btn btn-discount"> <i class="fas fa-tags"></i> Create Discount</button>
                <button id="saveSortOrder" type="button" class="btn btn-success btn-save"> <i class="fas fa-save"></i> Save Sort Order</button>

                <a href="#" onclick="exportFilteredTours()" class="btn btn-ExpoImpo"> <i class="fas fa-file-export"></i> Export Tours</a>
                <button type="button" class="btn btn-ExpoImpo" data-toggle="modal" data-target="#importPriceModal">
                    <i class="fas fa-file-import"></i> Import Price
                </button>
                
                @can('delete_tour') 
                <button type="submit" class="btn btn-danger btn-delete" onclick="return confirm('Are you sure to delete selected orders?')">
                    <i class="fas fa-trash-alt"></i> Delete Selected
                </button>
                @endcan
                
            </div>
        </div>
    </div>

    <div class="card-body p-0 tour-table">
        <div class="table-viewport">
            <table class="table table-striped" id="tourTable">
                <thead class="table-light">
                <tr>

                <th style="width:4%; text-align:center;">
                    <input type="checkbox" id="checkAll" class="table-checkbox" style="width:20px; height:20px;" />
                </th>

                <th style="width:4%;">{{ translate('Order') }}</th>

                <th style="width:6%;">{{ translate('Image') }}</th>

                <th style="width:32%;">{{ translate('Title') }}</th>

                <th style="width:12%;">{{ translate('Price') }}</th>

                <th style="width:12%;">{{ translate('SKU') }}</th>

                <!-- <th style="width:2%; text-align:center;">{{ translate('Reviews') }}</th> -->

                <th style="width:14%;">{{ translate('Category') }}</th>

                <th style="width:14%;">{{ translate('Actions') }}</th>

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
                                <div class="text-sm text-gray-500 mt-2"><i style="font-size:11px"><b>By:</b> {{ $tour->user->name }} </i> <i style="font-size:13px"><b>at:</b> {{ $tour->updated_at }}</i></div>
                            </td>  

                            
                            <td>{{ price_format_with_currency($tour->price, $tour->currency) }}</td>
                            <td>{{ $tour->unique_code }}</td>
                            <!-- <td class="text-center">{{ $tour->trustpilot_review ? 'Yes' : 'No' }}</td> -->
                            <td>{{ $tour->category_names ?: 'No categories' }}</td>
                            <td>
                                @can('clone_tour')   
                                <a class="btn btn-sm btn-success confirm-clone" data-href="{{ route('admin.tour.clone', encrypt($tour->id)) }}"><i class="fas fa-clone"></i></a>
                                @endcan
                                @can('delete_tour')  
                                <a class="btn btn-sm btn-danger confirm-delete" data-href="{{ route('admin.tour.destroy', encrypt($tour->id)) }}"><i class="fas fa-trash-alt"></i></a>
                                @endcan
                                <button class="btn btn-sm btn-primary tour-menu-btn mt-1" onclick="openTourMenu({{ $tour->id }}, '{{ addslashes($tour->title) }}')">
                                    <i class="fas fa-layer-group"></i> Tour Menu
                                </button>
                                
                                                                    
                            </td>
                        </tr>
                        
                        <div id="tour-menu-{{ $tour->id }}"  style="display:none">
                            

                            <a target="_blank" href="{{ route('admin.tour.edit', encrypt($tour->id)) }}">
                            <i class="fas fa-info-circle"></i> Basic
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.addone', encrypt($tour->id)) }}">
                            <i class="fas fa-plus-circle"></i> Extra
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.scheduling', encrypt($tour->id)) }}">
                            <i class="fas fa-calendar"></i> Scheduling
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.location', encrypt($tour->id)) }}">
                            <i class="fas fa-map-marker-alt"></i> Location
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.pickups', encrypt($tour->id)) }}">
                            <i class="fas fa-bus"></i> Pickups
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.itinerary', encrypt($tour->id)) }}">
                            <i class="fas fa-route"></i> Itinerary
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.faqs', encrypt($tour->id)) }}">
                            <i class="fas fa-question-circle"></i> FAQs
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.inclusions', encrypt($tour->id)) }}">
                            <i class="fas fa-check-circle"></i> Inclusions
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.exclusions', encrypt($tour->id)) }}">
                            <i class="fas fa-times-circle"></i> Exclusions
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.optionals', encrypt($tour->id)) }}">
                            <i class="fas fa-plus"></i> Optional
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.taxesfees', encrypt($tour->id)) }}">
                            <i class="fas fa-receipt"></i> Taxes
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.gallery', encrypt($tour->id)) }}">
                            <i class="fas fa-images"></i> Gallery
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.message.notification', encrypt($tour->id)) }}">
                            <i class="fas fa-envelope"></i> Message
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.booking', encrypt($tour->id)) }}">
                            <i class="fas fa-ticket-alt"></i> Booking
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.partner', encrypt($tour->id)) }}">
                            <i class="fas fa-handshake"></i> Partner
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.seo', encrypt($tour->id)) }}">
                            <i class="fas fa-search"></i> SEO
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.special.deposit', encrypt($tour->id)) }}">
                            <i class="fas fa-dollar-sign"></i> Special Deposit
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.review', encrypt($tour->id)) }}">
                            <i class="fas fa-star"></i> Review
                            </a>

                            <a target="_blank" href="{{ route('admin.tour.edit.parent', encrypt($tour->id)) }}">
                            <i class="fas fa-layer-group"></i> Parent
                            </a>
                            <a target="_blank" href="{{ route('admin.tour.edit.schedule-pricing', encrypt($tour->id)) }}">
                            <i class="fas fa-dollar-sign"></i> Price Scheduling
                            </a>
                            
                            </div>
                        
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
                    
                    <!-- <p>Upload a Excel file with columns: <strong>SKU</strong>, <strong>price</strong></p> -->
                    <div class="form-group">
                        <label>Import Type</label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="tour_pricing">Tour Pricing</option>
                            <option value="addon">Addon Pricing</option>
                        </select>
                    </div>

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

<div id="tourMenuModal" class="tour-modal">
    <div class="tour-modal-content">

    <div class="tour-modal-header">
    <span id="tourMenuTitle">Tour Menu</span>
    <button onclick="closeTourMenu()">✕</button>
    </div>

    <div id="tourMenuContent" class="tour-actions"></div>

    </div>
</div>
<!-- /.modal -->

<!-- Tour Price Update Modal -->
<div id="updatePriceModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width:80%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h4">{{ translate('Tour Price Update') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>

            <div class="row">
                <div class="col-md-8 col-6">
                    <div class="form-group p-3">
                        <label class="ml-3 mt-2">{{ translate('Selected Tours') }}</label>
                        <div id="selected_tour_list" class="border p-2 rounded bg-light">
                            <!-- dynamic list will appear here -->
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <form id="tourPriceUpdateForm" method="POST" action="{{ route('admin.tour.updatePrices') }}">
                        @csrf
                        <input type="hidden" name="selected_tours" id="selected_tours">

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="price_action">{{ translate('Price Action') }}</label>
                                <select name="price_action" id="price_action" class="form-control" required>
                                    <option value="INCREASE">{{ translate('Increase - (+)') }}</option>
                                    <option value="DECREASE">{{ translate('Decrease - (-)') }}</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-6">
                                    <!-- Price Type -->
                                    <div class="form-group">
                                        <label for="price_type">{{ translate('Price Type') }}</label>
                                        <select name="price_type" id="price_type" class="form-control" required>
                                            <option value="PERCENT">{{ translate('Percentage - (%)') }}</option>
                                            <option value="FIXED">{{ translate('Fixed Amount - ($)') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-6">
                                    <!-- Coupon Value -->
                                    <div class="form-group">
                                        <label for="price_value">{{ translate('Value') }}</label>
                                        <input type="number" step="0.01" name="price_value" id="price_value" class="form-control" placeholder="5" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="is_infant"><input type="checkbox" name="is_infant" id="is_infant"> {{ translate('Update Infant pricing also') }}</label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                            <button type="submit" class="btn btn-success">{{ translate('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            
        </div>
    </div>
</div>

@endsection
@section('js')

{{-- Include Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- Include Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('checkAll').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});
</script>

<script>
$(function() {
    $(".confirm-clone").click(function (e) {
        e.preventDefault();
        var url = $(this).data("href");
        $("#clone-modal").modal("show");
        $("#clone-link").attr("href", url);
    });
});
</script>

<script>
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

    $('#category-select').select2({
        placeholder: 'Select a category',
        ajax: {
            url: '{{ route("admin.category.search") }}',
            dataType: 'json',
            delay: 300,
            width: '250px',
            dropdownAutoWidth: true,
            dropdownParent: $('#category-select').parent(),
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
        handle: "td"
    });
});

$('#saveSortOrder').click(function () {
    let sortedData = [];

    $('#sortable-tours tr').each(function () {
        const tourId = $(this).data('id');
        sortedData.push(tourId);
    });

    let filters = $('#filterForm').serializeArray();

    let data = {
        _token: '{{ csrf_token() }}',
        order: sortedData
    };

    filters.forEach(f => {
        data[f.name] = f.value;
    });

    $.ajax({
        url: "{{ route('admin.tour.reorder') }}",
        type: "POST",
        data: data,
        success: function () {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Sort order updated!'
            });
            // location.reload();
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Error saving sort order'
            });
        }
    });
});
</script>

<script>
   $('#saveSortOrder').click(function () {
        let sortedData = [];

        $('#sortable-tours tr').each(function (index) {
            const tourId = $(this).data('id');
            sortedData.push(tourId); // FIXED (important)
        });

        let filters = $('#filterForm').serializeArray();

        let data = {
            _token: '{{ csrf_token() }}',
            order: sortedData
        };

        // attach filters dynamically
        filters.forEach(f => {
            data[f.name] = f.value;
        });

        $.ajax({
            url: "{{ route('admin.tour.reorder') }}",
            type: "POST",
            data: data,
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Sort order updated!'
                });
            }
        });
    });
</script>

<script> 
    $(document).on("click", "#updatePrice", function (e) {
        e.preventDefault(); 
        
        let selected = [];
        let selectedNames = [];

        $('input[name="ids[]"]:checked').each(function () {
            selected.push($(this).val());
            selectedNames.push({
                id: $(this).val(),
                name: $(this).closest('tr').find('td:nth-child(4) a.tour-heading').text().trim(),
                price: $(this).closest('tr').find('td:nth-child(5)').text().trim(),
                sku: $(this).closest('tr').find('td:nth-child(6)').text().trim()
            });
        });

        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please select at least one tour.'
            });
            return;
        }

        // Show selected tours in modal (with remove option)
        let listHtml = `<div style="max-height:450px; overflow-y:auto;"><ul class="list-group">`;
        selectedNames.forEach(item => {
            listHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${item.id}">
                    <span class"mr-3" style="margin-right : 10px">${item.sku} - ${item.name} - ${item.price}</span>
                    <button type="button" class="btn btn-sm btn-danger remove-tour" data-id="${item.id}">&times;</button>
                </li>`;
        });
        listHtml += `</ul></div>`;


        $("#updatePriceModal").find("#selected_tour_list").html(listHtml);

        // Save IDs in hidden input
        $("#updatePriceModal").find("#selected_tours").val(selected.join(","));

        // Show modal
        $("#updatePriceModal").modal("show");
    });

    $(document).on("click", "#saveTourCoupon", function (e) {
        e.preventDefault(); 
        
        let selected = [];
        let selectedNames = [];

        $('input[name="ids[]"]:checked').each(function () {
            selected.push($(this).val());
            selectedNames.push({
                id: $(this).val(),
                name: $(this).closest('tr').find('td:nth-child(4) a.tour-heading').text().trim()
            });
        });

        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please select at least one tour.'
            });
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
            selectedNames.push($(this).closest('tr').find('td:nth-child(4) a.tour-heading').text().trim());
        });

        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please select at least one tour.'
            });
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

<script>
function toggleTourMenu(id){

    let rows = document.querySelectorAll(".tour-expand-row");

    rows.forEach(function(row){

        if(row.id !== "tour-menu-"+id){
            row.style.display = "none";
        }

    });

    let current = document.getElementById("tour-menu-"+id);

    let isVisible = window.getComputedStyle(current).display === "table-row";

    if(isVisible){
        current.style.display = "none";
    }else{
        current.style.display = "table-row";
    }

}
</script>

<script>
function openTourMenu(id, title){

    let content = document.getElementById("tour-menu-" + id).innerHTML;

    document.getElementById("tourMenuContent").innerHTML = content;

    // ✅ set title
    document.getElementById("tourMenuTitle").innerText = "Tour Menu :    " + title;

    document.getElementById("tourMenuModal").style.display = "flex";
}

function closeTourMenu(){
    document.getElementById("tourMenuModal").style.display = "none";
}

</script>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '{{ session("success") }}'
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '{{ session("error") }}'
});
</script>
@endif

@endsection
</x-admin>