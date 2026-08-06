
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

.tour-filter-panel{background:linear-gradient(180deg,#f8fafc 0%,#fff 100%);border-bottom:1px solid #e5e7eb;padding:20px;}
.tour-filter-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;}
.tour-filter-heading h5{margin:0 0 3px;color:#172033;font-size:17px;font-weight:700;}
.tour-filter-heading p{margin:0;color:#6b7280;font-size:13px;}
.tour-filter-count{display:inline-flex;align-items:center;white-space:nowrap;padding:5px 10px;border-radius:999px;background:#e0e7ff;color:#3730a3;font-size:12px;font-weight:600;}
.tour-filter-heading-actions{display:flex;align-items:center;gap:9px;}
.tour-filter-toggle{height:36px;display:inline-flex;align-items:center;gap:7px;padding:0 12px;border:1px solid #c7d2fe;border-radius:7px;background:#fff;color:#4338ca;font-size:13px;font-weight:600;cursor:pointer;}
.tour-filter-toggle:hover{background:#eef2ff;}
.tour-filter-toggle .fa-chevron-down{font-size:10px;transition:transform .2s ease;}
.tour-filter-panel.is-expanded .tour-filter-toggle .fa-chevron-down{transform:rotate(180deg);}
.tour-filter-grid,.tour-filter-panel .search-options>.row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:0;}
.tour-filter-panel .search-options>.row>[class*=col-]{width:100%;max-width:none;padding:0;}
.tour-filter-field--search{grid-column:span 2;}
.tour-filter-panel:not(.is-expanded) .tour-filter-field--advanced{display:none;}
.tour-filter-panel:not(.is-expanded) .tour-filter-field--search{grid-column:span 4;}
.tour-filter-panel:not(.is-expanded) .tour-filter-body{display:block;}
.tour-filter-panel:not(.is-expanded) .search-options{flex:1;min-width:0;}
.tour-filter-panel:not(.is-expanded) .tour-filter-actions{display:none;}
.tour-filter-field label{display:block;margin:0 0 6px;color:#374151;font-size:12px;font-weight:600;}
.tour-filter-field .form-control,.tour-filter-field .select2-container .select2-selection--single,.tour-filter-field .bootstrap-select>.dropdown-toggle{min-height:40px;border-color:#d7dce3;border-radius:7px;background-color:#fff;font-size:13px;}
.tour-filter-field .form-control:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);}
.tour-filter-field .select2-container,.tour-filter-field .bootstrap-select{width:100%!important;}
.tour-filter-field .select2-container .select2-selection--single{height:40px;}
.tour-filter-field .select2-selection__rendered{line-height:38px!important;padding-left:12px!important;}
.tour-filter-field .select2-selection__arrow{height:38px!important;}
.tour-search-wrap{position:relative;}
.tour-search-wrap i{position:absolute;top:50%;left:13px;z-index:1;color:#9ca3af;transform:translateY(-50%);}
.tour-search-wrap .form-control{padding-left:37px;}
.tour-search-inline{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:stretch;gap:10px;}
.tour-search-inline-submit{display:none;min-width:120px;height:40px;align-items:center;justify-content:center;gap:7px;border:1px solid #4f46e5;border-radius:7px;background:#4f46e5;color:#fff;font-size:13px;font-weight:600;}
.tour-search-inline-submit:hover{border-color:#4338ca;background:#4338ca;color:#fff;}
.tour-filter-panel:not(.is-expanded) .tour-search-inline-submit{display:inline-flex;}
.tour-filter-panel.is-expanded .tour-search-inline{display:block;}
.tour-filter-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:16px;border-top:1px solid #e5e7eb;}
.tour-filter-actions .btn{min-width:120px;height:40px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:7px;font-size:13px;font-weight:600;}
.tour-filter-actions .btn-apply-filter{border-color:#4f46e5;background:#4f46e5;color:#fff;}
.tour-filter-actions .btn-apply-filter:hover{border-color:#4338ca;background:#4338ca;color:#fff;}

/* Tour management toolbar */
.tour-action-toolbar{padding:16px 20px!important;border-bottom:1px solid #e5e7eb!important;background:#fff;}
.tour-toolbar-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;}
.tour-toolbar-heading h6{margin:0;color:#1f2937;font-size:14px;font-weight:700;}
.tour-toolbar-heading span{color:#6b7280;font-size:12px;}
.tour-main-body .tour-action-toolbar .btn-options{display:flex;flex-wrap:wrap;gap:8px;width:100%;}
.tour-main-body .tour-action-toolbar .btn-options .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:38px;margin:0!important;padding:8px 12px;border:1px solid transparent;border-radius:7px;box-shadow:none;font-size:12px;font-weight:600;line-height:1.2;transition:background-color .15s ease,border-color .15s ease,transform .15s ease;}
.tour-main-body .tour-action-toolbar .btn-options .btn:hover{transform:translateY(-1px);box-shadow:0 3px 8px rgba(15,23,42,.12);}
.tour-main-body .tour-action-toolbar .btn-options .btn i{margin:0;}
.tour-main-body .tour-action-toolbar .btn-create{background:#16a34a;border-color:#16a34a;}
.tour-main-body .tour-action-toolbar .btn-UpdatePrice{background:#fff7ed;border-color:#fdba74;color:#c2410c!important;}
.tour-main-body .tour-action-toolbar .btn-MarkReview{background:#f0fdf4;border-color:#86efac;color:#15803d!important;}
.tour-main-body .tour-action-toolbar .btn-enable{background:#ecfeff;border-color:#a5f3fc;color:#0e7490!important;}
.tour-main-body .tour-action-toolbar .btn-discount{background:#fefce8;border-color:#fde047;color:#a16207!important;}
.tour-main-body .tour-action-toolbar .btn-save{background:#eef2ff;border-color:#c7d2fe;color:#4338ca!important;}
.tour-main-body .tour-action-toolbar .btn-ExpoImpo{background:#f8fafc;border-color:#cbd5e1;color:#475569!important;}
.tour-main-body .tour-action-toolbar .btn-delete{margin-left:auto!important;background:#fff1f2;border-color:#fda4af;color:#be123c!important;}
.tour-table .tour-list-thumbnail{display:block;width:48px;height:48px;border:1px solid #e5e7eb;border-radius:7px;object-fit:cover;background:#f8fafc;}
@media(max-width:991.98px){.tour-filter-grid,.tour-filter-panel .search-options>.row{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:575.98px){.tour-filter-panel{padding:16px}.tour-filter-heading{display:block}.tour-filter-heading-actions{margin-top:10px;justify-content:space-between}.tour-filter-grid,.tour-filter-panel .search-options>.row{grid-template-columns:1fr}.tour-filter-field--search,.tour-filter-panel:not(.is-expanded) .tour-filter-field--search{grid-column:span 1}.tour-filter-actions{align-items:stretch;flex-direction:column-reverse}.tour-filter-actions .btn{width:100%}.tour-search-inline-submit{min-width:100px}.tour-toolbar-heading{display:block}.tour-toolbar-heading span{display:block;margin-top:3px}.tour-main-body .tour-action-toolbar .btn-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.tour-main-body .tour-action-toolbar .btn-options .btn{width:100%!important}.tour-main-body .tour-action-toolbar .btn-options .btn-delete{margin-left:0!important}}

</style>
  
@section('title','Tours')
<div class="tour-main-body card rounded-lg-custom border">

    <!-- Search Form (GET) -->
    <form class="my-0" id="filterForm" method="GET" action="{{ route('admin.tour.index') }}">
        @php
            $advancedTourFilterKeys = ['city', 'category', 'author', 'status', 'special_deposit', 'schedule', 'trustpilot_review', 'schedule_expiry', 'last_updated', 'has_sub_tour'];
            $tourFilterKeys = ['search', ...$advancedTourFilterKeys];
            $activeTourFilters = collect($tourFilterKeys)->filter(fn ($key) => request()->filled($key))->count();
            $advancedTourFiltersActive = collect($advancedTourFilterKeys)->contains(fn ($key) => request()->filled($key));
        @endphp
        <div class="tour-filter-panel {{ $advancedTourFiltersActive ? 'is-expanded' : '' }}" id="tour-filter-panel">
            <div class="tour-filter-heading">
                <div>
                    <h5><i class="fas fa-sliders-h mr-2 text-primary"></i>Find tours</h5>
                    <p>Search by tour name or SKU, then narrow the results with filters.</p>
                </div>
                <div class="tour-filter-heading-actions">
                    @if($activeTourFilters)
                        <span class="tour-filter-count">{{ $activeTourFilters }} active {{ Str::plural('filter', $activeTourFilters) }}</span>
                    @endif
                    <button type="button" class="tour-filter-toggle" id="tour-filter-toggle" aria-controls="tour-advanced-filters" aria-expanded="{{ $advancedTourFiltersActive ? 'true' : 'false' }}">
                        <i class="fas fa-filter"></i> Filters <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="tour-filter-body">
            <div class="search-options">
                <div class="row">
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--search">
                        <label for="tour-search">Tour name or SKU</label>
                        <div class="tour-search-inline">
                            <div class="tour-search-wrap">
                                <i class="fas fa-search"></i>
                                <input id="tour-search" type="search" name="search" class="form-control" placeholder="e.g. Niagara Falls or SKU-1024" value="{{ request('search') }}" />
                            </div>
                            <button type="submit" class="tour-search-inline-submit"><i class="fas fa-search"></i> Apply filters</button>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced" id="tour-advanced-filters">
                        <label for="city-select">City</label>
                        <select name="city" id="city-select" class="form-control">
                            @if(request('city'))
                                <option value="{{ request('city') }}" selected>{{ ucwords(optional(\App\Models\City::find(request('city')))->name) }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="category-select">Category</label>
                        <select name="category" id="category-select" class="form-control">
                            @if(request('category'))
                                <option value="{{ request('category') }}" selected>{{ ucwords(optional(\App\Models\Category::find(request('category')))->name) }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="tour-author">Author</label>
                        <select id="tour-author" name="author" class="form-control aiz-selectpicker" data-live-search="true">
                            <option value="">All authors</option>
                                @foreach ($users as $author)
                                    <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                                        {{ ucwords($author->name) }}
                                    </option>
                                @endforeach
                            
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="tour-status">Status</label>
                        <select id="tour-status" name="status" class="form-control">
                            <option value="">All statuses</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                                    Active
                                </option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="special-deposit">Special deposit</label>
                        <select id="special-deposit" name="special_deposit" class="form-control">
                            <option value="">Any deposit status</option>
                            @foreach (['Active','Not_Active'] as $special_deposit)
                                <option value="{{ strtolower($special_deposit) }}" {{ request('special_deposit') == strtolower($special_deposit) ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', $special_deposit) }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="tour-schedule">Schedule</label>
                        <select id="tour-schedule" name="schedule" class="form-control">
                            <option value="">Any schedule status</option>
                            @foreach (['Active','Not_Active'] as $schedule)
                                <option value="{{ strtolower($schedule) }}" {{ request('schedule') == strtolower($schedule) ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', $schedule) }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="trustpilot-review">Trustpilot review</label>
                        <select id="trustpilot-review" name="trustpilot_review" class="form-control">
                            <option value="">Any review status</option>
                                <option value="0" {{ request('trustpilot_review') === '0' ? 'selected' : '' }}>
                                    No
                                </option>
                                <option value="1" {{ request('trustpilot_review') === '1' ? 'selected' : '' }}>
                                    Yes
                                </option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="schedule-expiry">Schedule expiry</label>
                        <select id="schedule-expiry" name="schedule_expiry" class="form-control">
                            <option value="">Any expiry date</option>
                            <option value="today" {{ request('schedule_expiry') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="last_7" {{ request('schedule_expiry') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_15" {{ request('schedule_expiry') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                            <option value="this_week" {{ request('schedule_expiry') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="upcoming_15" {{ request('schedule_expiry') == 'upcoming_15' ? 'selected' : '' }}>Upcoming 15 Days</option>
                            <option value="expired" {{ request('schedule_expiry') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="last-updated">Last updated</label>
                        <select id="last-updated" name="last_updated" class="form-control">
                            <option value="">Any update date</option>
                            <option value="today" {{ request('last_updated') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="last_7" {{ request('last_updated') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_15" {{ request('last_updated') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                            <option value="this_week" {{ request('last_updated') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="upcoming_15" {{ request('last_updated') == 'upcoming_15' ? 'selected' : '' }}>Upcoming 15 Days</option>
                            <option value="expired" {{ request('last_updated') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="has-sub-tour">Sub tours</label>
                        <select id="has-sub-tour" name="has_sub_tour" class="form-control">
                            <option value="">With or without sub tours</option>
                            @foreach (['Yes','No'] as $hasSubTour)
                                <option value="{{ strtolower($hasSubTour) }}" {{ request('has_sub_tour') == strtolower($hasSubTour) ? 'selected' : '' }}>
                                    {{ $hasSubTour === 'Yes' ? 'Has sub tours' : 'No sub tours' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 tour-filter-field tour-filter-field--advanced">
                        <label for="per-page">Results per page</label>
                        <select id="per-page" name="per_page" class="form-control">
                            @foreach (['All',10, 25, 50, 100] as $number)
                                <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                    {{ $number === 'All' ? 'Show all' : $number.' tours' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="tour-filter-actions">
                <a href="{{ route('admin.tour.index')}}" class="btn btn-outline-secondary"><i class="fas fa-undo-alt"></i> Reset filters</a>
                <button type="submit" class="btn btn-apply-filter"><i class="fas fa-search"></i> Apply filters</button>
            </div>
            </div>
        </div>
    </form>

    <script>
        document.getElementById('tour-filter-toggle').addEventListener('click', function () {
            const panel = document.getElementById('tour-filter-panel');
            const expanded = panel.classList.toggle('is-expanded');
            this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    </script>
    
    <div class="card-header tour-action-toolbar">
        <div class="w-100">
            <div class="tour-toolbar-heading">
                <h6><i class="fas fa-tools mr-2 text-primary"></i>Tour management</h6>
                <span>Select tours from the table before using bulk actions.</span>
            </div>
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
