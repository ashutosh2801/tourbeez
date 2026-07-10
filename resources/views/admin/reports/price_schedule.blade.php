<x-admin>
@section('title', 'Price Schedule')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    .table {
    font-size: 14px;
}

.table th {
    font-size: 14px;
    font-weight: 600;
    padding: 10px 8px;
}

.table td {
    font-size: 14px;
    padding: 10px 8px;
    vertical-align: middle;
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

    .table tbody tr:hover {
    background: #f5f9ff;
}

.table td {
    vertical-align: middle;
}
.table-wrapper {
    overflow: auto;
    cursor: grab;
    position: relative;
}

.table-wrapper.active {
    cursor: grabbing;
}
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
.table-wrapper {
    overflow-x: auto;
    position: relative;
}

/* Freeze first 4 columns */
/* 1st */
/* ---------------- First 4 Frozen Columns ---------------- */

/* No */
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

/* Header above body */
.table thead th {
    position: sticky;
    top: 0;
    background: #212529;
    color: #fff;
    z-index: 30;
    font-weight: 600;
}

.table thead th:nth-child(1),
.table thead th:nth-child(2),
.table thead th:nth-child(3),
.table thead th:nth-child(4) {
    z-index: 50;
}

/* Border for frozen columns */
.table td:nth-child(-n+4),
.table th:nth-child(-n+4) {
    /*box-shadow: 2px 0 4px rgba(0,0,0,.08);*/
    box-shadow: 1px 0 0 #dee2e6;
}
.product-name {
    white-space: normal !important;
    word-break: break-word;
    overflow-wrap: anywhere;
    max-width: 220px; /* Adjust as needed */
    line-height: 1.4;
}

.table thead th {
    background: #212529 !important;
    /*color: #fff;*/
}

.table th:nth-child(-n+4),
.table td:nth-child(-n+4) {
    /*background: #fff;*/
}

.table td:nth-child(-n+4),
.table th:nth-child(-n+4) {
    box-shadow: 2px 0 4px rgba(0,0,0,.08);
}



.table tfoot td {
    font-weight: 600;
}

/* Freeze first four columns */
.table tfoot .summary-label{
    position: sticky;
    left: 0;
    z-index: 40;
    background: inherit;
}

.summary-expense{
    background:#fff8e1;
}

.summary-total{
    background:#ffeeba;
}

.summary-net{
    background:#e8f4fd;
}

.summary-profit{
    background:#d4edda;
}

/* Freeze the first cell (colspan=4) */
.table tfoot td.summary-label{
    position: sticky;
    left: 0;
    z-index: 100;
    background: inherit;
}

/* Freeze the amount column (5th column) ONLY IN FOOTER */
.table tfoot td:nth-child(2){
    position: sticky;
    left: 480px; /* 20 + 130 + 150 + 180 */
    z-index: 100;
    background: inherit;
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
                <div class="col-xl-3 col-md-3 col-12">
                    <label class="filter-label">Pay Type</label>
                    <select name="action_type" class="form-control">
                        <option value="">All</option>
                        <option value="pay_now" {{ request('action_type')=='pay_now'?'selected':'' }}>Pay Now</option>
                        <option value="pay_later" {{ request('action_type')=='pay_later'?'selected':'' }}>Pay Later</option>
                    </select>
                </div>

                {{-- SOURCE --}}
                <div class="col-xl-3 col-md-3 col-12">
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
@if(!request()->hasAny(['booking_date','tour_date','product','order_status','payment_status','partner','action_type', 'exclude_product']))
        <div class="alert alert-info">
            Please apply filters to view report data.
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
        <th>Customer Total</th>
        <th>Excluded Total</th>
        <th>Order Balance</th>

        <th>Transport Cost - Tax</th>
        <th>Supplier Price</th>
        <th>Supplier Tax</th>
        <th>Other Fee</th>
        <th>Net Total</th>
        <th>Profit</th>
        <!-- <th></th> -->
        <th>Addons</th>

    </tr>


</thead>

                <tbody>
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

    <td align="right">{{ number_format_with_currency($row['customer_total'],2) }}</td>
    <td align="right">{{ number_format_with_currency($row['exclude_total'],2) }}  </td>

    <td align="right">{{ number_format_with_currency($row['balance_amount'],2) }}</td>

    <td align="right">{{ number_format_with_currency($row['transport_cost'],2) }}</td>

    <td align="right">{{ number_format_with_currency($row['tour_selling_price'],2) }}</td>

    <td align="right">{{ number_format_with_currency($row['tour_selling_tax'],2) }}</td>

    <td align="right">0</td>

    <td align="right">
        {{ number_format_with_currency(($row['tour_selling_total'] + $row['transport_cost']),2) }}
    </td>

    <td align="right">
        {{ number_format_with_currency(($row['customer_total'] - $row['tour_selling_total'] - $row['transport_cost']),2) }}
    </td>

    <td>@foreach($addonKeys as $key)

<!-- <td > -->

    @if(
        !empty($row[$key.'_desc']) ||
        !empty($row[$key.'_quant']) ||
        !empty($row[$key.'_price']) ||
        !empty($row[$key.'_tax']) ||
        !empty($row[$key.'_fee']) ||
        !empty($row[$key.'_total'])
    )

        <strong>{{ $row[$key.'_desc'] ?? '-' }}</strong><br>

        Qty :
        {{ $row[$key.'_quant'] ?? 0 }} |

        <!-- <br> -->

        Price :
        {{ number_format_with_currency($row[$key.'_price'] ?? 0,2) }} |

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
    <td align="right">{{ number_format_with_currency($totals['customer_total'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['exclude_total'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['balance_amount'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['transport_cost'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['tour_selling_price'],2) }}</td>
    <td align="right">{{ number_format_with_currency($totals['tour_selling_tax'],2) }}</td>

    <td align="center">-</td>

    <td align="right">{{ number_format_with_currency($totals['net_total'],2) }}</td>

    <td align="right">{{ number_format_with_currency($totals['profit'],2) }}</td>

    <td>
    <strong>Qty:</strong>
    {{ $totals['addonTotals']['qty'] }}

    <br>

    <strong>Price:</strong>
    {{ number_format_with_currency($totals['addonTotals']['price'], 2) }}

    <br>

    <strong>Total:</strong>
    {{ number_format_with_currency($totals['addonTotals']['total'], 2) }}
</td>
</tr>





@endif
</tbody>
@if($rows)
<tfoot class="table-footer">
        @foreach($businessExpense['expenses'] as $expense)
        <tr class="summary-expense">
            <td colspan="4" class="summary-label">
                {{ ucwords($expense->category) }}
            </td>

            <td align="right">
                {{ number_format_with_currency($expense->amount,2) }}
            </td>

            <td colspan="13"></td>
        </tr>
        @endforeach

        <tr class="summary-total">
            <td colspan="4" class="summary-label">
                Total Business Expense
            </td>

            <td align="right">
                {{ number_format_with_currency($businessExpense['total'],2) }}
            </td>

            <td colspan="13"></td>
        </tr>

        <tr class="summary-net">
            <td colspan="4" class="summary-label">
                Net Total + Business Expense
            </td>

            <td align="right">
                {{ number_format_with_currency($totals['net_total'] + $businessExpense['total'],2) }}
            </td>

            <td colspan="13"></td>
        </tr>

        <tr class="summary-profit">
            <td colspan="4" class="summary-label">
                Final Profit
            </td>

            <td align="right">
                {{ number_format_with_currency(
                    $totals['customer_total'] - ($totals['net_total'] + $businessExpense['total']),
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