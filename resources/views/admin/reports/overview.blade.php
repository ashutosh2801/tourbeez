<x-admin>
@section('title', 'Reports Overview')

<style>
/* FULL FIX FOR SELECT2 HEIGHT */
.select2-container .select2-selection--single {
    height: 42px !important;
    border: 1px solid #aeb0b4 !important;
    border-radius: 0.375rem !important;
    display: flex !important;
    align-items: center !important;
}

/* TEXT FIX */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
    padding-left: 10px !important;
    color: #495057 !important;
}

/* PLACEHOLDER COLOR */
.select2-container--default .select2-selection__placeholder {
    color: #6c757d !important;
}

/* ARROW ALIGNMENT */
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 10px !important;
}

/* FIX WHEN SELECTED (THIS IS YOUR BUG) */
.select2-container--default.select2-container--open .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--single {
    height: 38px !important;
}

/* FORCE CONSISTENT HEIGHT ALWAYS */
.select2-container {
    width: 100% !important;
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <div class="card-primary mb-3">
        <div class="card-header reports-head">
            <h3 class="card-title">Reports Overview</h3>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="card card-primary bg-white border rounded-lg-custom report-filter-box">
        <form method="GET">

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
                        'action_type'
                    ]);
                @endphp
                <div class="col-xl-4 col-md-3 col-12 position-relative">
                    <div class="form-group">
                        <label class="filter-label">Booking Date</label>
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
                <div class="col-xl-4 col-md-3 col-12 position-relative">
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
                <div class="col-xl-4 col-md-3 col-12 position-relative">
                    <div class="form-group">
                        <label class="filter-label">Products</label>
                        <select id="productFilter" name="product" class="form-control">
                            @if(request('product') && request('product_text'))
                                <option value="{{ request('product') }}" selected>
                                    {{ request('product_text') }}
                                </option>
                            @endif
                        </select>
                        <input type="hidden" id="product_text" name="product_text" value="{{ request('product_text') }}">
                    </div>
                </div> 

                {{-- ORDER STATUS --}}
                <div class="col-xl-3 col-md-3 col-12">
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
                <div class="col-xl-3 col-md-3 col-12">
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
                <div class="col-xl-3 col-md-3 col-12">
                    <div class="form-group">
                        <label class="filter-label">Source</label>
                        <select name="partner" class="form-control">
                            <option value="">All</option>
                            @php
                            
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
                <div class="col-xl-3 col-md-3 col-12">
                    <div class="d-flex column-gap-10">
                        <button class="btn btn-apply flex-fill">Apply</button>
                        <a href="{{ route('admin.report.overview') }}" class="btn btn-secondary flex-fill">Reset</a>
                    </div>
                </div>

            </div>
        </form>
    </div>
    <div class="active-filters mb-3">
        @if(request()->hasAny([
            'booking_date','tour_date','product',
            'order_status','action_type','partner'
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
                @if(request('product'))
                    <span class="badge bg-dark ml-2">
                        Product: {{ request('product_text') ?? request('product') }}
                        <a href="{{ request()->fullUrlWithQuery(['product' => null, 'product_text' => null]) }}"><span class="ml-2">✕</span></a>
                    </span>
                @endif

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

            </div>
        @endif
    </div>

    @if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type']))
        <div class="alert alert-info">
            Please apply filters to view report data.
        </div>
    @endif

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
                        <h3>$ {{ $performance['payment_received'] }}</h3>
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
    const today = moment();

    /*
    |--------------------------------------------------------------------------
    | BOOKING RANGE (DEFAULT = TODAY)
    |--------------------------------------------------------------------------
    */
    // let bookingStart = "{{ request('start_date') }}" ? moment("{{ request('start_date') }}") : today;
    // let bookingEnd   = "{{ request('end_date') }}" ? moment("{{ request('end_date') }}") : today;

    // $('#booking_range').daterangepicker({
    //     startDate: bookingStart,
    //     endDate: bookingEnd,
    //     autoUpdateInput: true,
    //     opens: 'left',
    //     locale: {
    //         format: 'DD MMM YYYY',
    //         cancelLabel: 'Clear'
    //     }
    // });

    // Set hidden fields initially
    // $('#start_date').val(bookingStart.format('YYYY-MM-DD'));
    // $('#end_date').val(bookingEnd.format('YYYY-MM-DD'));

    // Update on apply
    // $('#booking_range').on('apply.daterangepicker', function(ev, picker) {
    //     $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
    //     $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
    // });

    /*
    |--------------------------------------------------------------------------
    | TOUR RANGE
    |--------------------------------------------------------------------------
    */
    // let tourStart = "{{ request('tour_start_date') }}" ? moment("{{ request('tour_start_date') }}") : null;
    // let tourEnd   = "{{ request('tour_end_date') }}" ? moment("{{ request('tour_end_date') }}") : null;

    // $('#tour_range').daterangepicker({
    //     autoUpdateInput: false,
    //     opens: 'left',
    //     locale: { format: 'DD MMM YYYY' }
    // });

    // if (tourStart && tourEnd) {
    //     $('#tour_range').data('daterangepicker').setStartDate(tourStart);
    //     $('#tour_range').data('daterangepicker').setEndDate(tourEnd);
    //     $('#tour_range').val(tourStart.format('DD MMM YYYY') + ' - ' + tourEnd.format('DD MMM YYYY'));
    // }

    // $('#tour_range').on('apply.daterangepicker', function(ev, picker) {
    //     $('#tour_start_date').val(picker.startDate.format('YYYY-MM-DD'));
    //     $('#tour_end_date').val(picker.endDate.format('YYYY-MM-DD'));
    //     $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
    // });

    /*
        |--------------------------------------------------------------------------
        | CLEAR BOOKING RANGE
        |--------------------------------------------------------------------------
        */

        
        // function clearBooking() {
        //     $('#booking_range').val('');
        //     $('#start_date').val('');
        //     $('#end_date').val('');

        //     // reset picker UI as well
        //     let picker = $('#booking_range').data('daterangepicker');
        //     picker.setStartDate(moment());
        //     picker.setEndDate(moment());
            

            
        // }

        /*
        |--------------------------------------------------------------------------
        | CLEAR TOUR RANGE
        |--------------------------------------------------------------------------
        */
        // function clearTour() {
        //     $('#tour_range').val('');
        //     $('#tour_start_date').val('');
        //     $('#tour_end_date').val('');

        //     // reset picker UI
        //     let picker = $('#tour_range').data('daterangepicker');
        //     picker.setStartDate(moment());
        //     picker.setEndDate(moment());
            

        // }

        $('#productFilter').select2({
            placeholder: 'Select Tour',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route("admin.tours.tours-list") }}',
                dataType: 'json',
                delay: 0,
                cache: true,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(tour => ({
                            id: tour.id,
                            text: `${tour.title} (${tour.unique_code ?? 'N/A'})`
                        }))
                    };
                }
            }
        });
        $('#productFilter').on('select2:select', function (e) {
    let data = e.params.data;

    $('#product_text').val(data.text);
});

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

        if ($('#productFilter').val() && !$('#product_text').val()) {
    let selectedText = $('#productFilter option:selected').text();
    $('#product_text').val(selectedText);
}
</script>

@endsection

</x-admin>