<x-admin>
@section('title', 'Invoice With Details')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    .table {
        font-size: 12px;
    }

    .table th, .table td {
        white-space: nowrap;
        padding: 6px 10px;
        vertical-align: middle;
    }

    .table-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    .pagination {
        justify-content: center;
    }
</style>

<div class="card-primary mb-3">
    <div class="card-header reports-head">
        <h3 class="card-title">Invoice With Details</h3>
    </div>
</div>

{{-- FILTERS --}}
<div class="card card-primary bg-white border rounded-lg-custom report-filter-box">
    <form method="GET">

        <div class="row">

            {{-- BOOKING DATE --}}
            <div class="col-xl-2 col-md-2 col-12 position-relative">
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
            <div class="col-xl-2 col-md-2 col-12 position-relative">
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

{{-- TABLE --}}

    <div class="card card-primary bg-white border rounded-lg-custom report-table">

    <div class="card-header report-table-head">
            <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title">Invoice Report Details</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <a href="{{ route('admin.report.invoice.details.export', request()->all()) }}"
                           class="btn btn-success btn-sm">
                            Download Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <div class="table-wrapper">
        <table class="table table-bordered" style="min-width: 2500px; margin: 15px 20px;">

            <thead>
                <tr>
                    <th>No.</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Fulfilment</th>
                    <th>Total (CAD)</th>
                    <th>Paid</th>
                    <th>Product</th>

                    {{-- ADDON HEADERS --}}
                    <th colspan="5">Boat Cruise</th>
                    <th colspan="5">Helicopter</th>
                    <th colspan="5">Journey Behind Falls</th>
                    <th colspan="5">Sheraton Lunch</th>
                    <th colspan="5">Skylon Tower</th>
                    <th colspan="5">Airport Drop</th>
                    <th colspan="5">Wine</th>
                    <th colspan="5">Jet Tour</th>
                    <th colspan="5">Guide</th>
                    <th colspan="5">Zipline</th>
                </tr>

                <tr>
                    <th colspan="8"></th>

                    @for($i=0;$i<10;$i++)
                        <th>Desc</th>
                        <th>Price</th>
                        <th>Tax</th>
                        <th>Fee</th>
                        <th>Total</th>
                    @endfor
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td>{{ $row['no'] }}</td>
                    <td>{{ $row['order_number'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['order_date'] }}</td>
                    <td>{{ $row['fulfilment_date'] }}</td>
                    <td>{{ number_format($row['customer_total'], 2) }}</td>
                    <td>{{ $row['payment_status'] }}</td>
                    <td>{{ $row['product_name'] }}</td>

                    {{-- BOAT --}}
                    <td>{{ $row['boat_cruise_desc'] }}</td>
                    <td>{{ $row['boat_cruise_price'] }}</td>
                    <td>{{ $row['boat_cruise_tax'] }}</td>
                    <td>{{ $row['boat_cruise_fee'] }}</td>
                    <td>{{ $row['boat_cruise_total'] }}</td>

                    {{-- HELICOPTER --}}
                    <td>{{ $row['helicopter_desc'] }}</td>
                    <td>{{ $row['helicopter_price'] }}</td>
                    <td>{{ $row['helicopter_tax'] }}</td>
                    <td>{{ $row['helicopter_fee'] }}</td>
                    <td>{{ $row['helicopter_total'] }}</td>

                    {{-- JOURNEY --}}
                    <td>{{ $row['journey_falls_desc'] }}</td>
                    <td>{{ $row['journey_falls_price'] }}</td>
                    <td>{{ $row['journey_falls_tax'] }}</td>
                    <td>{{ $row['journey_falls_fee'] }}</td>
                    <td>{{ $row['journey_falls_total'] }}</td>

                    {{-- SHERATON --}}
                    <td>{{ $row['sheraton_desc'] }}</td>
                    <td>{{ $row['sheraton_price'] }}</td>
                    <td>{{ $row['sheraton_tax'] }}</td>
                    <td>{{ $row['sheraton_fee'] }}</td>
                    <td>{{ $row['sheraton_total'] }}</td>

                    {{-- SKYLON --}}
                    <td>{{ $row['skylon_desc'] }}</td>
                    <td>{{ $row['skylon_price'] }}</td>
                    <td>{{ $row['skylon_tax'] }}</td>
                    <td>{{ $row['skylon_fee'] }}</td>
                    <td>{{ $row['skylon_total'] }}</td>

                    {{-- AIRPORT --}}
                    <td>{{ $row['airport_desc'] }}</td>
                    <td>{{ $row['airport_price'] }}</td>
                    <td>{{ $row['airport_tax'] }}</td>
                    <td>{{ $row['airport_fee'] }}</td>
                    <td>{{ $row['airport_total'] }}</td>

                    {{-- WINE --}}
                    <td>{{ $row['wine_desc'] }}</td>
                    <td>{{ $row['wine_price'] }}</td>
                    <td>{{ $row['wine_tax'] }}</td>
                    <td>{{ $row['wine_fee'] }}</td>
                    <td>{{ $row['wine_total'] }}</td>

                    {{-- JET --}}
                    <td>{{ $row['jet_desc'] }}</td>
                    <td>{{ $row['jet_price'] }}</td>
                    <td>{{ $row['jet_tax'] }}</td>
                    <td>{{ $row['jet_fee'] }}</td>
                    <td>{{ $row['jet_total'] }}</td>

                    {{-- GUIDE --}}
                    <td>{{ $row['guide_desc'] }}</td>
                    <td>{{ $row['guide_price'] }}</td>
                    <td>{{ $row['guide_tax'] }}</td>
                    <td>{{ $row['guide_fee'] }}</td>
                    <td>{{ $row['guide_total'] }}</td>

                    {{-- ZIPLINE --}}
                    <td>{{ $row['zipline_desc'] }}</td>
                    <td>{{ $row['zipline_price'] }}</td>
                    <td>{{ $row['zipline_tax'] }}</td>
                    <td>{{ $row['zipline_fee'] }}</td>
                    <td>{{ $row['zipline_total'] }}</td>

                </tr>
                @empty
                <tr>
                    <td colspan="60" class="text-center">No Data Found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- PAGINATION --}}
        <div class="text-center">
            {{ $orders->links() }}
        </div>

    </div>
</div>




@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
let today = moment();

$('#booking_range').daterangepicker({
    startDate: today,
    endDate: today,
    locale: { format: 'DD MMM YYYY' }
}).on('apply.daterangepicker', function(ev, picker) {
    $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
    $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
});

$('#tour_range').daterangepicker({
    autoUpdateInput: false,
    locale: { format: 'DD MMM YYYY' }
}).on('apply.daterangepicker', function(ev, picker) {
    $('#tour_start_date').val(picker.startDate.format('YYYY-MM-DD'));
    $('#tour_end_date').val(picker.endDate.format('YYYY-MM-DD'));
});
</script>
@endsection

</x-admin>