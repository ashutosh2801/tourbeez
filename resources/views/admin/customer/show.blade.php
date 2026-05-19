<x-admin>
    @section('title', 'Show Customer')
    <div class="card-primary">
        <div class="card-header cus-order-head">
            <div class="row">
                <div class="col-md-8 col-12">            
                    <h3 class="card-title">{{ $user->name }} ({{ $user->email }})</h3>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card-tools">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-back">Back</a>
                        <a href="{{ route('admin.customers.edit.source', ['id' => encrypt($user->id),'source' => 'order_customer']) }}" class="btn btn-sm btn-primary"><i class="far fa-edit"></i> Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.user.update',$user) }}" method="POST">
            @method('PUT')
            @csrf
            <input type="hidden" name="id" value="{{ $user->id }}">
            <div class="row">
                <!-- <div class="col-lg-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Name:*</label>
                        <input type="text" class="form-control" name="name" required
                            value="{{ $user->name }}">
                            <x-error>name</x-error>
                    </div>
                </div> -->
                <!-- <div class="col-lg-6">
                    <div class="form-group">
                        <label for="Email" class="form-label">Email:*</label>
                        <input type="email" class="form-control" name="email" required
                            value="{{ $user->email }}">
                            <x-error>email</x-error>
                    </div>
                </div> -->
                
                
                <div class="col-lg-12">
                    <div class="float-right">
                        <!-- <button class="btn btn-primary" type="submit">Save</button> -->
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if($orders)
    <div class="customer-order-table card rounded-lg-custom border">
        <div class="top-header">
            <h3 class="card-title">Customer Orders</h3>
            <h4 class="card-title">Total orders : {{count($orders)}}</h4>
        </div>
        <div class="card-body p-0 order-table table-responsive">
                <table class="table table-striped" id="OrderTable">
                    <thead>
                        <tr>
                            <!-- <th style="width:5%;">
                                <input type="checkbox" id="checkAll" style="width:20px; height:20px;">
                            </th> -->
                            <th style="width:10%;">Order Number</th>
                            <th style="width:10%;">Status</th>
                            <th style="width:20%;">Tour</th>
                            <th style="width:10%;">Tour Date</th>
                            <th style="width:10%;">Customer</th>
                            <th style="width:10%;">Amount</th>
                            <th style="width:10%;">Created</th>
                            <th style="width:5%;">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <!-- <td><input type="checkbox" name="ids[]" value="{{ $order->id }}" style="width:20px; height:20px;"></td> -->
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
                                    <span> X {{ $order->orderTours->sum('number_of_guests') }}</span>
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
                                    </a>
                                    <br>
                                    {{ $order->customer?->phone }}
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
                                <td class="{{ $amountClass }}">
                                    {{ price_format_with_currency($order->total_amount, $order->currency) }}
                                    <br>
                                    @php
                                        $payment = $order->payments->first();
                                    @endphp

                                    @if($payment)
                                        <br>
                                        
                                        @if(strtoupper($payment->payment_type) === 'LINK')

                                            <span class="text-primary"><svg class="SVGInline-svg SVGInline--cleaned-svg SVG-svg BrandIcon-svg BrandIcon--size--20-svg" height="20" width="20" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#00D66F" d="M0 0h32v32H0z"></path><path fill="#011E0F" d="M15.144 6H10c1 4.18 3.923 7.753 7.58 10C13.917 18.246 11 21.82 10 26h5.144c1.275-3.867 4.805-7.227 9.142-7.914v-4.18c-4.344-.68-7.874-4.04-9.142-7.906Z"></path></svg>    Link</span>

                                        @elseif($payment->card_brand)

                                            {!! cardSvg($payment->card_brand) !!}
                                            {{ ucfirst($payment->card_brand) }}

                                            

                                        @else

                                            <span class="text-muted">Card info unavailable</span>

                                        @endif

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
    </div>
    @endif

</x-admin>
