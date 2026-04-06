<x-admin>
@section('title', 'Reports Overview')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    .filter-box {
        background: #fff;
        padding: 18px;
        border-radius: 10px;
        border: 1px solid #eaeaea;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        margin-bottom: 20px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 22px;
        border: 1px solid #eee;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-card h3 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }

    .stat-title {
        color: #888;
        font-size: 13px;
        margin-top: 5px;
    }

    .text-green { color: #28a745; }
    .text-red { color: #dc3545; }

    .filter-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .btn-primary {
        background: #3b82f6;
        border: none;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .position-relative .clear-btn {
        position: absolute;
        right: 10px;
        top: 38px;
        cursor: pointer;
        font-size: 14px;
        color: #999;
    }
</style>

<div class="container-fluid">

    {{-- FILTER --}}
    <div class="filter-box">
        <form method="GET">

            <div class="row">

                {{-- BOOKING DATE --}}
                <div class="col-md-3 position-relative">
                    <label class="filter-label">Booking Date</label>

                    <input type="text" id="booking_range" class="form-control"
                        placeholder="Select date range">

                    @if(request('start_date'))
                        <span class="clear-btn" onclick="clearBooking()">✕</span>
                    @endif

                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>

                {{-- TOUR DATE --}}
                <div class="col-md-3 position-relative">
                    <label class="filter-label">Fulfilment Date</label>

                    <input type="text" id="tour_range" class="form-control"
                        placeholder="Select date range">

                    @if(request('tour_start_date'))
                        <span class="clear-btn" onclick="clearTour()">✕</span>
                    @endif

                    <input type="hidden" name="tour_start_date" id="tour_start_date" value="{{ request('tour_start_date') }}">
                    <input type="hidden" name="tour_end_date" id="tour_end_date" value="{{ request('tour_end_date') }}">
                </div>

                {{-- ORDER STATUS --}}
                <div class="col-md-2">
                    <label class="filter-label">Order Status</label>
                    <select name="order_status" class="form-control">
                        <option value="">All</option>
                        @foreach(config('constants.status_with_code') as $key => $val)
                            <option value="{{ $key }}"
                                {{ request('order_status') == $key ? 'selected' : '' }}>
                                {{ $val }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PAY TYPE --}}
                <div class="col-md-2">
                    <label class="filter-label">Pay Type</label>
                    <select name="action_type" class="form-control">
                        <option value="">All</option>
                        <option value="pay_now" {{ request('action_type')=='pay_now'?'selected':'' }}>Pay Now</option>
                        <option value="pay_later" {{ request('action_type')=='pay_later'?'selected':'' }}>Pay Later</option>
                    </select>
                </div>

                {{-- BUTTONS --}}
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Apply</button>
                </div>

                <div class="col-md-2 d-flex align-items-end mt-2 mt-md-0">
                    <a href="{{ route('admin.report.overview') }}" class="btn btn-light w-100">Reset</a>
                </div>

            </div>
        </form>
    </div>

    {{-- STATS --}}
    <div class="row">

        <div class="col-md-3">
            <div class="stat-card">
                <h3>{{ $performance['total_orders'] }}</h3>
                <div class="stat-title">Total Bookings</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h3>{{ number_format($performance['gross_sales'], 2) }}</h3>
                <div class="stat-title">Gross Sales</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-red">{{ number_format($performance['refund'], 2) }}</h3>
                <div class="stat-title">Refund</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-green">{{ number_format($performance['net_sales'], 2) }}</h3>
                <div class="stat-title">Net Sales</div>
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