<x-admin>
@section('title', 'Revenue')
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
            <h3 class="card-title">Revenue</h3>
        </div>
    </div>

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
                        <a href="{{ route('admin.report.revenue') }}" class="btn btn-secondary flex-fill">Reset</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="card card-primary bg-white border rounded-lg-custom report-table">
        <div class="card-header report-table-head">
            <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title">Detailed Revenue Report</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <a href="{{ route('admin.report.revenue.export', request()->all()) }}" class="btn-sm btn-success">
                            Download Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="table table-bordered" style="min-width: 2200px; margin: 15px 20px;">

                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Order Status</th>
                        <th>Order Source</th>
                        <th>Agent/Supplier</th>
                        <th>Booking Date</th>
                        <th>Fulfilment Date</th>
                        <th>Customer</th>

                        <th>Order Amount</th>
                        <th>Payment Received</th>
                        <th>Balance</th>

                        <th>Booking Fees</th>
                        <th>Custom Fees</th>
                        <th>Surge</th>
                        <th>CC Surcharge</th>
                        <th>Platform Fees</th>
                        <th>Commission</th>
                        <th>Tax</th>
                        <th>Net Sales</th>

                        <th>Pax</th>
                        <th>Product Value</th>
                        <th>Adjustment</th>
                        <th>Extra Value</th>

                        <th>Promo/Voucher</th>

                        <th>Credit Card Payment</th>
                        <th>Cash Payment</th>
                        <th>Promo/Voucher Value</th>

                        <th>Free of Charge</th>
                        <th>Other Refund</th>

                        <th>Payment Status</th>
                        <th>All Paid</th>
                        <th>Payment Type</th>
                        <th>Gateway</th>
                        <th>Gateway Type</th>

                        <th>Internal Notes</th>
                        <th>How Heard</th>

                        <th>Product</th>
                        <th>Category</th>
                        <th>Agent Ref</th>
                    </tr>
                </thead>

                <tbody>
                        @forelse($orders as $order)
                        <tr>

                            <td><a href="{{ route('admin.orders.edit', encrypt($order->id)) }}" class="alink">{{ $order->order_number }}</a></td>
                            <td>{{ config('constants.status_with_code')[$order->order_status] ?? '-' }}</td>
                            <td>{{ $order->source ?? '-' }}</td>
                            <td>{{ $order->agent_name ?? 'NA' }}</td>

                            <td>{{ \Carbon\Carbon::parse($order->booking_date)->format('Y-m-d H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->fulfilment_date)->format('Y-m-d') }}</td>

                            <td>{{ trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? '')) ?: '-' }}</td>

                            {{-- ✅ MONEY (FROM FIXED BACKEND LOGIC) --}}
                            <td>{{ number_format($order->total_amount_converted, 2) }}</td>
                            <td>{{ number_format($order->paid_amount_converted, 2) }}</td>
                            <td>{{ $order->balance_converted > 0 ?number_format($order->balance_converted, 2) : 0 }}</td>

                            {{-- Fees (keep 0 if not calculated yet) --}}
                            <td>{{ number_format($order->booking_fee ?? 0, 2) }}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>

                            {{-- Commission & Tax (if added later) --}}
                            <td>{{ number_format($order->commission ?? 0, 2) }}</td>
                            <td>{{ number_format($order->tax_converted ?? 0, 2) }}</td>

                            {{-- Net Sales --}}
                            <td>{{ number_format($order->net_sales_converted ?? $order->total_amount_converted, 2) }}</td>

                            {{-- Pax --}}
                            <td>{{ $order->pax ?? 0 }}</td>

                            {{-- Product Value --}}
                            <td>{{ number_format($order->product_value_converted, 2) }}</td>

                            <td>{{ number_format($order->discount_value_converted, 2) }}</td> {{-- Adjustment --}}
                            <td>{{ number_format($order->extra_value_converted, 2) }}</td> {{-- Extra Value --}}

                            {{-- Promo --}}
                            <td>{{ number_format($order->promo_amount ?? 0, 2) }}</td>

                            {{-- Payment Split --}}
                            <td>{{ number_format($order->card_payment ?? 0, 2) }}</td>
                            <td>{{ number_format($order->cash_payment ?? 0, 2) }}</td>

                            <td>{{ number_format($order->promo_amount ?? 0, 2) }}</td>

                            <td>0</td> {{-- Free --}}
                            <td>{{ number_format($order->refunded ?? 0, 2) }}</td>

                            {{-- Status --}}
                            <td>{{ config('constants.payment_status')[$order->payment_status] ?? '-' }}</td>
                            <td>{{ $order->all_paid }}</td>

                            {{-- Payment Info --}}
                            <td>{{ $order->payment_method ?? '-' }}</td>
                            <td>{{ $order->gateway ?? 'NA' }}</td>
                            <td>{{ $order->gateway_type ?? 'NA' }}</td>

                            {{-- Notes --}}
                            <td>{{ $order->instructions ?? '-' }}</td>
                            <td>{{ $order->how_heard ?? 'N/A' }}</td>

                            {{-- Product --}}
                            <td>{{ $order->product_name ?? '-' }}</td>
                            <td>{{ $order->category ?? '-' }}</td>

                            <td>{{ $order->created_by ?? '-' }}</td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="40" class="text-center">No Data Found</td>
                        </tr>
                        @endforelse
                        </tbody>

            </table>
            <div class="text-center">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    <div class="card card-primary bg-white border rounded-lg-custom mt-4 report-table">
        <div class="card-header report-table-head">
            <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title">Customer Report</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <a href="{{ route('admin.report.customer.export', request()->query()) }}" class="btn-sm btn-success">Export Customers</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="table table-bordered" style="min-width: 2000px; margin: 15px 20px;">

                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Booking Date</th>
                        <th>Fulfilment Date</th>
                        <!-- <th>How Heard</th> -->

                        <th>First Name</th>
                        <!-- <th>Middle Name</th> -->
                        <th>Last Name</th>

                        

                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>DOB</th>
                        <!-- <th>Mobile</th>

                        <th>Fax</th>
                        <th>Skype</th>

                        <th>Address</th>
                        <th>City</th>
                        <th>Postcode</th>
                        <th>State</th>
                        <th>Country</th>

                        <th>Language</th>
                        <th>Company</th>

                        <th>Marketing Consent</th> -->
                        <th>Special Requirement</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $c)
                    <tr>
                        <td>{{ $c->order_number }}</td>

                        <td>{{ $c->booking_date }}</td>
                        <td>{{ $c->fulfilment_date }}</td>

                        <!-- <td>-</td> -->

                        <td><a href="{{ route('admin.customers.show', encrypt($c->id) ) }}" class="alink" target="_blank"> {{ $c->first_name ?? '-' }}</a></td>

                        <!-- <td>{{ $c->first_name ?? '-' }}</td> -->
                        <!-- <td>-</td> -->
                        <td>{{ $c->last_name ?? '-' }}</td>

                        

                        <td>{{ $c->email ?? '-' }}</td>
                        <td>{{ $c->phone ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                       <!--  <td>-</td>

                        <td>-</td>
                        <td>-</td>

                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>

                        <td>-</td>
                        <td>-</td>

                        <td>-</td> -->
                        <td>{{ $c->instructions ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="25" class="text-center">No Data Found</td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
            <div class="text-center">
                {{ $customers->links() }}
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