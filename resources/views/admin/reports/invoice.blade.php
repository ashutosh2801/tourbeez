<x-admin>
@section('title', 'Invoice Report')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    /* TABLE FIX */
    .table {
        font-size: 12px; /* smaller text */
    }

    .table th,
    .table td {
        white-space: nowrap;     /* prevent line break */
        font-size: 12px;         /* consistent small text */
        padding: 6px 10px;       /* tighter spacing */
        vertical-align: middle;
    }

    /* Optional: header slightly bold but compact */
    .table th {
        font-weight: 600;
        font-size: 12px;
    }

    /* Smooth horizontal scroll */
    .table-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    /* Optional: make numbers align nicely */
    .table td {
        text-align: left;
    }
    .pagination {
        justify-content: center;
    }
</style>

<div class="card-primary mb-3">
    <div class="card-header reports-head">
        <h3 class="card-title">Invoice Report</h3>
    </div>
</div>


{{-- ================= FILTER ================= --}}
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
                <div class="col-xl-2 col-md-2 col-12">
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
                <div class="col-xl-2 col-md-2 col-12">
                    <div class="d-flex column-gap-10">
                        <button class="btn btn-apply flex-fill">Apply</button>
                        <a href="{{ route('admin.report.invoice') }}" class="btn btn-secondary flex-fill">Reset</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

{{-- ================= TABLE ================= --}}
<div class="card card-primary bg-white border rounded-lg-custom report-table">

    <div class="card-header report-table-head">
            <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title">Invoice Report</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <a href="{{ route('admin.report.invoice.export', request()->all()) }}"
                           class="btn btn-success btn-sm">
                            Download Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <div class="table-wrapper">
        <table class="table table-bordered" style="min-width: 1400px;">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Order Number</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Fulfilment Date</th>
                    <th>Product Price</th>
                    <th>Extra Amount</th>
                    <th>Tax</th>
                    <th>Booking Fee</th>
                    <th>Total</th>
                    <th>Total Paid</th>
                    <th>Product Name</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['no'] }}</td>
                        <td><a href="{{ route('admin.orders.edit', encrypt($row['id'])) }}" class="alink">{{ $row['order_number'] }} </a></td>
                        <td>{{ $row['customer_name'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($row['order_date'])->format('Y-m-d') }}</td>
                        <td>{{ \Carbon\Carbon::parse($row['fulfilment_date'])->format('Y-m-d') }}</td>

                        <td>{{ number_format($row['product_price'], 2) }}</td>
                        <td>{{ number_format($row['extra_amount'], 2) }}</td>
                        <td>{{ number_format($row['tax_amount'], 2) }}</td>
                        <td>{{ number_format($row['booking_fee'], 2) }}</td>
                        <td>{{ number_format($row['customer_total'], 2) }}</td>

                        <td>{{ $row['total_paid'] }}</td>
                        <td>{{ $row['product_name'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">No Data Found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="text-center mt-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>




@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    const today = moment();

    let bookingStart = "{{ request('start_date') }}" ? moment("{{ request('start_date') }}") : today;
    let bookingEnd   = "{{ request('end_date') }}" ? moment("{{ request('end_date') }}") : today;

    $('#booking_range').daterangepicker({
        startDate: bookingStart,
        endDate: bookingEnd,
        locale: { format: 'DD MMM YYYY' }
    });

    $('#start_date').val(bookingStart.format('YYYY-MM-DD'));
    $('#end_date').val(bookingEnd.format('YYYY-MM-DD'));

    $('#booking_range').on('apply.daterangepicker', function(ev, picker) {
        $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
    });

    let tourStart = "{{ request('tour_start_date') }}" ? moment("{{ request('tour_start_date') }}") : null;
    let tourEnd   = "{{ request('tour_end_date') }}" ? moment("{{ request('tour_end_date') }}") : null;

    $('#tour_range').daterangepicker({
        autoUpdateInput: false,
        locale: { format: 'DD MMM YYYY' }
    });

    $('#tour_range').on('apply.daterangepicker', function(ev, picker) {
        $('#tour_start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#tour_end_date').val(picker.endDate.format('YYYY-MM-DD'));
        $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
    });

    if (tourStart && tourEnd) {
        $('#tour_range').data('daterangepicker').setStartDate(tourStart);
        $('#tour_range').data('daterangepicker').setEndDate(tourEnd);
        $('#tour_range').val(tourStart.format('DD MMM YYYY') + ' - ' + tourEnd.format('DD MMM YYYY'));
    }

    function clearBooking() {
        $('#booking_range').val('');
        $('#start_date').val('');
        $('#end_date').val('');

        // reset picker UI as well
        let picker = $('#booking_range').data('daterangepicker');
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        

        
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAR TOUR RANGE
    |--------------------------------------------------------------------------
    */
    function clearTour() {
        $('#tour_range').val('');
        $('#tour_start_date').val('');
        $('#tour_end_date').val('');

        // reset picker UI
        let picker = $('#tour_range').data('daterangepicker');
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        

    }
</script>
@endsection

</x-admin>