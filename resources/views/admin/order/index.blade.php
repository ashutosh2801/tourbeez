<x-admin>
    <style>
        body.sidebar-open {
            overflow: hidden;
        }
        .text-orange {
            color: #fd7e14;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #fd7e14;
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 2px 8px;
            margin: 0;
            line-height: 1.6;
        }

        #excludeProductFilter + .select2-container .select2-selection--multiple .select2-selection__choice {
            background-color: #dc3545; /* red for excluded, orange for included */
        }

        @media(min-width:767px) {

            .daterangepicker.show-calendar {
                top: 245px !important;
                left: auto;
                right: 430px !important;
            }

            .daterangepicker.show-calendar:before,
            .daterangepicker.show-calendar:after {
                left: 595px;
                border-bottom-color: #999;
                rotate: 90deg;
                top: 200px;
            }

            .daterangepicker.show-calendar:nth-of-type(2):before,
            .daterangepicker.show-calendar:nth-of-type(2):after {
                top: 140px;
            }
        }

        @media(max-width:767px) {

            .daterangepicker.show-calendar {
                height: 200px;
                overflow-y: scroll;
            }

        }

    </style>
    @section('title', 'Orders List')

    <div class="card-primary mb-3">
        <div class="card-header order-list-head">
            <div class="row">
                <div class="col-md-8 col-6">
                    <h3 class="card-title text-white">Order List</h3>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card-tools">
                        <button type="button" class="btn btn-secondary" id="toggleFilter">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="order-list-body card rounded-lg-custom border">
        @php
            $statuses = config('constants.status_with_code');
        @endphp

        {{-- Filter/Search Form --}}
        <div class="tour-search-filter">
            <div id="filterSidebar" class="filter-sidebar">
                <div class="filter-header">
                    <h5><i class="fas fa-filter"></i> Filters</h5>
                    <button type="button" id="closeFilter">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="GET" action="{{ route('admin.orders.index') }}">
                    <div class="filter-body">
                        <div class="search-options">
                            <div class="row">
                                <div class="col-md-12 col-12">
                                    <input type="text" name="search" class="form-control" placeholder="Order # / Customer First / Last Name / Email" value="{{ request('search') }}">
                                </div>
                                <!-- <div class="col-md-4 col-6">
                                    <select name="product" class="form-control aiz-selectpicker" data-live-search="true">
                                        <option value="">Select Tour</option>
                                        @foreach ($products->sortBy('title') as $product)
                                            <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>{{ $product->title }}</option>
                                        @endforeach
                                    </select>
                                </div> -->
                                <div class="col-md-12 col-12">
                                    <select id="productFilter" name="product[]" class="form-control" multiple>
                                        @foreach($selectedProducts as $sp)
                                            <option value="{{ $sp->id }}" selected>{{ $sp->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 col-12">
                                    <select id="excludeProductFilter" name="exclude_product[]" class="form-control" multiple>
                                        @foreach($excludedProducts as $ep)
                                            <option value="{{ $ep->id }}" selected>{{ $ep->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 col-12">
                                    <select name="payment_status" class="form-control" >
                                        <option value="">Payment Status</option>
                                        <option value="1" {{ request('payment_status') === '1' ? 'selected' : '' }}>Paid</option>
                                        <option value="0" {{ request('payment_status') === '0' ? 'selected' : '' }}>Unpaid</option>
                                    </select>
                                </div>
                                <div class="col-md-12 col-12">
                                    <select name="order_status" class="form-control" >
                                        <option value="">Order Status</option>
                                        @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ request('order_status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 col-12"> 
                                    <input 
                                        type="text" 
                                        name="tour_start_date" 
                                        class="form-control aiz-date-range" 
                                        data-advanced-range="true" 
                                        data-separator=" - " 
                                        data-show-dropdown="true"
                                        data-tomorrow="true"
                                        data-yesterday="false"
                                        placeholder="Filter by Tour Date"
                                        autocapitalize="off" autocomplete="off" 

                                        value="{{ request('tour_start_date') }}"
                                    >
                                </div> 

                                <div class="col-md-12 col-12"> 
                                    <input 
                                        type="text" 
                                        name="order_created_date" 
                                        class="form-control aiz-date-range" data-advanced-range="true" data-separator=" - " data-show-dropdown="true"
                                        placeholder="Filter by Order Created"
                                        autocapitalize="off" autocomplete="off" 
                                        value="{{ request('order_created_date') }}"
                                    >
                                </div> 
                                
                                <?php 
                                    /* <div class="col-md-2 col-6">
                                        <select name="date_filter" class="form-control" >
                                            <option value="">Filter by Order Created</option>
                                            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                                            <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                            <option value="last_7" {{ request('date_filter') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                                            <option value="last_15" {{ request('date_filter') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                                            <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                            <option value="last_90" {{ request('date_filter') == 'last_90' ? 'selected' : '' }}>Last 90 Days</option>
                                            <option value="last_6_months" {{ request('date_filter') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                            <option value="this_year" {{ request('date_filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <select name="tour_date_filter" class="form-control" >
                                            <option value="">All Tour Dates</option>
                                            <option value="today" {{ request('tour_date_filter') == 'today' ? 'selected' : '' }}>Today</option>

                                            <option value="yesterday" {{ request('tour_date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>

                                            <option value="last_7" {{ request('tour_date_filter') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                                            <option value="last_15" {{ request('tour_date_filter') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                                            <option value="this_month" {{ request('tour_date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                            <option value="last_90" {{ request('tour_date_filter') == 'last_90' ? 'selected' : '' }}>Last 90 Days</option>
                                            <option value="last_6_months" {{ request('tour_date_filter') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                            <option value="this_year" {{ request('tour_date_filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                                        </select>
                                    </div> */ 
                                ?>

                                <div class="col-md-12 col-12">
                                    <select name="source" class="form-control">
                                        <option value="">Source</option>

                                        @foreach (source_list_db() as $source)
                                            <option value="{{ $source->key }}"
                                                {{ request('source') == $source->key ? 'selected' : '' }}>
                                                {{ $source->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="col-md-12 col-12">
                                    <select name="per_page" class="form-control">
                                        @foreach ([10, 25, 50, 100, 500] as $number)
                                            <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                                {{ $number }} per page
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filter-footer">
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search"></i> Search
                        </button>

                        <a href="{{ route('admin.orders.index') }}" class="btn btn-clear">
                            <i class="fas fa-times"></i> Clear Search
                        </a>
                    </div>
                </form>
                
            </div>
            <div id="filterOverlay"></div>
        </div>

        @php
            $hasActiveFilters =
                request('search') ||
                request()->filled('payment_status') ||
                request('order_status') ||
                request('tour_start_date') ||
                request('order_created_date') ||
                request('source') ||
                $selectedProducts->isNotEmpty() ||
                $excludedProducts->isNotEmpty();
        @endphp

        @if($hasActiveFilters)
        <div class="active-filters">
            <div class="d-flex flex-wrap gap-2">

                {{-- Search --}}
                @if(request('search'))
                    <span class="badge badge-dark mr-2 mt-2 mt-2 text-white">
                        Search: {{ request('search') }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['search' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Payment --}}
                @if(request()->filled('payment_status'))
                    <span class="badge badge-dark mr-2 mt-2">
                        Payment: {{ request('payment_status') ? 'Paid' : 'Unpaid' }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['payment_status' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Order Status --}}
                @if(request('order_status'))
                    <span class="badge badge-dark mr-2 mt-2">
                        Status: {{ $statuses[request('order_status')] }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['order_status' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Tour Date --}}
                @if(request('tour_start_date'))
                    <span class="badge badge-dark mr-2 mt-2">
                        Tour Date: {{ request('tour_start_date') }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['tour_start_date' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Created Date --}}
                @if(request('order_created_date'))
                    <span class="badge badge-dark mr-2 mt-2">
                        Created: {{ request('order_created_date') }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['order_created_date' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Source --}}
                @if(request('source'))
                    <span class="badge badge-dark mr-2 mt-2">
                        Source: {{ source_list(request('source')) }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['source' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Selected Tour --}}
                @if($selectedProducts->isNotEmpty())
                    @php $p = $selectedProducts->first(); @endphp
                    <span class="badge badge-dark mr-2 mt-2">
                        Tour: {{ $p->title }}
                        <a class="text-white ml-1" href="{{ request()->fullUrlWithQuery(['product' => null]) }}">✕</a>
                    </span>
                @endif

                {{-- Excluded Tours --}}
                @foreach($excludedProducts as $ep)
                    <span class="badge badge-dark mr-2 mt-2">
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
        </div>
        @endif        

        {{-- Bulk Delete --}}
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.order.bulkDelete') }}">
            @csrf
            @method('DELETE')
            <div class="card-header btn-options">
                <div class="card-tools">
                    <div>
                        <a type="button" class="btn btn-success" href="{{ route('admin.orders.create') }}">
                            <i class="fas fa-calendar-plus"></i> Create Internal Order
                        </a>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure to delete selected orders?')">
                            <i class="fas fa-trash-alt"></i> Delete Selected
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-ImpOrder" data-toggle="modal" data-target="#importOrderModal">
                            <i class="fas fa-file-import"></i> Import Order
                        </button>
                        <a type="button" class="btn btn-info" href="{{ route('admin.orders.index') }}">
                            <i class="fas fa-cog"></i> Refresh
                        </a>
                    </div>
                </div>
            </div>

            @if(session()->has('importResult'))
                @php

                    $r = session('importResult')

                @endphp

                <div class="bg-gray-100 p-4 rounded mb-4 text-sm">
                    <p>
                        <strong>Total:</strong> {{ $r['total'] }},
                        <span class="text-green-700"><strong>Imported:</strong> {{ $r['imported'] }}</span>,
                        <span class="text-yellow-700"><strong>Skipped:</strong> {{ $r['skipped'] }}</span>,
                        <span class="text-red-700"><strong>Failed:</strong> {{ $r['failed'] }}</span>
                    </p>

                    @if(!empty($r['errors']))
                        <ul class="mt-2 text-red-700 list-disc pl-5">
                            @foreach($r['errors'] as $e)
                                <li>Row {{ $e['row'] }} – {{ $e['reason'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if(session('import_summary'))
                @php $summary = session('import_summary'); @endphp

                <div class="alert alert-info">
                    <strong>Total:</strong> {{ $summary['total'] }} |
                    <strong>Imported:</strong> {{ $summary['imported'] }} |
                    <strong>Skipped:</strong> {{ $summary['skipped'] }} |
                    <strong>Failed:</strong> {{ $summary['failed'] }}
                </div>

                @if(!empty($summary['errors']))
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($summary['errors'] as $error)
                                <li>
                                    Row {{ $error['row'] }}:
                                    {{ implode(', ', $error['errors']) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
            
            <div class="card-body p-0 order-table table-responsive">
                <table class="table table-striped" id="OrderTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:4%;">
                                <input type="checkbox" id="checkAll" style="width:20px; height:20px;">
                            </th>

                            <th style="width:9%; white-space: nowrap;">
                                #
                            </th>

                            <th style="width:10%; white-space: nowrap;">
                                Status
                            </th>

                            <th style="width:24%; white-space: nowrap;">
                                Tour
                            </th>

                            <th style="width:12%; white-space: nowrap;">
                                Tour Date
                            </th>

                            <th style="width:13%; white-space: nowrap;">
                                Customer
                            </th>

                            <th style="width:10%; white-space: nowrap;">
                                Amount
                            </th>

                            <th style="width:10%; white-space: nowrap;">
                                Created
                            </th>

                            <th style="width:8%; white-space: nowrap;">
                                Source
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $order->id }}" style="width:20px; height:20px;"></td>
                                <td>
                                    <a href="{{ route('admin.orders.edit', encrypt($order->id)) }}" class="alink">{{ $order->order_number }}</a>
                                </td>
                                <td class="text-center">{!! order_status($order->order_status) !!} <br> <small>{{  $order->latestPaymentLog?->status ? in_array($order->latestPaymentLog?->status, ['success', 'authorized', 'failed', 'cancelled']) ? ucwords($order->latestPaymentLog?->status) : 'Failed' : '' }}</small></td>
                                <td class="">
                                    @foreach ($order->orderTours as $order_tour)

                                        <div style="display:flex; justify-content:space-start; align-items:center;">
                                            <a href="{{ route('admin.tour.edit', encrypt($order_tour->tour_id)) }}"
                                               class="alink"
                                               target="_blank"
                                               style="max-width:85%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">
                                                {{ $order->tour?->title }}
                                            </a>
                                            @if($loop->iteration == 1)
                                            <span class="font-bold ml-1">
                                                X {{ $order->orderTours->sum('number_of_guests') }}
                                            </span>
                                            @endif
                                        </div>

                                        @if($order->sub_tour_id && $order->subTour)
                                            <div style="max-width:85%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                <a href="{{ route('admin.tour.edit', encrypt($order_tour->tour->id)) }}"
                                                   class="alink text-small"
                                                   target="_blank"
                                                   style="font-size:small;">
                                                    {{ $order_tour->tour?->title }}
                                                </a>
                                            </div>
                                        @endif

                                    @endforeach
                                </td>
                                <td>

                                    @foreach ($order->orderTours as $order_tour)
                                        {{ \Carbon\Carbon::parse($order_tour->tour_date)->format('M d, Y') }}<br>

                                         {{ $order_tour->tour_time }}<br>
                                    @endforeach


                                </td>
                                <td style="max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">
                                    <a href="{{ route('admin.customers.show', encrypt($order->customer?->id)) }}" class="alink" target="_blank">
                                        {{ $order->customer?->name }}
                                    </a>

                                    
                                    <br>
                                    {{ str_contains($order->customer?->phone, '+') || ($order->customer?->phone == 'N/A') ? '' : '+' }}{{ $order->customer?->phone }}
                                    <br>
                                    {{ $order->customer?->email }}
                                </td>
                                <!-- @php

                                    $total = round($order->total_amount);
                                   // $paid = round($order->booked_amount) ?? 0; 

                                    $paid = round($order->payments->where('status', 'succeeded')->sum('amount') - $order->payments->where('status', 'refunded')->sum('amount') + $order->payments->where('status', 'partial_refunded')->sum('amount'));


                                    $hasUncaptured = $order->payments->contains('status', 'uncaptured');

                                    if ($paid < $total) {
                                        if($paid == 0 && $hasUncaptured){
                                            $amountClass = 'text-orange';
                                        } else{
                                            $amountClass = 'text-danger'; // red
                                        }
                                       
                                    } else {
                                        $amountClass = 'text-success'; // green
                                    }

                                    if ($order->order_status == 6) {
                                        $amountClass = 'text-secondary'; // grey
                                    } 
                                @endphp
                                <td >

                                    <span class="{{ $amountClass }}">{{ price_format_with_currency($order->total_amount, $order->currency) }}</span>
                                <!-- </td> -->

                                @php

                                    $total = $order->total_amount;



                                    $paid = 

                                        $order->payments->where('status', 'succeeded')->sum('amount')

                                        - $order->payments->where('status', 'refunded')->sum('amount')

                                        + $order->payments->where('status', 'partial_refunded')->sum('amount');



                                    $balance = max(0, $total - $paid);



                                    $hasUncaptured = $order->payments->contains('status', 'uncaptured');



                                    if ($paid < $total) {

                                        if ($paid == 0 && $hasUncaptured) {

                                            $amountClass = 'text-orange';

                                        } else {

                                            $amountClass = 'text-danger'; // red

                                        }

                                    } else {

                                        $amountClass = 'text-success'; // green

                                    }



                                    if ($order->order_status == 6) {

                                        $amountClass = 'text-secondary'; // grey

                                    }

                                @endphp



                                <td>





                                <span class="{{ $amountClass }}">

                                        @if($amountClass == 'text-danger')

                                            {{ price_format_with_currency($balance, $order->currency) }}

                                        @else

                                            {{ price_format_with_currency($order->total_amount, $order->currency) }}

                                        @endif

                                    </span>
                                <br>
                                <span>{{ $order->action_name ? $order->action_name == "book" ? "Pay Now" : "Pay Later" : "N/A" }}</span>
                                
                                    @php
                                        $payment = $order->payments()
                                            ->where('collection_type', 'Inside')
                                            ->latest()
                                            ->first();
                                        
                                        // fallback if not found
                                        if (!$payment) {
                                            $payment = $order->payments()->latest()->first();
                                        }
                                    @endphp

                                    @if($payment)
                                        <br>
                                        @if(strtoupper($payment->payment_type) === 'LINK')
                                            <span class="text-black"><svg class="SVGInline-svg SVGInline--cleaned-svg SVG-svg BrandIcon-svg BrandIcon--size--20-svg" height="20" width="20" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#00D66F" d="M0 0h32v32H0z"></path><path fill="#011E0F" d="M15.144 6H10c1 4.18 3.923 7.753 7.58 10C13.917 18.246 11 21.82 10 26h5.144c1.275-3.867 4.805-7.227 9.142-7.914v-4.18c-4.344-.68-7.874-4.04-9.142-7.906Z"></path></svg>    Link</span>

                                        @elseif(strtoupper($payment->payment_type) === 'KLARNA')    
                                            <span class="text-black"><svg class="SVGInline-svg SVGInline--cleaned-svg SVG-svg BrandIcon-svg BrandIcon--size--16-svg" height="16" width="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#FFA8CD" d="M0 0h32v32H0z"></path><path fill="#0B051D" d="M23.665 6h-4.342c0 3.571-2.185 6.771-5.506 9.057l-1.305.914V6H8v20h4.512v-9.914L19.975 26h5.506l-7.18-9.486c3.264-2.371 5.392-6.057 5.364-10.514Z"></path></svg>    Klarna</span>
                                        @elseif($payment->card_brand)
                                            {!! cardSvg($payment->card_brand) !!} 
                                        @elseif(!empty($payment->payment_type))  
                                            {!! $payment->payment_type !!}                                            
                                        @else
                                            <span class="text-muted">Info unavailable</span>
                                        @endif

                                    @else
                                    <br>
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $created = \Carbon\Carbon::parse($order->created_at);
                                    @endphp

                                    {{ $created->format('M d, Y') }} <br>
                                    {{ $created->format('h:i A') }}
                                    <!-- {{ optional($order->created_at)->format('M d, Y') }} <br>
                                    {{ optional($order->created_at)->format('h:i A') }} -->
                                </td>
                                <td style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100px;">
                                    {{ source_list($order->source) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="table-footer">

                    <!-- Pagination -->
                    <div>
                        {{ $orders->withQueryString()->links() }}
                    </div>

                    <!-- Total Orders -->
                    <div class="total-orders">
                        <span class="badge">Total Orders: {{ $totalOrders }}</span>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <div id="importOrderModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ translate('Import Order') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>

                <form method="POST" action="{{ route('admin.orders.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="p-3">
                        <p class="m-0">Upload a Excel file with columns</p>
                    </div>
                    <div class="border p-3 bg-light">
                        <label for="file">Select File</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".csv,.xlsx,.xls">
                    </div>
                    
                    <div class="modal-footer">
                        <div class="m-0">
                            <button type="submit" class="btn btn-ExpoImpo"> <i class="fas fa-file-import"></i>  {{ translate('Import') }}</button>
                            <button type="button" class="btn btn-success" id="downloadSample">
                                <i class="fas fa-file-excel"></i> Download Sample Excel
                            </button>
                        </div>
                        <div class="m-0">
                            <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('js')
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
        document.getElementById('checkAll').addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('input[name="ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
    <script>
        document.getElementById('downloadSample').addEventListener('click', function () {
            window.location.href = "{{ route('admin.orders.sample-excel') }}";
        });
    </script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery UI (for sortable) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- jQuery UI JS -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
    $(document).ready(function () {

        // ✅ Check all
        $('#checkAll').on('click', function () {
            $('input[name="ids[]"]').prop('checked', this.checked);
        });

        // ✅ Download sample
        $('#downloadSample').on('click', function () {
            window.location.href = "{{ route('admin.orders.sample-excel') }}";
        });

        // ✅ Select2 (optimized)
        // $('#productFilter').select2({
        //     placeholder: 'Select Tour',
        //     minimumInputLength: 4,
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
        //                     text: tour.title
        //                 }))
        //             };
        //         }
        //     }
        // });

        function initTourSelect(selector, isMultiple, placeholderText) {
            $(selector).select2({
                placeholder: placeholderText,
                minimumInputLength: 4,
                multiple: isMultiple,
                dropdownParent: $('#filterSidebar'),
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

// initTourSelect('#productFilter');


    });

@if($selectedProducts->count())

let option = new Option(
    "{{ $selectedProducts->first()->title }}",
    "{{ $selectedProducts->first()->id }}",
    true,
    true
);

$('#productFilter')
    .append(option)
    .trigger('change');

@endif
    </script>

@endsection
</x-admin>
