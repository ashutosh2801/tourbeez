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

    /* FORCE CONSISTENT HEIGHT ALWAYS */
    .select2-container {
        width: 100% !important;
    }

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

/* Revenue filter */
.revenue-filter-card {
    padding: 20px;
    border-color: #e5e7eb !important;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
}
.revenue-filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid #eef0f3;
}
.revenue-filter-header h5 {
    margin: 0 0 3px;
    color: #172033;
    font-size: 16px;
    font-weight: 700;
}
.revenue-filter-header p {
    margin: 0;
    color: #6b7280;
    font-size: 12px;
}
.revenue-filter-count {
    padding: 5px 10px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.revenue-filter-header-actions {
    display: flex;
    align-items: center;
    gap: 9px;
}
.revenue-filter-toggle {
    height: 36px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0 12px;
    border: 1px solid #c7d2fe;
    border-radius: 7px;
    background: #fff;
    color: #4338ca;
    font-size: 13px;
    font-weight: 600;
}
.revenue-filter-toggle:hover { background: #eef2ff; }
.revenue-filter-toggle .fa-chevron-down {
    font-size: 10px;
    transition: transform .2s ease;
}
.revenue-filter-card.is-expanded .revenue-filter-toggle .fa-chevron-down {
    transform: rotate(180deg);
}
.revenue-filter-content { display: none; }
.revenue-filter-card.is-expanded .revenue-filter-content { display: block; }
.revenue-filter-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}
.revenue-filter-grid > div {
    width: auto;
    max-width: none;
    padding: 0;
    min-width: 0;
}
.revenue-filter-card .form-group { margin-bottom: 0; }
.revenue-filter-card .filter-label {
    display: block;
    margin-bottom: 6px;
    color: #374151;
    font-size: 12px;
    font-weight: 600;
}
.revenue-filter-card .form-control {
    min-height: 42px;
    border-color: #d7dce3;
    border-radius: 7px;
    background: #fff;
    font-size: 13px;
}
.revenue-filter-card .select2-container--default .select2-selection--multiple {
    min-height: 42px !important;
    margin-bottom: 0 !important;
    padding: 4px 8px !important;
    border-color: #d7dce3 !important;
    border-radius: 7px !important;
}
.revenue-filter-actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 16px !important;
    border-top: 1px solid #eef0f3;
}
.revenue-filter-actions .btn {
    min-width: 120px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
}
@media (max-width: 991.98px) {
    .revenue-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .revenue-filter-card { padding: 15px; }
    .revenue-filter-header { display: block; }
    .revenue-filter-header-actions { margin-top: 10px; justify-content: space-between; }
    .revenue-filter-grid { grid-template-columns: 1fr; }
    .revenue-filter-actions { display: grid; grid-template-columns: 1fr 1fr; }
    .revenue-filter-actions .btn { min-width: 0; width: 100%; }
}
</style>


    <div class="card-primary mb-3">
        <div class="card-header reports-head">
            <h3 class="card-title">Revenue</h3>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="card card-primary bg-white border report-filter-box revenue-filter-card">
        <form method="GET">
            @php
                    $activeFilterCount = collect([
                        'booking_date', 'tour_date', 'product', 'order_status',
                        'partner', 'action_type', 'exclude_product', 'order_by'
                    ])->filter(function ($key) {
                        $value = request($key);
                        return is_array($value) ? count(array_filter($value)) > 0 : request()->filled($key);
                    })->count();
            @endphp
            <div class="revenue-filter-header">
                <div>
                    <h5><i class="fas fa-filter mr-2 text-primary"></i>Filter Revenue</h5>
                    <p>Narrow the report by date, product, status or source.</p>
                </div>
                <div class="revenue-filter-header-actions">
                    <span class="revenue-filter-count">{{ $activeFilterCount }} active</span>
                    <button type="button" class="revenue-filter-toggle" id="revenueFilterToggle" aria-expanded="false" aria-controls="revenueFilterContent">
                        <i class="fas fa-filter"></i>
                        <span>Show Filters</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div class="revenue-filter-content" id="revenueFilterContent">
              <div class="revenue-filter-grid">

                {{-- BOOKING DATE --}}
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
                    <div class="form-group">
                        <label class="filter-label">Products</label>
                        <select id="productFilter" name="product[]" class="form-control" multiple>
                            @foreach($selectedProducts as $sp)
                                <option value="{{ $sp->id }}" selected>{{ $sp->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="form-group">
                        <label class="filter-label">Excluded Products</label>
                        <select id="excludeProductFilter" name="exclude_product[]" class="form-control" multiple>
                            @foreach($excludedProducts as $ep)
                                <option value="{{ $ep->id }}" selected>{{ $ep->title }}</option>
                            @endforeach
                        </select>
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
                <div class="revenue-filter-actions">
                    <a href="{{ route('admin.report.revenue') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo-alt"></i> Reset
                    </a>
                    <button class="btn btn-apply" type="submit">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
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
                        <a href="{{ request()->fullUrlWithQuery(['booking_date' => null]) }}" ><span class="ml-2">✕</span></a>
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
                        <a href="{{ request()->fullUrlWithQuery(['order_status' => null]) }}" ><span class="ml-2">✕</span></a>
                    </span>
                @endif

                {{-- PAY TYPE --}}
                @if(request('action_type'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('action_type') }}
                        <a href="{{ request()->fullUrlWithQuery(['action_type' => null]) }}" ><span class="ml-2">✕</span></a>
                    </span>
                @endif
                 @if(request('partner'))
                    <span class="badge bg-dark ml-2">
                        Pay: {{ request('partner') }}
                        <a href="{{ request()->fullUrlWithQuery(['partner' => null]) }}" ><span class="ml-2">✕</span></a>
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

                        <th>Total Pax</th>
                        <th>Adult</th>
                        <th>Child</th>
                        <th>Infant</th>
                        <th>Senior Citizen</th>

                        
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

                            <td>{{ \Carbon\Carbon::parse($order->booking_date)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->fulfilment_date)->format('Y-m-d') }}</td>

                            <td>{{ trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? '')) ?: '-' }}</td>

                            {{-- ✅ MONEY (FROM FIXED BACKEND LOGIC) --}}
                            <td>{{ number_format_with_currency($order->total_amount_converted, 2) }}</td>
                            <td>{{ number_format_with_currency($order->paid_amount_converted, 2) }}</td>
                            <td class="{{ $order->balance_converted > 0.01 ? 'text-danger font-weight-bold' : 'text-success' }}">{{ $order->balance_converted > 0 ? number_format_with_currency($order->balance_converted, 2) : 0 }}</td>

                            {{-- Fees (keep 0 if not calculated yet) --}}
                            <td>{{ number_format_with_currency($order->booking_fee ?? 0, 2) }}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>

                            {{-- Commission & Tax (if added later) --}}
                            <td>{{ number_format_with_currency($order->commission ?? 0, 2) }}</td>
                            <td>{{ number_format_with_currency($order->tax_converted ?? 0, 2) }}</td>

                            {{-- Net Sales --}}
                            <td>{{ number_format_with_currency($order->net_sales_converted ?? $order->total_amount_converted, 2) }}</td>

                            {{-- Pax --}}
                            <td>{{ $order->pax ?? 0 }}</td>
                            <td>{{ $order->adult }}</td>
                            <td>{{ $order->child }}</td>
                            <td>{{ $order->infant }}</td>
                            <td>{{ $order->other }}</td>
                            

                            {{-- Product Value --}}
                            <td>{{ number_format_with_currency($order->product_value_converted, 2) }}</td>

                            <td>{{ number_format_with_currency($order->discount_value_converted, 2) }}</td> {{-- Adjustment --}}
                            <td>{{ number_format_with_currency($order->extra_value_converted, 2) }}</td> {{-- Extra Value --}}

                            {{-- Promo --}}
                            <td>{{ number_format_with_currency($order->promo_amount ?? 0, 2) }}</td>

                            {{-- Payment Split --}}
                            <td>{{ number_format_with_currency($order->card_payment ?? 0, 2) }}</td>
                            <td>{{ number_format_with_currency($order->cash_payment ?? 0, 2) }}</td>

                            <td>{{ number_format_with_currency($order->promo_amount ?? 0, 2) }}</td>

                            <td>0</td> {{-- Free --}}
                            <td>{{ number_format_with_currency($order->refunded ?? 0, 2) }}</td>

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

    // // Set hidden fields initially
    // $('#start_date').val(bookingStart.format('YYYY-MM-DD'));
    // $('#end_date').val(bookingEnd.format('YYYY-MM-DD'));

    // // Update on apply
    // $('#booking_range').on('apply.daterangepicker', function(ev, picker) {
    //     $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
    //     $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
    // });

    // /*
    // |--------------------------------------------------------------------------
    // | TOUR RANGE
    // |--------------------------------------------------------------------------
    // */
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
    //     function clearBooking() {
    //         $('#booking_range').val('');
    //         $('#start_date').val('');
    //         $('#end_date').val('');

    //         // reset picker UI as well
    //         let picker = $('#booking_range').data('daterangepicker');
    //         picker.setStartDate(moment());
    //         picker.setEndDate(moment());
            

            
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | CLEAR TOUR RANGE
    //     |--------------------------------------------------------------------------
    //     */
    //     function clearTour() {
    //         $('#tour_range').val('');
    //         $('#tour_start_date').val('');
    //         $('#tour_end_date').val('');

    //         // reset picker UI
    //         let picker = $('#tour_range').data('daterangepicker');
    //         picker.setStartDate(moment());
    //         picker.setEndDate(moment());
            

    //     }



</script>

    <script>
    $(document).ready(function () {
        $('#revenueFilterToggle').on('click', function () {
            const $card = $('.revenue-filter-card');
            const expanded = !$card.hasClass('is-expanded');

            $card.toggleClass('is-expanded', expanded);
            $(this).attr('aria-expanded', expanded ? 'true' : 'false');
            $(this).find('span').text(expanded ? 'Hide Filters' : 'Show Filters');
        });

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

//         if ($('#productFilter').val() && !$('#product_text').val()) {
//     let selectedText = $('#productFilter option:selected').text();
//     $('#product_text').val(selectedText);
// }
</script>

@endsection

</x-admin>
