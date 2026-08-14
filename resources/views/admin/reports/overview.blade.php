<x-admin>
@section('title', 'Reports Overview')

<style>
    body.sidebar-open {
        overflow: hidden;
    }
    /* FULL FIX FOR SELECT2 HEIGHT */
   .tour-search-filter .select2-container--default .select2-selection--multiple {
        min-height: calc(1.3125rem + 1.2rem + 2px) !important;
        padding: 0.4rem 1rem !important;
        margin-bottom: 15px !important;
        font-size: 14px;
        color: #898b92;
        border: 1px solid #aeb0b4;
    }

    .tour-search-filter .select2-container--default.select2-container--focus .select2-selection--multiple {
        border: 1px solid #aeb0b4;
        outline: 0;
    }

    .tour-search-filter .select2-container--default .select2-search--inline .select2-search__field {
        background: transparent;
        border: none !important;
        outline: 0;
        box-shadow: none;
        -webkit-appearance: textfield;
        margin: 0 !important;
    }

    .tour-search-filter .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #FFF;
        margin: 0;
        line-height: 1.7;
    }

    .search-options .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-right: 0;
        margin-left: 0;
        margin-bottom: 5px;
        margin-top: 0;
        background-color: #a3a3a3;
    }

    .search-options .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
        font-size: 13px;
    }

    .search-options .select2-container--default .select2-search--inline .select2-search__field {
        font-size: 14px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin: 0 0 5px 1px;
        font-size: 13px;
    }

    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #5897fb;
        color: white;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #333;
        background: #607D8B;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice{
        background-color: #a3a3a3 !important;
    }

    @media(min-width:767px) {

        .daterangepicker.show-calendar {
            top: 80px !important;
            left: auto;
            right: 430px !important;
        }

        .daterangepicker.show-calendar:before,
        .daterangepicker.show-calendar:after {
            left: 623px !important;
            border-bottom-color: #999;
            rotate: 90deg;
            top: 130px;
        }

        .daterangepicker.show-calendar:nth-of-type(2):before,
        .daterangepicker.show-calendar:nth-of-type(2):after {
            top: 40px;
        }
    }

    @media(max-width:767px) {

        .daterangepicker.show-calendar {
            height: 200px;
            overflow-y: scroll;
        }

    }


</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="report-overview">

    <div class="card-primary mb-3">
        <div class="card-header tour-main-head">
            <div class="row">
                <div class="col-md-8 col-7">
                    <h3 class="card-title text-white">Reports Overview</h3>
                </div>
                <div class="col-md-4 col-5">
                    <div class="card-tools">
                        <button type="button" class="btn btn-secondary" id="toggleFilter">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="tour-search-filter">
        <div id="filterSidebar" class="filter-sidebar">
            <div class="filter-header">
                <h5><i class="fas fa-filter"></i> Filters</h5>
                <button type="button" id="closeFilter">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="GET">
                <div class="filter-body">
                    <div class="row">

                        {{-- BOOKING DATE --}}
                        @php
                            $hasFilter = request()->hasAny([
                                'booking_date',
                                'tour_date',
                                'product',
                                'order_status',
                                'payment_status',
                                'partner',
                                'action_type',
                                'exclude_product'
                            ]);
                        @endphp
                        <div class="col-12 position-relative">
                            <div class="form-group">
                                <label class="filter-label">Order Date</label>
                                <input 
                                    type="text" 
                                    name="booking_date"
                                    id="booking_range"
                                    class="form-control aiz-date-range"
                                    data-advanced-range="true"
                                    data-separator=" - "
                                    data-show-dropdown="true"
                                    placeholder="Select date range"
                                    autocomplete="off"
                                    value="{{ request('booking_date') }}"
                                >

                                @if(request('booking_date'))
                                    <span class="clear-btn" onclick="clearBooking()">✕</span>
                                @endif
                            </div>
                        </div>

                        {{-- TOUR DATE --}}
                        <div class="col-12 position-relative">
                            <div class="form-group">
                                <label class="filter-label">Tour Date</label>
                                <input 
                                    type="text" 
                                    name="tour_date"
                                    
                                    class="form-control aiz-date-range"
                                    data-advanced-range="true"
                                    data-separator=" - "
                                    data-show-dropdown="true"
                                    placeholder="Select date range"
                                    autocomplete="off"
                                    value="{{ request('tour_date') }}"
                                >
                                @if(request('tour_date'))
                                    <span class="clear-btn" onclick="clearTour()">✕</span>
                                @endif
                            </div>
                        </div>

                        <!-- <div class="col-xl-3 col-md-3 col-12 position-relative">
                            <label class="filter-label">Booking Date</label>

                                <input type="text" id="booking_range" class="form-control"
                                    placeholder="Select date range" autocomplete="off">

                            
                                <span class="clear-btn" onclick="clearBooking()">✕</span>
                            

                            <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                        </div> -->

                        {{-- TOUR DATE --}}
                        <!-- <div class="col-xl-3 col-md-3 col-12 position-relative">
                            <label class="filter-label">     Date</label>

                                <input type="text" id="tour_range" class="form-control"
                                    placeholder="Select date range" autocomplete="off">

                        
                                <span class="clear-btn" onclick="clearTour()">✕</span>
                        

                            <input type="hidden" name="tour_start_date" id="tour_start_date" value="{{ request('tour_start_date') }}">
                            <input type="hidden" name="tour_end_date" id="tour_end_date" value="{{ request('tour_end_date') }}">
                        </div> -->
                        <!-- <div class="col-xl-3 col-md-3 col-12 position-relative">
                            <label class="filter-label">Products</label>
                            <select id="productFilter" name="product" class="form-control"></select>
                        </div>  -->

                        {{-- PRODUCTS --}}
                        <div class="col-12">
                            <label class="filter-label">Products</label>
                            <select id="productFilter" name="product[]" class="form-control" multiple>
                                @foreach($selectedProducts as $sp)
                                    <option value="{{ $sp->id }}" selected>{{ $sp->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- EXCLUDED PRODUCTS --}}
                        <div class="col-12">
                            <label class="filter-label">Excluded Products</label>
                            <select id="excludeProductFilter" name="exclude_product[]" class="form-control" multiple>
                                @foreach($excludedProducts as $ep)
                                    <option value="{{ $ep->id }}" selected>{{ $ep->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ORDER STATUS --}}
                        <div class="col-12">
                            <div class="form-group">
                                <label class="filter-label">Order Status</label>
                                <select name="order_status" class="form-control">
                                    <option value="">All</option>
                                    @php
                                    $status_with_code = [
                                                
                                                3 => 'Pending supplier',
                                                4 => 'Pending customer',
                                                5 => 'Confirmed',
                                                
                                        ];
                                    @endphp
                                    @foreach($status_with_code as $key => $val)
                                        <option value="{{ $key }}"
                                            {{ request('order_status') == $key ? 'selected' : '' }}>
                                            {{ $val }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- PAY TYPE --}}
                        <div class="col-12">
                            <div class="form-group">
                                <label class="filter-label">Pay Type</label>
                                <select name="action_type" class="form-control">
                                    <option value="">All</option>
                                    <option value="pay_now" {{ request('action_type')=='pay_now'?'selected':'' }}>Pay Now</option>
                                    <option value="pay_later" {{ request('action_type')=='pay_later'?'selected':'' }}>Pay Later</option>
                                </select>
                            </div>
                        </div>

                        {{-- SOURCE --}}
                        <div class="col-12">
                            <div class="form-group">
                                <label class="filter-label">Source</label>
                                <select name="partner" class="form-control">
                                    <option value="">All</option>
                                    
                                    
                                    @foreach($partners as $partner)
                                        <option value="{{ ucfirst($partner->slug) }}"
                                            {{ request('partner') == ucfirst($partner->slug) ? 'selected' : '' }}>
                                            {{ $partner->name }}
                                        </option>
                                    @endforeach
                                    <option value="Tourbeez" {{ request('partner') == 'Tourbeez' ? 'selected' : '' }}>Tourbeez</option>
                                    <option value="Internal" {{ request('partner') == 'Internal' ? 'selected' : '' }}>Internal</option>
                                </select>
                            </div>
                        </div>

                        {{-- BUTTONS --}}
                        <!-- <div class="col-12">
                            <div class="d-flex column-gap-10">
                                <button class="btn btn-apply flex-fill">Apply</button>
                                <a href="{{ route('admin.report.overview') }}" class="btn btn-secondary flex-fill">Reset</a>
                            </div>
                        </div> -->

                        <div class="col-12">
                            @if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type']))
                                <div class="alert alert-info">
                                    Please apply filters to view report data.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
                <div class="filter-footer">
                    <button id="applyBtn" class="btn btn-search">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.report.overview') }}" class="btn btn-clear">
                        <i class="fas fa-times"></i> Clear Search
                    </a>
                </div>
            </form>
        </div>
        <div id="filterOverlay"></div>
    </div>

    <div class="active-filters mb-3">
        @if(request()->hasAny([
            'booking_date','tour_date','product',
            'order_status','action_type','partner', 'exclude_product'
        ]))

            <div class="d-flex flex-wrap gap-2">

                {{-- BOOKING DATE --}}
                @if(request('booking_date'))
                    <span class="badge bg-dark">
                        Booking: {{ request('booking_date') }}
                        <a href="{{ request()->fullUrlWithQuery(['booking_date' => null]) }}"> <span class="ml-2">✕</span> </a>
                    </span>
                @endif

                {{-- TOUR DATE --}}
                @if(request('tour_date'))
                    <span class="badge bg-dark ml-2">
                        Tour: {{ request('tour_date') }}  
                        <a href="{{ request()->fullUrlWithQuery(['tour_date' => null]) }}"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PRODUCT --}}
                

                {{-- STATUS --}}
                @if(request('order_status'))
                    <span class="badge bg-dark ml-2">
                        Status: {{ $status_with_code[request('order_status')] ?? request('order_status') }}
                        <a href="{{ request()->fullUrlWithQuery(['order_status' => null]) }}"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PAY TYPE --}}
                @if(request('action_type'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('action_type') }}
                        <a href="{{ request()->fullUrlWithQuery(['action_type' => null]) }}"><span class="ml-2">✕</span></a>
                    </span>
                @endif
                 @if(request('partner'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('partner') }}
                        <a href="{{ request()->fullUrlWithQuery(['partner' => null]) }}"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                 {{-- Selected Tour --}}
                @if($selectedProducts->isNotEmpty())
                    @php $p = $selectedProducts->first(); @endphp
                    <span class="badge badge-dark ml-2">
                        Tour: {{ $p->title }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['product' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Excluded Tours --}}
                @foreach($excludedProducts as $ep)
                    <span class="badge badge-dark ml-2">
                        Excluded: {{ $ep->title }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery([
                            'exclude_product' => collect(request('exclude_product'))
                                ->reject(fn($id) => $id == $ep->id)
                                ->values()
                                ->all(),
                        ]) }}">✕</a>
                    </span>
                @endforeach

            </div>
        @endif
    </div>

    {{-- STATS --}}
    <div class="report-stats">
        <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-calendar-check"></i>
                    </div>
                    <div class="sale-num">
                        <h3>{{ $performance['total_orders'] }}</h3>
                        <div class="stat-title">Total Bookings</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-dollar-sign"></i>
                    </div>
                    <div class="sale-num">
                        <h3>$ {{ number_format($performance['gross_sales'], 2) }}</h3>
                        <div class="stat-title">Gross Sales</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-undo"></i>
                    </div>
                    <div class="sale-num">
                        <h3 class="text-red">$ {{ number_format($performance['refund'], 2) }}</h3>
                        <div class="stat-title">Refund</div>
                    </div>
                </div>
            </div>            

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="sale-num">
                        <h3>$ {{ number_format($performance['payment_received'], 2) }}</h3>
                        <div class="stat-title">Payment Recieved</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-wallet"></i>
                    </div>
                    <div class="sale-num">
                        <h3>$ {{ number_format($performance['pending_amount'], 2) }}</h3>
                        <div class="stat-title">Pending Balance</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-chart-line"></i>
                    </div>
                    <div class="sale-num">
                        <h3 class="text-green">$ {{ number_format($performance['net_sales'], 2) }}</h3>
                        <div class="stat-title">Net Sales</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="sale-num">
                        <h3 > {{ $performance['adult'] }}</h3>
                        <div class="stat-title">Adults</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="sale-num">
                        <h3 > {{ $performance['child'] }}</h3>
                        <div class="stat-title">Childs</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="sale-num">
                        <h3 > {{ $performance['infant'] }}</h3>
                        <div class="stat-title">Infants</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="sale-num">
                        <h3 > {{ $performance['other'] }}</h3>
                        <div class="stat-title">Senior Citigen</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js') 
@parent()

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $('#toggleFilter').click(function () {

        $('#filterSidebar').addClass('show');

        $('#filterOverlay').addClass('show');

        $('body').addClass('sidebar-open');

        $(this)
            .removeClass('btn-secondary')
            .addClass('btn-danger')
            .html('<i class="fas fa-times"></i> Filters');
    });

    $('#closeFilter,#filterOverlay').click(function () {

        $('#filterSidebar').removeClass('show');

        $('#filterOverlay').removeClass('show');

        $('body').removeClass('sidebar-open');

        $('#toggleFilter')
            .removeClass('btn-danger')
            .addClass('btn-secondary')
            .html('<i class="fas fa-filter"></i> Filters');
    });
</script>

<script>
    const today = moment();


        // $('#productFilter').select2({
        //     placeholder: 'Select Tour',
        //     minimumInputLength: 3,
        //     ajax: {
        //         url: '{{ route("admin.tours.tours-list") }}',
        //         dataType: 'json',
        //         delay: 0,
        //         cache: true,
        //         data: function (params) {
        //             return { q: params.term };
        //         },
        //         processResults: function (data) {
        //             return {
        //                 results: data.map(tour => ({
        //                     id: tour.id,
        //                     text: `${tour.title} (${tour.unique_code ?? 'N/A'})`
        //                 }))
        //             };
        //         }
        //     }
        // });
//         $('#productFilter').on('select2:select', function (e) {
//     let data = e.params.data;

//     $('#product_text').val(data.text);
// });
           function initTourSelect(selector, isMultiple, placeholderText) {
            $(selector).select2({
                placeholder: placeholderText,
                dropdownParent: $('#filterSidebar'),
                minimumInputLength: 4,
                multiple: isMultiple,
                ajax: {
                    url: '{{ route("admin.tours.tours-list") }}',
                    dataType: 'json',
                    delay: 0,
                    cache: true,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(tour => ({ id: tour.id, text: tour.title }))
                    })
                }
            });
        }

        initTourSelect('#productFilter', true, 'Select Tour');
        initTourSelect('#excludeProductFilter', true, 'Exclude tours');

        function clearBooking() {
    $('#booking_range').val('');

    // remove from URL (important UX)
    let url = new URL(window.location.href);
    url.searchParams.delete('booking_date');
    window.location.href = url.toString();
}

function clearTour() {
    $('input[name="tour_date"]').val('');

    let url = new URL(window.location.href);
    url.searchParams.delete('tour_date');
    window.location.href = url.toString();
}

//         if ($('#productFilter').val() && !$('#product_text').val()) {
//     let selectedText = $('#productFilter option:selected').text();
//     $('#product_text').val(selectedText);
// }
</script>

@endsection

</x-admin>