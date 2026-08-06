<x-admin>
@section('title', 'Price Schedule')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>

            /* FULL FIX FOR SELECT2 HEIGHT */
   .search-options .select2-container--default .select2-selection--multiple {
        min-height: calc(1.3125rem + 1.2rem + 2px) !important;
        padding: 0.4rem 1rem !important;
        margin-bottom: 15px !important;
    }

.select2-container--default .select2-selection--multiple {
    min-height: calc(1.3125rem + 1.2rem + 2px) !important;
        padding: 0.6rem 1rem !important;
        margin-bottom: 15px !important;
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

.search-options .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #FFF;
    margin-left: 0;
}

.search-options .select2-container--default .select2-search--inline .select2-search__field {
    font-size: 14px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    margin: 0 0 5px 1px;
    font-size: 13px;
}

.select2-container--default .select2-search--inline .select2-search__field {
    background: transparent;
    border: none;
    outline: 0;
    box-shadow: none;
    -webkit-appearance: textfield;
    margin: 0;
}

.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
    background-color: #5897fb;
    color: white;
}

.selection .select2-selection .select2-selection--multiple {
    min-height: calc(1.3125rem + 1.2rem + 2px) !important;
    padding: 0.6rem 1rem !important;
    margin-bottom: 15px !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    margin: 0;
    line-height: 1.7;
    color: #FFF;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #333;
    background: #607D8B;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice{
    background-color: #a3a3a3 !important;
}
    /* ================= TABLE ================= */

.table {
    font-size: 14px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table th,
.table td {
    padding: 6px 10px;
    white-space: nowrap;
    vertical-align: middle;
    font-size: 14px;
}

.table tbody tr:hover {
    background: #f5f9ff;
}

.table-wrapper {
    width: 100%;
    overflow: auto;
    position: relative;
    cursor: grab;
    max-height: 75vh; /* Required for sticky header */
}

.table-wrapper.active {
    cursor: grabbing;
}

/* ================= STICKY HEADER ================= */

.table thead th {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #212529 !important;
    color: #fff;
    font-weight: 600;
}

/* ================= FROZEN FIRST 4 COLUMNS ================= */

/* # */
.table th:nth-child(1),
.table td:nth-child(1) {
    position: sticky;
    left: 0;
    width: 20px;
    min-width: 20px;
    max-width: 20px;
    background: #f1f5f9;
    z-index: 20;
}

/* Order */
.table th:nth-child(2),
.table td:nth-child(2) {
    position: sticky;
    left: 20px;
    width: 130px;
    min-width: 130px;
    max-width: 130px;
    background: #f1f5f9;
    z-index: 20;
}

/* Customer */
.table th:nth-child(3),
.table td:nth-child(3) {
    position: sticky;
    left: 150px;
    width: 150px;
    min-width: 150px;
    max-width: 150px;
    background: #f1f5f9;
    z-index: 20;
}

/* Product */
.table th:nth-child(4),
.table td:nth-child(4) {
    position: sticky;
    left: 300px;
    width: 180px;
    min-width: 180px;
    max-width: 180px;
    background: #f1f5f9;
    z-index: 20;
}

/* Header over frozen columns */
.table thead th:nth-child(-n+4) {
    z-index: 110;
}

/* Shadow */
.table th:nth-child(-n+4),
.table td:nth-child(-n+4) {
    box-shadow: 1px 0 0 #dee2e6;
}

/* ================= PRODUCT NAME ================= */

.product-name {
    white-space: normal !important;
    word-break: break-word;
    overflow-wrap: anywhere;
    max-width: 220px;
    line-height: 1.4;
}

/* ================= FOOTER ================= */

.table tfoot td {
    font-weight: 600;
}

/* Freeze label */
.table tfoot .summary-label {
    position: sticky;
    left: 0;
    z-index: 120;
    background: inherit;
}

/* Freeze amount column */
/*.table tfoot .summary-value {
    position: sticky;
    left: 480px; 
    z-index: 120;
    background: inherit;
}
*/
.table tfoot td:nth-child(2){ position: sticky; left: 480px; /* 20 + 130 + 150 + 180 */ z-index: 120; background: inherit; }

/* Footer colors */

.summary-expense td {
    background: #fff8e1;
}

.summary-total td {
    background: #ffeeba;
}

.summary-net td {
    background: #e8f4fd;
}

.summary-profit td {
    background: #d4edda;
}

.table-footer{
    background:#d4edda;
}

/* ================= PAGINATION ================= */

.pagination {
    justify-content: center;
}

.col-total {
    background: #d4edda !important;
    color: #155724;
    font-weight: 600;
}

/* Dark Green */
.col-profit {
    background: #198754 !important;
    color: #fff !important;
    font-weight: 700;
}

</style>

<div class="card-primary mb-3">
    <div class="card-header reports-head">
        <h3 class="card-title">Price Schedule Filters</h3>
    </div>
</div>

{{-- FILTERS --}}
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
                        'action_type',
                        'exclude_product'
                    ]);
                @endphp
                <div class="col-xl-3 col-md-3 col-12 position-relative">
                    <div class="form-group">
                        <label class="filter-label">Order Date</label>
                        <input 
                            type="text" 
                            name="booking_date"
                            
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
                <div class="col-xl-3 col-md-3 col-12 position-relative">
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
                <div class="col-md-3 col-6">
                    <label class="filter-label">Products</label>
                    <select id="productFilter" name="product[]" class="form-control" multiple>
                        @foreach($selectedProducts as $sp)
                            <option value="{{ $sp->id }}" selected>{{ $sp->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="filter-label">Excluded Products</label>
                    <select id="excludeProductFilter" name="exclude_product[]" class="form-control" multiple>
                        @foreach($excludedProducts as $ep)
                            <option value="{{ $ep->id }}" selected>{{ $ep->title }}</option>
                        @endforeach
                    </select>
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
                <div class="col-xl-3 col-md-3 col-12">
                    <div class="form-group">
                            <label class="filter-label">Sort By</label>
                            <select name="order_by" id="order_by" class="form-control">
                                <option value="">Sort By</option>

                                <option value="tour_date_desc"
                                    {{ request('order_by') == 'tour_date_desc' ? 'selected' : '' }}>
                                    Tour Date (Newest First)
                                </option>

                                <option value="tour_date_asc"
                                    {{ request('order_by') == 'tour_date_asc' ? 'selected' : '' }}>
                                    Tour Date (Oldest First)
                                </option>

                                <option value="booking_date_desc"
                                    {{ request('order_by') == 'booking_date_desc' ? 'selected' : '' }}>
                                    Booking Date (Newest First)
                                </option>

                                <option value="booking_date_asc"
                                    {{ request('order_by') == 'booking_date_asc' ? 'selected' : '' }}>
                                    Booking Date (Oldest First)
                                </option>

                                <!-- <option value="revenue_desc"
                                    {{ request('order_by') == 'revenue_desc' ? 'selected' : '' }}>
                                    Revenue (High → Low)
                                </option>

                                <option value="revenue_asc"
                                    {{ request('order_by') == 'revenue_asc' ? 'selected' : '' }}>
                                    Revenue (Low → High)
                                </option> -->
                            </select>
                        </div>
                    </div>


                {{-- BUTTONS --}}
                <div class="col-xl-3 col-md-3 col-12">
                    <div class="d-flex column-gap-10">
                        <button class="btn btn-apply flex-fill">Apply</button>
                        <a href="{{ route('admin.report.price_schedule') }}" class="btn btn-secondary flex-fill">Reset</a>
                    </div>
                </div>

            </div>
        </form>
    </div>
@if(!request()->filled('booking_date') && !request()->filled('tour_date'))
    <div class="alert alert-info">
        Please apply at least one date filter (Booking Date or Tour Date) to view the report.
    </div>
@endif
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
                        <a href="{{ request()->fullUrlWithQuery(['booking_date' => null]) }}" class="remove-filter" data-filter="booking_date"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- TOUR DATE --}}
                @if(request('tour_date'))
                    <span class="badge bg-dark ml-2">
                        Tour: {{ request('tour_date') }}
                        <a href="{{ request()->fullUrlWithQuery(['tour_date' => null]) }}" class="remove-filter" data-filter="tour_date"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PRODUCT --}}
                

                {{-- STATUS --}}
                @if(request('order_status'))
                    <span class="badge bg-dark  ml-2">
                        Status: {{ $status_with_code[request('order_status')] ?? request('order_status') }}
                        <a href="{{ request()->fullUrlWithQuery(['order_status' => null]) }}" class="remove-filter" data-filter="order_status"><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PAY TYPE --}}
                @if(request('action_type'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('action_type') }}
                        <a href="{{ request()->fullUrlWithQuery(['action_type' => null]) }}" class="remove-filter" data-filter="action_type"><span class="ml-2">✕</span></a>
                    </span>
                @endif
                 @if(request('partner'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('partner') }}
                        <a href="{{ request()->fullUrlWithQuery(['partner' => null]) }}" class="remove-filter" data-filter="action_type"><span class="ml-2">✕</span></a>
                    </span>
                @endif

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
{{-- TABLE --}}

    <div class="card card-primary bg-white border rounded-lg-custom report-table">

    @if($rows)
        <table style="width:100%; border-collapse:separate; border-spacing:20px 0; margin-bottom:25px; margin-top: 20px;">
            <tr>

                <td style="
                    width:25%;
                    border:2px solid #d6e4ff;
                    background:#f8fbff;
                    border-radius:8px;
                    text-align:center;
                    padding:18px;
                ">
                    <div style="font-size:13px;color:#666;font-weight:600;">
                        TOTAL BOOKINGS
                    </div>

                    <div style="font-size:24px;font-weight:bold;color:#1f2937;margin-top:8px;">
                        {{count($rows)}}
                    </div>
                </td>

                <td style="
                    width:25%;
                    border:2px solid #d1fae5;
                    background:#f0fdf4;
                    border-radius:8px;
                    text-align:center;
                    padding:18px;
                ">
                    <div style="font-size:13px;color:#666;font-weight:600;">
                        TOTAL PROFIT
                    </div>

                    <div style="font-size:24px;font-weight:bold;color:#15803d;margin-top:8px;">
                        {{ number_format_with_currency($totals['profit'] - $totals['balance_amount'],2) }}
                    </div>
                </td>

                <td style="
                    width:25%;
                    border:2px solid #fee2e2;
                    background:#fef2f2;
                    border-radius:8px;
                    text-align:center;
                    padding:18px;
                ">
                    <div style="font-size:13px;color:#666;font-weight:600;">
                        TOTAL ADS EXPENSES
                    </div>

                    <div style="font-size:24px;font-weight:bold;color:#dc2626;margin-top:8px;">
                        {{ number_format_with_currency($businessExpense['total'],2) }}
                    </div>
                </td>

                <td style="
                    width:25%;
                    border:2px solid #fde68a;
                    background:#fffbeb;
                    border-radius:8px;
                    text-align:center;
                    padding:18px;
                ">
                    <div style="font-size:13px;color:#666;font-weight:600;">
                        TOTAL REVENUE
                    </div>

                    <div style="font-size:24px;font-weight:bold;color:#b45309;margin-top:8px;">
                        {{  number_format_with_currency($totals['profit'] - $totals['balance_amount'] - $businessExpense['total'],2)  }}
                    </div>
                </td>

            </tr>
        </table>
    @endif

        <div class="card-header report-table-head">
            <div class="row">
                <div class="col-md-8 col-12">
                    <h3 class="card-title">Price Schedule List</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <!-- <a href="{{ route('admin.report.invoice.details.export', request()->all()) }}"
                           class="btn btn-success btn-sm">
                            Download Excel
                        </a> -->
                        <a 
                            href="{{ route('admin.report.price_schedule.export', request()->query()) }}" 
                            class="btn btn-success"
                        >
                            <i class="fas fa-download"></i> Download Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-wrapper" id="tableWrapper">
          <table class="table table-bordered" style=" margin: 15px 20px;">

                <thead>
    <tr>
        <th>#</th>
        <th>Order</th>
        <!-- <th>Order Date</th> -->
        <th>Customer</th>
        <th class="product-name">Product</th>

        <th >Product Price</th>
        <th>Extra Amount</th>
        <th>Tax Amount</th>
        <th>Discount</th>
        <th>Excluded</th>
        <th>Excluded Balance</th>
        <th class="col-total">Customer Total</th>
        <!-- <th>Excluded Total</th> -->
        <th>Order Balance</th>

        <!-- <th>Transport Cost - Tax</th> -->
        <th>Supplier Price</th>
        <th>Extra Included</th>
        <th>Extra Excluded</th>
        <th>Supplier Tax</th>
        <!-- <th>Other Fee</th> -->
        <th class="col-total">Supplier Total</th>
        <th class="col-profit">Profit</th>
        <!-- <th></th> -->
        <th>Addons</th>

    </tr>


</thead>

                <tbody>

                    @php


                      $totalAddonQnty = 0;
                      $totalAddonPrice = 0;

                    @endphp
                    @forelse($rows as $row) 
                    
                    <tr>

    {{-- No --}}
    <td>{{ $row['no'] ?? '' }}</td>

    {{-- Order --}}
    <td >

        <strong>
            <a href="{{ route('admin.orders.edit', encrypt($row['order_id'])) }}" target="_blank">
                {{ $row['order_number'] }}
            </a>
        </strong>

        <br>

        <small class="text-muted">
            Order :
            {{ \Carbon\Carbon::parse($row['order_date'])->format('Y-m-d') }}
        </small>

        <br>

        <small>
            Fulfilment :
            {{ $row['fulfilment_date'] }}
        </small>

    </td>

    {{-- Customer --}}
    <td>

        <strong>{{ $row['customer_name'] }}</strong>

        <br>

        <small>
            Adult :
            {{ $row['adult'] }}
            |
            Child :
            {{ $row['child'] }}
            |
            Infant :
            {{ $row['infant'] }}
            |
            Senior :
            {{ $row['senior'] }}
        </small>

        <br>

        <small>
            Qty :
            {{ $row['adult'] + $row['child'] + $row['infant'] + $row['other'] + $row['senior'] }}
        </small>

        <br>

        

    </td>
    <td class="product-name" style="word-wrap: ;">
            {{ $row['product_name'] }}

    </td>

    <td align="right">{{ number_format_with_currency($row['product_price'],2) }}</td>

    <td align="right">{{ number_format_with_currency($row['extra_amount'],2) }}</td>

    <td align="right">{{ number_format_with_currency($row['tax_amount'],2) }}</td>

    <td align="right">{{ number_format_with_currency($row['discount_amount'],2) }}</td>
    <!-- <td align="right">{{ number_format_with_currency($row['exclude_total'],2) }}</td> -->
    <td align="right">{{ number_format_with_currency($row['excluded_commission_payment'],2) }}</td>

    <td class="col-total" align="right">{{ number_format_with_currency($row['customer_total'],2) }} 

    @if($row['excluded_commission_payment'] > 0)
      <p class="text-danger">(<small>{{$row['customer_total'] + $row['excluded_commission_payment'] }}  - {{$row['excluded_commission_payment']}}</small>)</p>
    @endif

  </td>
    <!-- <td align="right">{{ number_format_with_currency($row['exclude_total'],2) }}  </td> -->

    <td align="right">{{ $row['excluded_balance_amount'] ? number_format_with_currency($row['excluded_balance_amount'],2) :  number_format_with_currency($row['balance_amount'],2) }}</td>

    <!-- <td align="right">{{ number_format_with_currency($row['transport_cost'],2) }}</td> -->

    <td align="right">{{ number_format_with_currency($row['tour_selling_price'],2) }}</td>
    <td align="right">{{ number_format_with_currency($row['tour_extra_included_price'],2) }} </td>
    <td align="right">{{ number_format_with_currency($row['tour_extra_excluded_price'],2) }} </td>
    <!-- <td align="right"> Extra Excluded</td> -->

    <td align="right">{{ number_format_with_currency($row['tour_selling_tax'],2) }}</td>

    <!-- <td align="right">0</td> -->

    <td class="col-total" align="right">
        {{ number_format_with_currency(($row['tour_selling_total'] + $row['transport_cost']),2) }}
    </td>

    <td class="col-profit" align="right">

        @if($row['customer_total'] == 0)
            0
        @else
           {{ number_format_with_currency(($row['customer_total'] - $row['balance_amount'] - $row['tour_selling_total'] - $row['transport_cost']),2) }}
        @endif
    </td>

          $totalAddonPrice += $row[$key.'_total'];

        @endphp
        <strong>{{ $row[$key.'_desc'] ?? '-' }}</strong><br>

        Qty :
        {{ $row[$key.'_quant'] ?? 0 }} |

        <!-- <br> -->

        <!-- Price :
        {{ number_format_with_currency($row[$key.'_price'] ?? 0,2) }} --> 

        Total :
        <strong>
            {{ number_format_with_currency($row[$key.'_total'] ?? 0,2) }}
        </strong>
        <br>
   

    @endif

<!-- </td> -->

@endforeach</td>


    {{-- Dynamic Addons --}}

</tr>


    @empty
    <tr>
        <td colspan="{{ 8 + (count($addonKeys) * 5) }}" class="text-center">
            No Data Found
        </td>
    </tr>
    @endforelse
    @if($rows)
    <tr style="background:#eef2f7;font-weight:700;">
    <td colspan="4" style="position: sticky;
    background: #fff;
    z-index: 50;">Grand Total</td>


    <td align="right">{{ number_format_with_currency($totals['product_price'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['extra_amount'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['tax_amount'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['discount_amount'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['excluded_commission_payment'],2) }}</td>


    <td class="col-total" align="right">{{ number_format_with_currency($totals['customer_total'],2) }}</td>
    <!-- <td align="right">{{ number_format_with_currency($totals['exclude_total'],2) }}</td> -->
    <td align="right">{{ number_format_with_currency($totals['balance_amount'],2) }}</td>
    <!-- <td align="right">{{ number_format_with_currency($totals['transport_cost'],2) }}</td> -->
    <td align="right">{{ number_format_with_currency($totals['tour_selling_price'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['tour_extra_included_price'],2) }} </td>
    <td align="right">{{ number_format_with_currency($totals['tour_extra_excluded_price'],2) }} </td>
    <td align="right">{{ number_format_with_currency($totals['tour_selling_tax'],2) }}</td>

    <!-- <td align="center">-</td> -->

    <td class="col-total" align="right">{{ number_format_with_currency($totals['net_total'],2) }}</td>

    <td class="col-profit" align="right">{{ number_format_with_currency($totals['profit'] - $totals['balance_amount'],2) }}</td>

    <td>
    <strong>Qty:</strong>
    {{ $totalAddonQnty }}

    <!-- <br> -->

    <!-- <strong>Price:</strong> -->
    <!-- {{ number_format_with_currency($totals['addonTotals']['price'], 2) }} -->

    <br>

    <strong>Total:</strong>
    {{ number_format_with_currency($totalAddonPrice, 2) }}
</td>
</tr>





@endif
</tbody>
@if($rows)
<tfoot class="table-footer">


        <tr class="summary-total">
            <td colspan="4" class="summary-label">
                Total Product Amount
            </td>

            <td align="right">
                {{ number_format_with_currency($totals['customer_total'],2) }}
            </td>

            <td colspan="13"></td>
        </tr>
        <tr class="summary-total">
            <td colspan="4" class="summary-label">
                Total Pending Balance
            </td>

            <td align="right">
                {{ number_format_with_currency($totals['balance_amount'],2) }}
            </td>

            <td colspan="13"></td>
        </tr>
        <!-- @foreach($businessExpense['expenses'] as $expense)
        <tr class="summary-expense">
            <td colspan="4" class="summary-label">
                {{ ucwords($expense->category) }}
            </td>

            <td align="right">
                {{ number_format_with_currency($expense->amount,2) }}
            </td>

            <td colspan="13"></td>
        </tr>
        @endforeach -->
        <tr class="summary-total">
            <td colspan="4" class="summary-label">
                Supplier Total Expense
            </td>

            <td align="right">
               {{ number_format_with_currency($totals['net_total'],2) }}
            </td>

            <td colspan="13"></td>
        </tr>

        <tr class="summary-total">
            <td colspan="4" class="summary-label">
                Total Ads Expense
            </td>

            <td align="right">
                {{ number_format_with_currency($businessExpense['total'],2) }}
            </td>

            <td colspan="13"></td>
        </tr>
        

        <!-- <tr class="summary-net">
            <td colspan="4" class="summary-label">
                Total Expense <small>(Supplier Total + Ads Expense)</small>
            </td>

            <td align="right">
                {{ number_format_with_currency($totals['net_total'] + $businessExpense['total'],2) }}
            </td>

            <td colspan="13"></td>
        </tr> -->

        <tr class="summary-profit">
            <td colspan="4" class="summary-label">
                Final Profit <small>(Customer Total - Expenses)</small>
            </td>

            <td align="right">
                {{ number_format_with_currency(
                    $totals['customer_total']- $totals['balance_amount'] - ($totals['net_total'] + $businessExpense['total']),
                    2
                ) }}
            </td>

            <td colspan="13"></td>
        </tr>
    </tfoot>
    @endif
          </table>


          {{-- PAGINATION --}}
          @if($orders instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="text-center">
                {{ $orders->links() }}
            </div>
        @endif

        </div>
  </div>




@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  let today = moment();
            

        // ✅ Select2 (optimized)
        function initTourSelect(selector, isMultiple, placeholderText) {
            $(selector).select2({
                placeholder: placeholderText,
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

const tableWrapper = document.getElementById("tableWrapper");

let isDragging = false;
let startX = 0;
let startY = 0;
let scrollLeft = 0;
let scrollTop = 0;

tableWrapper.addEventListener("mousedown", function (e) {
    isDragging = true;
    tableWrapper.classList.add("active");

    startX = e.pageX - tableWrapper.offsetLeft;
    startY = e.pageY - tableWrapper.offsetTop;

    scrollLeft = tableWrapper.scrollLeft;
    scrollTop = tableWrapper.scrollTop;
});

tableWrapper.addEventListener("mouseleave", stopDragging);
tableWrapper.addEventListener("mouseup", stopDragging);

function stopDragging() {
    isDragging = false;
    tableWrapper.classList.remove("active");
}

tableWrapper.addEventListener("mousemove", function (e) {
    if (!isDragging) return;

    e.preventDefault();

    const x = e.pageX - tableWrapper.offsetLeft;
    const y = e.pageY - tableWrapper.offsetTop;

    tableWrapper.scrollLeft = scrollLeft - (x - startX);
    tableWrapper.scrollTop = scrollTop - (y - startY);
});


</script>

@endsection

</x-admin>
