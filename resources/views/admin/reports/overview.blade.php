<x-admin>
@section('title', 'Reports Overview')

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
                <div class="col-xl-3 col-md-3 col-12 position-relative">
                    <label class="filter-label">Booking Date</label>

                    <input type="text" id="booking_range" class="form-control"
                        placeholder="Select date range" autocomplete="off">

                    @if(request('start_date'))
                        <span class="clear-btn" onclick="clearBooking()">✕</span>
                    @endif

                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>

                {{-- TOUR DATE --}}
                <div class="col-xl-3 col-md-3 col-12 position-relative">
                    <label class="filter-label">Fulfilment Date</label>

                    <input type="text" id="tour_range" class="form-control"
                        placeholder="Select date range" autocomplete="off">

                    @if(request('tour_start_date'))
                        <span class="clear-btn" onclick="clearTour()">✕</span>
                    @endif

                    <input type="hidden" name="tour_start_date" id="tour_start_date" value="{{ request('tour_start_date') }}">
                    <input type="hidden" name="tour_end_date" id="tour_end_date" value="{{ request('tour_end_date') }}">
                </div>

                {{-- ORDER STATUS --}}
                <div class="col-xl-2 col-md-3 col-12">
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

                {{-- PAY TYPE --}}
                <div class="col-xl-2 col-md-3 col-12">
                    <label class="filter-label">Pay Type</label>
                    <select name="action_type" class="form-control">
                        <option value="">All</option>
                        <option value="pay_now" {{ request('action_type')=='pay_now'?'selected':'' }}>Pay Now</option>
                        <option value="pay_later" {{ request('action_type')=='pay_later'?'selected':'' }}>Pay Later</option>
                    </select>
                </div>
                <div class="col-md-2">
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

                {{-- BUTTONS --}}
                <div class="col-xl-2 col-md-3 col-12">
                    <div class="d-flex column-gap-10">
                        <button class="btn btn-apply flex-fill">Apply</button>
                        <a href="{{ route('admin.report.overview') }}" class="btn btn-secondary flex-fill">Reset</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- STATS --}}
    <div class="report-stats">
        <div class="row">

            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
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

            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-dollar-sign"></i>
                    </div>
                    <div class="sale-num">
                        <h3>{{ number_format($performance['gross_sales'], 2) }}</h3>
                        <div class="stat-title">Gross Sales</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-undo"></i>
                    </div>
                    <div class="sale-num">
                        <h3 class="text-red">{{ number_format($performance['refund'], 2) }}</h3>
                        <div class="stat-title">Refund</div>
                    </div>
                </div>
            </div>



        </div>
        <div class="row">

            

            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="info-stats4">
                    <div class="info-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="sale-num">
                        <h3>{{ $performance['payment_received'] }}</h3>
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
                        <h3>{{ number_format($performance['pending_amount'], 2) }}</h3>
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
                        <h3 class="text-green">{{ number_format($performance['net_sales'], 2) }}</h3>
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

<script>
    const today = moment();

    /*
    |--------------------------------------------------------------------------
    | BOOKING RANGE (DEFAULT = TODAY)
    |--------------------------------------------------------------------------
    */
    let bookingStart = "{{ request('start_date') }}" ? moment("{{ request('start_date') }}") : today;
    let bookingEnd   = "{{ request('end_date') }}" ? moment("{{ request('end_date') }}") : today;

    $('#booking_range').daterangepicker({
        startDate: bookingStart,
        endDate: bookingEnd,
        autoUpdateInput: true,
        opens: 'left',
        locale: {
            format: 'DD MMM YYYY',
            cancelLabel: 'Clear'
        }
    });

    // Set hidden fields initially
    $('#start_date').val(bookingStart.format('YYYY-MM-DD'));
    $('#end_date').val(bookingEnd.format('YYYY-MM-DD'));

    // Update on apply
    $('#booking_range').on('apply.daterangepicker', function(ev, picker) {
        $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
    });

    /*
    |--------------------------------------------------------------------------
    | TOUR RANGE
    |--------------------------------------------------------------------------
    */
    let tourStart = "{{ request('tour_start_date') }}" ? moment("{{ request('tour_start_date') }}") : null;
    let tourEnd   = "{{ request('tour_end_date') }}" ? moment("{{ request('tour_end_date') }}") : null;

    $('#tour_range').daterangepicker({
        autoUpdateInput: false,
        opens: 'left',
        locale: { format: 'DD MMM YYYY' }
    });

    if (tourStart && tourEnd) {
        $('#tour_range').data('daterangepicker').setStartDate(tourStart);
        $('#tour_range').data('daterangepicker').setEndDate(tourEnd);
        $('#tour_range').val(tourStart.format('DD MMM YYYY') + ' - ' + tourEnd.format('DD MMM YYYY'));
    }

    $('#tour_range').on('apply.daterangepicker', function(ev, picker) {
        $('#tour_start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#tour_end_date').val(picker.endDate.format('YYYY-MM-DD'));
        $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
    });
</script>

@endsection

</x-admin>