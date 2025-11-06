<style>
    .dropdown-menu li a span.text {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-right: 0;
        vertical-align: bottom;
        text-wrap: auto !important; 
    }
    .bootstrap-select .dropdown-menu.inner {
        max-height: 400px !important;  /* default is usually 300px */
        overflow-y: auto !important;
    }


</style>

<x-admin>
    @section('title', 'Orders List')

    <div class="card">
        @php
            $statuses = config('constants.status_with_code');
        @endphp

        {{-- Filter/Search Form --}}
        <form method="GET" action="{{ route('admin.orders.index') }}">
            <div class="card-header">
                <div class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Order # / Customer" value="{{ request('search') }}">
                    </div>
                    <!-- <div class="col-md-2">
                        <select name="product" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Tours</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>{{ $product->title }}</option>
                            @endforeach
                        </select>
                    </div> -->

                    <div class="col-md-3">
                        <select name="product" 
                           
                            class="form-control aiz-selectpicker" data-live-search="true">
                            <option value="">Select Tour</option>

                            @foreach ($products->sortBy('title') as $product)
                                <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>{{ $product->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Payment Status</option>
                            <option value="1" {{ request('payment_status') === '1' ? 'selected' : '' }}>Paid</option>
                            <option value="0" {{ request('payment_status') === '0' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="order_status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Order Status</option>
                             @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('order_status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                               
                            @endforeach
                            
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="tour_start_date" class="form-control form-control-sm" value="{{ request('tour_start_date') }}">
                    </div>
                    
                    <div class="col-md-2 mt-2">
                        <select name="date_filter" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Filter by Order Created</option>
                            <option value="last_7" {{ request('date_filter') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_15" {{ request('date_filter') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                            <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_90" {{ request('date_filter') == 'last_90' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="last_6_months" {{ request('date_filter') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="this_year" {{ request('date_filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-2">
                        <select name="tour_date_filter" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Tour Dates</option>
                            <option value="last_7" {{ request('tour_date_filter') == 'last_7' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_15" {{ request('tour_date_filter') == 'last_15' ? 'selected' : '' }}>Last 15 Days</option>
                            <option value="this_month" {{ request('tour_date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_90" {{ request('tour_date_filter') == 'last_90' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="last_6_months" {{ request('tour_date_filter') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="this_year" {{ request('tour_date_filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mt-2">
                        <button type="submit" class="btn btn-primary btn-sm p-2">Search</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm p-2">Clear</a>
                    </div>
                    
                </div>
               
            </div>
            <div class="ml-4">
                <div class="row g-2 align-items-start">
                    <!-- <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div> -->
                    <!-- <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div> -->
                </div>
            </div>
        </form>
        

        {{-- Bulk Delete --}}
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.order.bulkDelete') }}">
            @csrf
            @method('DELETE')
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h3 class="card-title mb-0">Orders</h3>

                    <div class="d-flex justify-content-end align-items-center">
                        <a type="button" class="btn btn-success btn-sm" href="{{ route('admin.orders.create') }}">
                            Create Internal order
                        </a>
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete selected orders?')">
                            Delete Selected
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped" id="OrderTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll" style="width:15px; height:15px;"></th>
                            <th style="white-space: nowrap;">Order Number</th>
                            <th style="white-space: nowrap;">Status</th>
                            <th style="white-space: nowrap;">Tour</th>
                            <th style="white-space: nowrap;">Tour Date</th>
                            <th style="white-space: nowrap;">Customer</th>
                            <th style="white-space: nowrap;">Amount</th>
                            <th style="white-space: nowrap;">Payment</th>
                            <th style="white-space: nowrap;">Created</th>
                            <th style="white-space: nowrap;">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $order->id }}" style="width:15px; height:15px;"></td>
                                <td>
                                    <a href="{{ route('admin.orders.edit', encrypt($order->id)) }}" class="alink">{{ $order->order_number }}</a>
                                </td>
                                <td>{!! order_status($order->order_status) !!}</td>
                                <td>
                                    @foreach ($order->orderTours as $order_tour)

                                            <a href="{{ route('admin.tour.edit', encrypt($order_tour->tour_id)) }}" class="alink" target="_blank" >
                                                {{ $order_tour->tour?->title }}
                                            </a>

                                            @if($order->sub_tour_id  && $order->subTour)
                                                <hr>
                                                <a href="{{ route('admin.tour.edit', encrypt($order->subTour->tour_id)) }}" class="alink text-small" target="_blank" style="font-size: small;">
                                                    {{ $order->subTour?->title }} 
                                                </a>
                                            @endif

                                        <br>
                                        
                                    @endforeach
                                </td>
                                <td>

                                    @foreach ($order->orderTours as $order_tour)
                                        {{ \Carbon\Carbon::parse($order_tour->tour_date)->format('M d, Y') }}<br>

                                         {{ $order_tour->tour_time }}
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('admin.customers.show', encrypt($order->customer?->id)) }}" class="alink" target="_blank">
                                        {{ $order->customer?->name }}
                                    </a><br>
                                    {{ $order->customer?->phone }}
                                </td>
                                <td>{{ $order->currency }} {{ price_format($order->total_amount) }}</td>
                                <td>
                                    @if($order->payment_method)
                                        {{ ucwords($order->payment_method) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ date__format($order->created_at) }}</td>
                                <td>{{ $order->source ?? 'Online' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $orders->withQueryString()->links() }}
            </div>
        </form>
    </div>

    @section('js')
        <script>
            document.getElementById('checkAll').addEventListener('click', function () {
                const checkboxes = document.querySelectorAll('input[name="ids[]"]');
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            });
        </script>
    @endsection
</x-admin>
