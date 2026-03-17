<x-admin>
    <style>
        .text-orange {
            color: #fd7e14;
        }
</style>
    @section('title', 'Orders List')

    <style>
        .filter-panel {
            display: none;
            animation: fadeSlide 0.3s ease-in-out;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

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

        <form method="GET" id="filterForm" action="{{ route('admin.orders.index') }}">
            <div class="filter-panel" id="filterPanel">
                <div class="card-header">
                    <div class="search-options">
                        <div class="row">
                            <div class="col-md-4 col-6">
                                <input type="text" name="search" class="form-control" placeholder="Order # / Customer" value="{{ request('search') }}">
                            </div>
                            <?php /*
                            <div class="col-md-2">
                                <select name="product" class="form-control form-control-sm" >
                                    <option value="">All Tours</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div> 
                            */ ?>
                            <div class="col-md-4 col-6">
                                <select name="product" class="form-control aiz-selectpicker" data-live-search="true">
                                    <option value="">Select Tour</option>
                                    @foreach ($products->sortBy('title') as $product)
                                        <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <select name="payment_status" class="form-control" >
                                    <option value="">Payment Status</option>
                                    <option value="1" {{ request('payment_status') === '1' ? 'selected' : '' }}>Paid</option>
                                    <option value="0" {{ request('payment_status') === '0' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <select name="order_status" class="form-control" >
                                    <option value="">Order Status</option>
                                    @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ request('order_status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <input type="date" name="tour_start_date" class="form-control" value="{{ request('tour_start_date') }}">
                            </div>
                            <div class="col-md-2 col-6">
                                <select name="date_filter" class="form-control" >
                                    <option value="">Filter by Order Created</option>
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
                                    <option value="last_7" {{ request('tour_date_filter') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="last_15" {{ request('tour_date_filter') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                                    <option value="this_month" {{ request('tour_date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_90" {{ request('tour_date_filter') == 'last_90' ? 'selected' : '' }}>Last 90 Days</option>
                                    <option value="last_6_months" {{ request('tour_date_filter') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                    <option value="this_year" {{ request('tour_date_filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                            <select name="per_page" class="form-control">
                                @foreach ([10, 25, 50, 100, 500] as $number)
                                    <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                        {{ $number }} per page
                                    </option>
                                @endforeach
                            </select>
                        </div>
                            
                            <div class="col-md-2 col-6">
                                <button type="submit" class="btn btn-search"> <i class="fas fa-search"></i> Search</button>
                            </div>
                            <div class="col-md-2 col-6">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-clear border" > <i class="fas fa-times"></i> Clear Search</a>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </form>
        

        {{-- Bulk Delete --}}
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.order.bulkDelete') }}">
            @csrf
            @method('DELETE')
            <div class="card-header btn-options">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="card-tools">
                        <a type="button" class="btn btn-success" href="{{ route('admin.orders.create') }}">
                            <i class="fas fa-calendar-plus"></i> Create Internal Order
                        </a>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure to delete selected orders?')">
                            <i class="fas fa-trash-alt"></i> Delete Selected
                        </button>
                        <button type="button" class="btn btn-ImpOrder" data-toggle="modal" data-target="#importOrderModal">
                            <i class="fas fa-file-import"></i> Import Order
                        </button>
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
                <table class="table table-striped" id="OrderTable" style="table-layout:fixed; width:100%;">
                    <thead>
                        <tr>
                            <th style="width:4%;">
                                <input type="checkbox" id="checkAll" style="width:20px; height:20px;">
                            </th>

                            <th style="width:8%; white-space: nowrap;">
                                Order <br> Number
                            </th>

                            <th style="width:10%; white-space: nowrap;">
                                Status
                            </th>

                            <th style="width:25%; white-space: nowrap;">
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
                                <td>{!! order_status($order->order_status) !!}</td>
                                <td>
                                    @foreach ($order->orderTours as $order_tour)

                                        <div style="display:flex; justify-content:space-start; align-items:center;">
                                            <a href="{{ route('admin.tour.edit', encrypt($order_tour->tour_id)) }}"
                                               class="alink"
                                               target="_blank"
                                               style="max-width:85%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">
                                                {{ $order_tour->tour?->title }}
                                            </a>
                                            @if($loop->iteration == 1)
                                            <span class="font-bold ml-1">
                                                X {{ $order->orderTours->sum('number_of_guests') }}
                                            </span>
                                            @endif
                                        </div>

                                        @if($order->sub_tour_id && $order->subTour)
                                            <div style="max-width:85%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                <a href="{{ route('admin.tour.edit', encrypt($order->subTour->tour_id)) }}"
                                                   class="alink text-small"
                                                   target="_blank"
                                                   style="font-size:small;">
                                                    {{ $order->subTour?->title }}
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
                                <td>
                                    <a href="{{ route('admin.customers.show', encrypt($order->customer?->id)) }}" class="alink" target="_blank">
                                        {{ $order->customer?->name }}
                                    </a>

                                    
                                    <br>
                                    {{ str_contains($order->customer?->phone, '+') || ($order->customer?->phone == 'N/A') ? '' : '+' }}{{ $order->customer?->phone }}
                                </td>
                                @php
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
                                <br>
                                <span>{{ $order->action_name ? $order->action_name == "book" ? "Pay Now" : "Pay Later" : "NA" }}</span>
                                
                                    @php
                                        $payment = $order->payments->first();
                                    @endphp

                                    @if($payment)
                                        <br>
                                        
                                        @if(strtoupper($payment->payment_type) === 'LINK')

                                            <span class="text-primary"><svg class="SVGInline-svg SVGInline--cleaned-svg SVG-svg BrandIcon-svg BrandIcon--size--20-svg" height="20" width="20" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#00D66F" d="M0 0h32v32H0z"></path><path fill="#011E0F" d="M15.144 6H10c1 4.18 3.923 7.753 7.58 10C13.917 18.246 11 21.82 10 26h5.144c1.275-3.867 4.805-7.227 9.142-7.914v-4.18c-4.344-.68-7.874-4.04-9.142-7.906Z"></path></svg>    Link</span>

                                        @elseif($payment->card_brand)

                                            {!! cardSvg($payment->card_brand) !!}
                                            <!-- {{ ucfirst($payment->card_brand) }} -->

                                            

                                        @else

                                            <span class="text-muted">Card info unavailable</span>

                                        @endif

                                    @else
                                    <br>
                                        N/A
                                    @endif
                                    <!-- </td> -->
                                <td>{{ date__format($order->created_at) }}</td>
                                <td style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100px;">
                                {{ $order->source }}
                                

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="card-footer">
                    {{ $orders->withQueryString()->links() }}
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
                <div class="modal-body">
                    
                    <p>Upload a Excel file with columns</p>

                    <div class="form-group">
                        <label for="file">Select File</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".csv,.xlsx,.xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="downloadSample">
                        <i class="fas fa-file-excel"></i> Download Sample Excel
                    </button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ translate('Import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('js')
    <script>
        let filterOpen = false;

        $('#toggleFilter').on('click', function () {
            $('#filterPanel').slideToggle(250);

            filterOpen = !filterOpen;

            if (filterOpen) {
                $(this)
                    .removeClass('btn-secondary')
                    .addClass('btn-danger')
                    .html('<i class="fas fa-times"></i> Hide Filters');
            } else {
                $(this)
                    .removeClass('btn-danger')
                    .addClass('btn-secondary')
                    .html('<i class="fas fa-filter"></i> Filters');
            }
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
@endsection
</x-admin>
