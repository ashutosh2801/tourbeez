<x-admin>
    @section('title', 'Orders List')

    <div class="card rounded-lg-custom border">
        @php
            $statuses = config('constants.status_with_code');
        @endphp

        {{-- Filter/Search Form --}}
        <form method="GET" action="{{ route('admin.orders.index') }}">
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
                            <button type="submit" class="btn btn-search"> <i class="fas fa-search"></i> Search</button>
                        </div>
                        <div class="col-md-12 col-12">
                            <a href="{{ route('admin.orders.index') }}" class="btn-clear" style="margin-top:10px;display: block;"> <i class="fas fa-times"></i> Clear Search</a>
                        </div>
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
                    <div class="card-tools">
                        <a type="button" class="btn btn-success" href="{{ route('admin.orders.create') }}">
                            <i class="fas fa-calendar-plus"></i> Create Internal Order
                        </a>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure to delete selected orders?')">
                            <i class="fas fa-trash-alt"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0 order-table table-responsive">
                <table class="table table-striped" id="OrderTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll" style="width:20px; height:20px;"></th>
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
                                <td><input type="checkbox" name="ids[]" value="{{ $order->id }}" style="width:20px; height:20px;"></td>
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
                <div class="card-footer">
                    {{ $orders->withQueryString()->links() }}
                </div>
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
