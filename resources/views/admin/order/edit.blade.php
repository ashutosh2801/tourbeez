<x-admin>
@section('title', 'Order '.$order->order_number)

@section('css')
<style>
    .accordion .fa{
        margin-right: 0.5rem;
        font-size: 24px;
        font-weight: bold;
        position: relative;
        top: 2px;
    }
    ul.flex {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    ul.flex li {
        margin-right:15px;
    }
    ul.flex li:after {
        margin-left:15px;
        content: "|";
        color: #bbb;
    }
    ul.flex li:last-child:after {
        content: "";
    }
    .highlight {
        animation: fadeHighlight 2s ease;
    }

    @keyframes fadeHighlight {
        0%   { background-color: #e1a10b; }
        100% { background-color: transparent; }
    }
    /* Improve button group and dropdown alignment */
    .btngroup .btn-group .btn.dropdown-toggle {
        padding: 0.5rem 2rem;
        font-weight: 600;
        background-color: #f8f9fa;
        border-bottom: 2px solid #ced4da;
        border-radius: 0.25rem;
        font-size: 21px;
    }

    /* Payment dropdown styling */
    .dropdown-menu.dropdown-value.payment-details-breakdown--container {
        min-width: 250px;
        padding: 0.75rem;
        border-radius: 0.25rem;
        background-color: #ffffff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .payment-details-breakdown--item {
        display: flex;
        justify-content: space-between;
        padding: 0.25rem 0;
        font-size: 0.95rem;
    }

    .payment-details-breakdown--text {
        color: #333;
    }

    /* Order status dropdown */
    .dropdown-menu.dropdown-value {
        min-width: 220px;
        padding: 0.5rem;
        border-radius: 0.25rem;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .dropdown-menu.dropdown-value li {
        display: flex;
        align-items: center;
        padding: 0.4rem 0.5rem;
        list-style: none;
    }
    .dropdown-menu.dropdown-value input[type="radio"] {
        margin-right: 10px;
    }
    .dropdown-menu.dropdown-value label {
        margin: 0;
        font-weight: 500;
        display: flex;
        align-items: center;
        cursor: pointer;
        width: 100%;
    }

    .dropdown-menu.dropdown-value label:hover {
        background-color: #f1f1f1;
        border-radius: 4px;
    }

    .dropdown-menu.dropdown-value i.fa-circle {
        font-size: 10px;
        margin-right: 6px;
        color: #6c757d;
    }
    .dropdown-menu input[type=radio] {
        display: none
    }
    .order-status, .order-balance {
        display: inline-block;
    }
    .order-status label, .order-status .btn-group, .order-balance label, .order-balance .btn-group {
        display: block; text-align: center
    }
    .order-status label, .order-balance label {
        margin: 0
    }
    /* Balance dropdown always green */
    .payment-status .btn.dropdown-toggle {
    border-color: #28a745 !important;
    color: #28a745 !important;
    }
    .payment-status .btn.dropdown-toggle:hover {
    background-color: rgba(40,167,69,0.1);
    }


    /* Due (red) */
    .payment-status.due .btn.dropdown-toggle {
        border-color: #dc3545 !important;
        color: #dc3545 !important;
    }
    .payment-status.due .btn.dropdown-toggle:hover {
        background-color: rgba(220,53,69,0.1);
    }

    /* Order‐status color map */
    .status-NEW           { --status-color: #6c757d; } /* gray */
    .status-ON_HOLD       { --status-color: #ffc107; } /* yellow */
    .status-PENDING_SUPPLIER { --status-color: #6610f2; } /* purple */
    .status-PENDING_CUSTOMER { --status-color: #20c997; } /* teal */
    .status-CONFIRMED     { --status-color: #28a745; } /* green */
    .status-CANCELLED     { --status-color: #dc3545; } /* red */
    .status-ABANDONED_CART{ --status-color: #343a40; } /* dark */

    /* Apply the variable to the button */
    .order-status .btn.dropdown-toggle {
    border-width: 2px;
    border-style: solid;
    border-color: var(--status-color);
    color: var(--status-color);
    background-color: #fff;
    }
    .order-status .btn.dropdown-toggle:hover {
    background-color: rgba(0,0,0,0.03);
    }
</style>
@endsection

@php
    $statuses = config('constants.order_statuses');

    $expectEmails = ['order_pending', 'payment_request', 'payment_receipt'];
    
@endphp
    <form action="{{ route('admin.orders.update',$order->id) }}" method="POST">
    @method('PUT')
    @csrf
    <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}" /> 
    <div class="card card-primary rounded-lg-custom border order-edit-head">
        <div class="card-header">
            <div class="row">
                <div class="col-md-9">
                    <h5 class="m-0">Created on {{ date__format($order->created_at) }} online on your booking form</h5>
                </div>
                <div class="col-md-3">
                    <button class="btn charge-btn" data-order-id="{{ $order->id }}" data-customer-name="{{ $order->customer?->name }}" data-balance="{{ $order->balance_amount }}" type="button">
                        Charge now
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body order-edit">
            <div>
                <div class="row">
                    <div class="info-blog">
                        <div class="info-stats4">
                            <div class="info-icon flex-shrink-0">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="sale-num">
                                <p>Balance</p>
                                <button type="button" class="btn btn-balance dropdown-toggle arrow" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    @if($order->payment_status ==3)

                                        <strong id="totalDue" class="total-due">{{ price_format_with_currency($order->balance_amount + $order->booked_amount, $order->currency) }}</strong>
                                    @else

                                        <strong id="totalDue" class="total-due">{{ price_format_with_currency($order->balance_amount, $order->currency) }}</strong>
                                    @endif

                                    
                                </button>
                                <ul class="dropdown-menu dropdown-value payment-details-breakdown--container">

                                    @if($order->payment_status ==3)
                                        <li class="payment-details-breakdown--item">
                                            <strong class="payment-details-breakdown--text">Uncaptured</strong>
                                            <strong class="payment-details-breakdown--text">{{ price_format_with_currency($order->booked_amount, $order->currency) }}</strong>
                                        </li>
                                    @else
                                        <li class="payment-details-breakdown--item">
                                            <strong class="payment-details-breakdown--text">Paid</strong>
                                            <strong class="payment-details-breakdown--text">{{ price_format_with_currency($order->booked_amount, $order->currency) }}</strong>
                                        </li>

                                    @endif
                                    <li class="payment-details-breakdown--item">
                                        <strong class="payment-details-breakdown--text">Total</strong>
                                        <strong class="payment-details-breakdown--text">{{ price_format_with_currency($order->total_amount, $order->currency) }}</strong>
                                    </li>
                                    <li class="payment-details-breakdown--item">
                                        <strong class="payment-details-breakdown--text">Refunded</strong>
                                        <strong class="payment-details-breakdown--text">{{ price_format_with_currency(0, $order->currency) }}</strong>
                                    </li>
                                    @if($order->payment_status == 3)
                                        <li class="payment-details-breakdown--item">
                                        <strong class="payment-details-breakdown--text">Balance</strong>
                                            <strong class="payment-details-breakdown--text due">{{ price_format_with_currency($order->balance_amount + $order->booked_amount, $order->currency) }}</strong>
                                        </li>

                                    @else
                                        <li class="payment-details-breakdown--item">
                                        <strong class="payment-details-breakdown--text">Balance</strong>
                                            <strong class="payment-details-breakdown--text due">{{ price_format_with_currency($order->balance_amount, $order->currency) }}</strong>
                                        </li>

                                    @endif
                                    

                                    <!-- Divider -->
                                    <li role="separator" class="divider"></li>

                                    <!-- Action Button -->
                                    
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="info-blog">
                        <div class="info-stats4">
                            <div class="info-icon flex-shrink-0">
                                <i class="fas fa-stream"></i>
                            </div>
                            <div class="sale-num">
                                <p>Order Status</p>
                                <button type="button" class="btn btn-status dropdown-toggle arrow childOrderEnabled"
                                    data-element-to-update=".payment-status"
                                    data-selected="{{ $order->status }}"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                    {{ $statuses[$order->status] ?? 'Unknown' }}
                                </button>
                                <ul class="dropdown-menu dropdown-value">
                                    @foreach ($statuses as $key => $label)
                                        <li>
                                            <input type="radio"
                                                id="{{ $key }}"
                                                name="order_status"
                                                value="{{ $key }}"
                                                autocomplete="off"
                                                {{ $order->status === $key ? 'checked' : '' }}>
                                            <label for="{{ $key }}" class="{{ $key }}">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                {{ $label }}
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="info-blog">
                        <div class="info-stats4">
                            <div class="info-icon flex-shrink-0">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <div class="sale-num">
                                <p>Email</p>
                                <select class="form-control form-option" name="email_template_name" id="email_template_name">
                                    <option value="" >Select</option>

                                    

                                    @foreach($email_templates as $email_template)

                                        @if(in_array($email_template->identifier, $expectEmails))
                                            @continue
                                        @endif


                                        <option value="{{$email_template->id}}" >{{snakeToWords($email_template->identifier)}} -> Send Now</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="info-blog">
                        <div class="info-stats4">
                            <div class="info-icon flex-shrink-0">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="sale-num">
                                <p>SMS</p>
                                <select class="form-control form-option" name="sms_template_name" id="sms_template_name">
                                    <option value="" >Select</option>

                                    @foreach($sms_templates as $sms_template)
                                

                                    <option value="{{$sms_template->id}}" >{{snakeToWords($sms_template->identifier)}} -> Send Now</option>

                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="info-blog">
                        <div class="info-stats4">
                            <div class="info-icon flex-shrink-0">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="sale-num">
                                <p>Print</p>
                                <select class="form-control form-option" name="print_template_name" id="print_template_name">
                                    <option value="" >Select</option>
                                    <option value="Order Details" >Order Details -> Send Now</option>
                                    <option value="Order Cancellation" >Order Cancellation -> Send Now</option>
                                    <option value="Payment Receipt" >Payment Receipt -> Send Now</option>
                                    <option value="Reminder 1st" >Reminder 1st -> Send Now</option>
                                    <option value="Reminder 2nd" >Reminder 2nd -> Send Now</option>
                                    <option value="Reminder 3rd" >Reminder 3rd -> Send Now</option>
                                    <option value="FollowUp Review" >FollowUp Review -> Send Now</option>
                                    <option value="FollowUp Recommend" >FollowUp Recommend -> Send Now</option>
                                    <option value="FollowUp Coupon" >FollowUp Coupon -> Send Now</option>
                                    <option value="Simple Email" >Simple Email -> Send Now</option>
                                </select>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
            
            <div class="bs-example">
                <div class="accordion" id="accordionExample">
                    <div class="card">
                        <div class="card-header bg-secondary py-0" id="headingOne">
                            <h2 class="my-0 py-0">
                                <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0 px-0" data-toggle="collapse" data-target="#collapseOne"><i class="fa fa-angle-right"></i>Customer Details</button>                                  
                            </h2>
                        </div>
                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">

                                
                            
                               
                                    <ul class="flex flex-row">


                                        <li><a href="{{ route('admin.customers.show', encrypt($order->customer?->id) ) }}" class="alink" target="_blank">{{ $order->customer?->name }}</a></li>
                                        <li>{{ $order->customer?->email }}</li>
                                        <li>+{{ $order->customer?->phone }}</li>
                                    </ul>
                                
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-secondary py-0" id="headingTwo">
                            <h2 class="my-0 py-0">
                                <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" data-toggle="collapse" data-target="#collapseTwo"><i class="fa fa-angle-down"></i> Tour Details</button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body">                               
                                
                                <div id="tour_all">
                                    @php $count = count( $order->orderTours ); $index=0; @endphp
                                    @foreach ($order->orderTours as $order_tour)
                                    @php
                                        $row_id = 'row_'.$index++;
                                        $subtotal = 0;
                                        $_tourId = $order_tour->tour_id;
                                    @endphp
                                    <div id="{{ $row_id }}" style="border:1px solid #e1a604; margin-bottom:10px">
                                    <input type="hidden" name="tour_id[]" value="{{ $order_tour->tour_id }}" />    
                                    <table class="table">
                                        <tr>
                                            <td width="600"><h3 class="text-lg">{{ $order_tour->tour?->title }}</h3></td>
                                            <td class="text-right" width="200">
                                                <div class="input-group">
                                                    <input type="text" class="aiz-date-range form-control" id="tour_startdate" name="tour_startdate[]" placeholder="Select Date" data-single="true" data-show-dropdown="true" value="{{ $order_tour->tour_date }}">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-right" width="200">
                                                <div class="input-group">


                                                    <input type="text" placeholder="Time" name="tour_starttime[]" id="tour_starttime" value="{{ $order_tour->tour_time }}" class="form-control aiz-time-picker" data-minute-step="1"> 
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    </div>                       
                                                </div>
                                            </td>
                                            {{-- <td class="text-right" width="200">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>                       
                                                    <input type="text" placeholder="99.99" name="tour_price[]" id="tour_price" value="{{ $order_tour->total_amount }}" class="form-control"> 
                                                </div>
                                            </td> --}}
                                            <td class="text-right">
                                                <button type="button" onClick="removeTour('{{ $row_id }}')" class="btn btn-sm btn-danger">-</button>
                                                <button type="button" onClick="addTour()" class="btn btn-sm btn-info">+</button>
                                            </td>
                                        </tr>
                                    </table>

                                    <table class="table" style="background:#ebebeb">
                                        <tr>
                                            <td style="width:200px">
                                                <table class="table">
                                                    <tr>
                                                        <td colspan="2">
                                                            <h4 style="font-size:16px; font-weight:600">Quantities</h4>
                                                        </td>
                                                    </tr>
                                                    @if ($order_tour->tour)
                                                    @php
                                                        $tour_pricing = !empty($order_tour->tour_pricing) ? ( json_decode($order_tour->tour_pricing) ) : [];
                                                    @endphp
                                                    @foreach($order_tour->tour?->pricings as $pricing)
                                                    @php
                                                        $price = $pricing->price;
                                                        $result = getTourPricingDetails($tour_pricing, $pricing->id);
                                                        if(isset($result['price'])) {
                                                            $price = $result['price'];
                                                            $subtotal = $subtotal + ($result['quantity'] * $price);
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td width="60">
                                                            <input type="hidden" name="tour_pricing_id_{{$_tourId}}[]" value="{{ $pricing->id }}" />  
                                                            <input type="number" name="tour_pricing_qty_{{$_tourId}}[]" value="{{ $result['quantity'] ?? 0 }}" style="width:60px" min="0" class="form-contorl text-center">
                                                            <input type="hidden" name="tour_pricing_price_{{$_tourId}}[]" value="{{ $price }}" />  
                                                        </td>
                                                        <td>{{ $pricing->label }} ({{ price_format_with_currency($price, $order->currency, $order->currency) }})</td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                                </table>
                                            </td>
                                            <td style="width:200px">
                                                <table class="table">
                                                    <tr>
                                                        <td colspan="2">
                                                            <h4 style="font-size:16px; font-weight:600">Optional extras</h4>
                                                        </td>
                                                    </tr>
                                                    @if ($order_tour->tour)
                                                    @php
                                                        $tour_extra = !empty($order_tour->tour_extra) ? ( json_decode($order_tour->tour_extra) ) : [];
                                                    @endphp
                                                    @foreach($order_tour->tour?->addons as $extra)
                                                    @php
                                                        $price = $extra->price;
                                                        $result = getTourExtraDetails($tour_extra, $extra->id);
                                                        if(isset($result['price'])) {
                                                            $price = $result['price'];
                                                            $subtotal = $subtotal + ($result['quantity'] * $price);
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td width="60">
                                                            <input type="hidden" name="tour_extra_id_{{$_tourId}}[]" value="{{ $extra->id }}" />  
                                                            <input type="number" name="tour_extra_qty_{{$_tourId}}[]" value="{{ $result['quantity'] ?? 0 }}" style="width:60px" min="0" class="form-contorl text-center">
                                                            <input type="hidden" name="tour_extra_price_{{$_tourId}}[]" value="{{ $price }}" /> 
                                                        </td>
                                                        <td>{{ $extra->name }} ({{ price_format_with_currency($extra->price, $order->currency) }})</td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <table class="table">
                                        @php
                                        $i=1;
                                        $taxesfees = $order_tour->tour->taxes_fees;
                                        @endphp 

                                        @if( $taxesfees )
                                        @foreach ($taxesfees as $key => $item)  
                                        @php
                                        $price      = get_tax($subtotal, $item->fee_type, $item->tax_fee_value);
                                        $tax        = $price ?? 0;
                                        $subtotal   = $subtotal + $tax; 
                                        @endphp 
                                        <tr>
                                            <td>{{ $item->label }} ({{ taxes_format($item->fee_type, $item->tax_fee_value) }})</td>
                                            <td class="text-right">{{ price_format_with_currency($tax, $order->currency) }}</td>
                                        </tr>
                                        @endforeach
                                        @endif

                                        <tr>
                                            <th>Subtotal </th>
                                            <th class="text-right">  {{ price_format_with_currency($subtotal, $order->currency) }} </th>
                                        </tr>
                                    </table>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <div id="tourContainer"></div>

                                <div style="border:1px solid #e1a604; margin-bottom:10px">
                                    <table class="table">
                                        @if ($order->bookingFee->value('value'))
                                            <tr>
                                                <td><b>Booking fee</b> (included in price)</td>
                                                <td class="text-right">{{ price_format_with_currency($order->bookingFee->value('value'), $order->currency) }}</td>
                                            </tr>
                                        @endif
                                        {{-- <tr>
                                            <td><b>Booking fee</b> (included in price)</td>
                                            <td class="text-right">{{ $order->bookingFee ? price_format_with_currency($order->bookingFee->value('value'), $order->currency) : "NA" }} </td>
                                        </tr> --}}
                                        <tr>
                                            <td><b>Total</b></td>
                                            <td class="text-right">{{ price_format_with_currency($order->total_amount, $order->currency) }} {{ $order->currency}}</td>
                                        </tr>
                                        <tr style="color: red">
                                            <td><b>Balance</b></td>
                                            <td class="text-right"><b>{{ price_format_with_currency($order->balance_amount, $order->currency) }} {{ $order->currency}}</b></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-secondary py-0" id="heading4">
                            <h2 class="my-0 py-0">
                                <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0 px-0" data-toggle="collapse" data-target="#collapse4"><i class="fa fa-angle-right"></i>Additional information</button>


                            </h2>
                        </div>
                        <div id="collapse4" class="collapse show" aria-labelledby="heading4" data-parent="#accordionExample">
                            <div class="card-body">
                               


                                 <div style="border:1px solid #e1a604; margin-bottom:10px">
                                    <table class="table">
                                        <tr>
                                            <td><b>Tour Guest</b> </td>
                                            <td class="text-right">{{ $order->order_tour->number_of_guests }} </td>
                                        </tr>
                                        
                                        <tr>
                                            <td><b>Category</b></td>
                                            <td class="text-right">{{ $order->tour['catogory'] ?? '-' }}</td>
                                        </tr>

                                        
                                        <tr>
                                            <td><b>Tour Types</b></td>
                                            <td class="text-right">{{ $order->tour->category->name ?? '-' }}</td>
                                        </tr>
                                        
                                        <tr>
                                            <td><b>Price Type</b></td>
                                            <td class="text-right">{{ snakeToWords($order->tour->price_type)  ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Country</b></td>
                                            <td class="text-right">{{ $order->tour->location->country->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>State</b></td>
                                            <td class="text-right">{{ $order->tour->location->state->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>City</b></td>
                                            <td class="text-right">{{  $order->tour->location->city->name ?? '-' }}</td>
                                        </tr>

                                        @php
                                            $pickName = '';
                                            if($order->customer && $order->customer->pickup_name){
                                                $pickName = $order->customer->pickup_name;
                                            } elseif($order->customer && $order->customer->pickup_id) {
                                                $pickLocation = \App\Models\PickupLocation::find($order->customer->pickup_id);
                                                $pickName = $pickLocation->location . " - " . $pickLocation->address . " - " . $pickLocation->time;
                                            }
                                        @endphp
                                        <tr>
                                            <td><b>Pickup Location</b></td>
                                            <td class="text-right">{{ $pickName }}</td> 
                                            

                                        </tr>
                                         
                                        @foreach ($order->tour->pickups as $pickup)
                                           
                                            <tr>
                                                <td><b>Pickup Charge</b></td>
                                                <td class="text-right">{{ $pickup->pickup_charge }}</td>
                                            </tr> 
                                        @endforeach
                                        
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-secondary py-0" id="headingThree">
                            <h2 class="my-0 py-0">
                                <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" data-toggle="collapse" data-target="#collapseThree"><i class="fa fa-angle-right"></i> Payment Details</button>                     
                            </h2>
                        </div>

                        <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordionExample">
                            <div class="card-body">
                                <div class="card text-success" ><p>This customer choose to pay {{ ($order->adv_deposite =='full') ? ucwords($order->adv_deposite) : "Partial" }} amount ({{ price_format_with_currency($order->booked_amount, $order->currency) }})</p></div>
                                
                                <table class="table">    
                                    <tr>
                                        <td>Payment Type</td>
                                        <td>Ref number</td>
                                        
                                        <td>Total</td>
                                        <td></td>
                                        <td>Balance</td>
                                        <td>Paid</td>
                                        <!-- <td>Refund</td> -->



                                    </tr>
                                    <tr>
                                    <td>{{ ucwords($order->payment_method)}}</td>
                                    <td>{{ ucwords($order->payment_intent_id)}}</td>
                                    
                                    <td>{{ price_format_with_currency($order->total_amount, $order->currency) }}</td>
                                        <td></td>
                                        <td>{{ price_format_with_currency($order->balance_amount) }}</td>
                                        <td>{{ price_format_with_currency($order->booked_amount, $order->currency) }}</td>
                                    <!-- <td>
                                        <button class="btn btn-sm btn-danger refund-btn" 
                                          style="width:150px; display:inline-block;" 
                                          data-order-id="{{ $order->id }}" 
                                          data-amount="{{ $order->booked_amount }}" 
                                          type="button">
                                          Refund
                                        </button>
                                    </td> -->
                                    </tr>

                                </table>

                                <h5 class="mt-4">💳 Payment Details</h5>

                                    @if($order->payments->isNotEmpty())
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Amount</th>
                                                    <th>Card</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order->payments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->id }}</td>
                                                        <td>{{ price_format_with_currency($payment->amount, $payment->currency) }}</td>
                                                        <td>
                                                            {{ strtoupper($payment->card_brand) ?? 'N/A' }} 
                                                            •••• {{ $payment->card_last4 ?? '----' }}  
                                                            <br>
                                                            <small>Exp: {{ $payment->card_exp_month }}/{{ $payment->card_exp_year }}</small>
                                                        </td>
                                                        <td>
                                                            @if($payment->status === 'succeeded')
                                                                <span class="badge bg-success">Succeeded</span>
                                                            @elseif($payment->status === 'pending')
                                                                <span class="badge bg-warning text-dark">Pending</span>
                                                            @elseif($payment->status === 'failed')
                                                                <span class="badge bg-danger">Failed</span>
                                                            @elseif($payment->status === 'refunded')
                                                                <span class="badge bg-secondary">Refunded</span>
                                                            @elseif($payment->status === 'partial_refunded')
                                                                <span class="badge bg-secondary">Partial Refunded </span> <span>{{(price_format_with_currency( $payment->refund_amount))}}</span>  
                                                            @endif
                                                        </td>
                                                        <td>
                                                            
                                                            @if($payment->status === 'succeeded' || $payment->status === 'partial_refunded')
                                                                <button 
                                                                    type="button"  
                                                                    class="btn btn-danger btn-sm open-refund-modal" 
                                                                    data-id="{{ $payment->id }}" 
                                                                    data-amount="{{ $payment->amount }}"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#refundModal">
                                                                    <i class="fa fa-undo"></i> Refund
                                                                </button>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $payment->created_at->format('d M Y h:i A') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted">No payments have been recorded yet.</p>
                                    @endif



                            </div>
                        </div>
                        <div class="text-left mt-3">
                            <button id="addPaymentBtn" type="button" class="btn btn-primary">
                                + Add Payment
                            </button>
                        </div>
                        <!-- Hidden Add Payment Form -->
                        <div id="addPaymentBlock" class="mt-3" style="display:none;">
                            <div id="addPaymentSection">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" id="addPaymentAmount" class="form-control" min="1" placeholder="Enter amount">
                                </div>

                                <div id="cardFields">
                                    <div class="form-group">
                                        <label for="card-element">Card Details</label>
                                        <div id="card-element" class="form-control" style="padding:10px; height:auto;"></div>
                                        <small id="card-errors" class="text-danger mt-2"></small>
                                    </div>
                                </div>

                                <button id="addPaymentSubmit" class="btn btn-success">Pay Now</button>
                            </div>
                        </div>
                    </div>




</div>

                    <div class="card">
                        <div class="card-header bg-secondary py-0" id="headingThree">
                            <h2 class="my-0 py-0">
                                <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" data-toggle="collapse" data-target="#collapseThree"><i class="fa fa-angle-right"></i> Order Email History</button>                     
                            </h2>
                        </div>
                        <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordionExample">
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>To</th>
                                            <th>From</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <!-- <th>Content</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($order->emailHistories) && is_iterable($order->emailHistories))
                                            @foreach($order->emailHistories as $email)
                                                <tr>
                                                    <td>{{ $email->created_at }}</td>
                                                    <td>{{ $email->to_email }}</td>
                                                    <td>{{ $email->from_email }}</td>
                                                    <td>{{ $email->subject }}</td>
                                                    <td>{{ ucwords($email->status) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5">No email history found</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer" style="display:block">
                        <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save order</button>
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <a onclick="return confirm('Are you sure?')" style="padding:0.6rem 2rem" href="{{ route('admin.tour.destroy', encrypt($order->id)) }}" class="btn btn-outline-danger">Delete</a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    </form>


@section('modal')
<!-- Order Template Modal -->
<div class="modal fade" id="order_template_modal" tabindex="1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="order_mail" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="identifier" id="identifier">
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card" style="margin: 0; padding: 0">
                                <div class="card-header"  style="margin: 0; padding: 0">
                                    <h2 class="card-title" style="font-size: 21px; font-weight:600">{{translate('Order Details Templates')}}</h2>
                                </div>
                                <div class="card-body"  style="margin: 0; padding: 0">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="tab-content" id="v-pills-tabContent">
                                                    <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">To</label>
                                                        <div class="col-md-12">
                                                            <input type="text" name="email"  id="email"  class="form-control" placeholder="{{translate('TO')}}" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">{{translate('CC Mail')}}</label>
                                                        <div class="col-md-12">
                                                            <input type="text" name="cc_mail" id="cc_mail" class="form-control" placeholder="{{translate('CC Mail')}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">{{translate('BCC Mail')}}</label>
                                                        <div class="col-md-12">
                                                            <input type="text" name="bcc_mail" id="bcc_mail" class="form-control" placeholder="{{translate('BCC Mail')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">{{translate('Subject')}}</label>
                                                        <div class="col-md-12">
                                                            <input type="text" name="subject" id="subject"  class="form-control" placeholder="{{translate('Subject')}}" required>
                                                            @error('subject')
                                                                <small class="form-text text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="event" id="event"  class="form-control" placeholder="{{translate('event')}}" required>

                                                    {{-- <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">{{translate('Email Header')}}</label>
                                                        <div class="col-md-12">
                                                            <textarea name="header" id="header" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="300"></textarea>
                                                            @error('header')
                                                                <small class="form-text text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </div>
                                                    </div> --}}

                                                    <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">{{translate('Email Body')}}</label>
                                                        <div class="col-md-12">
                                                            <textarea name="body" id="body" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="500"></textarea>
                                                            @error('body')
                                                                <small class="form-text text-danger">{{ $message }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">{{translate('Email Footer')}}</label>
                                                        <div class="col-md-12">
                                                            <textarea name="footer" id="footer" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="300"></textarea>
                                                        </div>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Send Mail</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
            </form>
        </div>
    </div>
</div>

<!-- Order Confirmation Template -->
<div class="modal fade" id="order_confirmation_sms" tabindex="2" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="order_sms_send" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="identifier" id="identifier">

            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card" style="margin: 0; padding: 0">
                            <div class="card-header" style="margin: 0; padding: 0">
                                <h2 class="card-title" style="font-size: 21px; font-weight:600">{{translate('Order Confirmation SMS')}}</h2>
                            </div>
                            <div class="card-body" style="margin: 0; padding: 0">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="tab-content" id="v-pills-tabContent">
                                        
                                            <div class="form-group row">
                                                <label class="col-md-12 col-form-label">{{ translate('Mobile Number') }}</label>
                                                <div class="col-md-12">
                                                    <input type="text" name="mobile_number"  id="mobile_number"  class="form-control" placeholder="{{translate('mobile') }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-12 col-form-label">{{translate('Message')}}</label>
                                                <div class="col-md-12">
                                                    <textarea name="message" id="message" class="form-control aiz-text-editor" placeholder="Type.." data-min-height="500"></textarea>
                                                
                                                </div>
                                            </div>
                                        </div>                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Send Sms</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
       </form>
    </div>
  </div>
</div>



<div class="modal fade" id="chargeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Carge Payment</h5>
      </div>
      <div class="modal-body">

        
        <div class="mb-3">
          <label>Customer Name:  <span id="customerName"></span> </label>
          
        </div>

        @if($order->payment_status == 3)


        <div class="mb-3">
          <label class="text-danger">Please confirm the order before charging the amount </label>
          <!-- <input type="text" id="chargeAmount" value="{{ $order->balance_amount }}" class="form-control"  name="amount" required> -->
        </div>
        @else
        <form id="chargeForm">
          <input type="hidden" id="chargeOrderId" name="order_id">
        <!-- Amount field -->


        <div class="mb-3">
          <label>Amount (current order balance: {{ price_format_with_currency($order->balance_amount, $order->currency) }}) </label>
          <input type="text" id="chargeAmount" value="{{ $order->balance_amount }}" class="form-control"  name="amount" required>
        </div>

        <!-- Card details block (will be shown/hidden) -->
        <div class="mb-3" id="cardDetailsBlock" style="display:none;"></div>
        </form>
        @endif
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-primary" id="confirmCharge">Confirm Charge</button> -->
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>

        @if($order->payment_status == 3)
            <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
        @else
        <button type="submit" form="chargeForm" class="btn btn-primary">Charge</button>
        @endif
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="refundModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Refund Payment</h5>
      </div>
      <div class="modal-body">
        <form id="refundForm">
          <input type="hidden" id="refundPaymentId" name="payment_id">

          <div class="mb-3">
            <label>Refund Amount</label>
            <input type="number" id="refundAmount" name="amount" class="form-control" min="0" step="0.01" required>
          </div>

          <div class="mb-3">
            <label>Reason (optional)</label>
            <input type="text" id="refundReason" name="reason" class="form-control">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="refundForm" class="btn btn-danger">Confirm Refund</button>
      </div>
    </div>
  </div>
</div>




@endsection

@section('js') 
@parent() 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let tourCount = {{ $count ? $count : 1 }}
    function tourOptions() {
        let options = '';
        @foreach ($tours as $tour)
            options += `<option value="{{ $tour->id }}">{{ $tour->title }}</option>`;
        @endforeach
        return options;
    }

    function addTour() {
        const container = document.getElementById('tourContainer');

        const newRow = document.createElement('div');
        //newRow.classList.add('row', 'align-items-end', 'mb-2');
        newRow.setAttribute('id', `row_${tourCount}`);

        newRow.innerHTML = `<div style="border:1px solid #ccc; margin-bottom:10px">
        <table class="table">
            <tr>
                <td>
                    <select onchange="loadOrderTour(this.value)" name="load_order_tour" 
                        id="load_order_tour" class="form-control aiz-selectpicker" 
                        data-live-search="true" style="max-width: 500px">
                        <option value="">Select Tour</option>` 
                        + tourOptions() + 
                    `</select>
                </td>
            </tr>
        </table></div>`;
        container.replaceChildren(newRow);
        TB.plugins.bootstrapSelect('refresh'); 

        if (container) {
            container.scrollIntoView({ top:15, behavior: "smooth" });
            container.classList.add('highlight');
            setTimeout(() => {
                container.classList.remove('highlight');
            }, 5000);
        }
        tourCount++;
    }

    function removeTour(id) {
        const row = document.getElementById(`${id}`);
        if (row) {
            row.remove();
            tourCount--;
        }
    }

    function loadOrderTour(tour_id)
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '{{ route('tour.single') }}',
            type: 'POST',
            data: {
                id: tour_id,
                tourCount: tourCount
            },
            success: function(response) {
                //console.log('Success:', response);
                $('#tour_all').append(response);
                $('#tourContainer').html('');
                tourCount++;

                TB.plugins.dateRange();
                TB.plugins.timePicker();
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    $(document).on('click', '.fa-calendar', function() {
        $(this).closest('.input-group').find('.aiz-time-picker, .aiz-date-range').focus();
    });

    $(document).ready(function(){
        // Add down arrow icon for collapse element which is open by default
        $(".collapse.show").each(function(){
            $(this).prev(".card-header").find(".fa").addClass("fa-angle-down").removeClass("fa-angle-right");
        });
        
        // Toggle right and down arrow icon on show hide of collapse element
        $(".collapse").on('show.bs.collapse', function(){
            $(this).prev(".card-header").find(".fa").removeClass("fa-angle-right").addClass("fa-angle-down");
        }).on('hide.bs.collapse', function(){
            $(this).prev(".card-header").find(".fa").removeClass("fa-angle-down").addClass("fa-angle-right");
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

    // ✅ Function to update status UI
    function updateStatusUI(radio) {
        const id     = radio.id;
        const label  = document.querySelector(`label[for="${id}"]`).textContent.trim();
        const group  = radio.closest('.btn-group');
        const button = group.querySelector('button.dropdown-toggle');

        // Update button text
        button.textContent = label;

        // Remove old status classes
        button.classList.remove(
            'status-NEW','status-ON_HOLD','status-PENDING_SUPPLIER',
            'status-PENDING_CUSTOMER','status-CONFIRMED',
            'status-CANCELLED','status-ABANDONED_CART'
        );

        // Add new class based on status
        const safeId = id.replace(/\s+/g, '_').toUpperCase();
        button.classList.add(`status-${safeId}`);
    }

    // ✅ On click of payment option
    document.querySelectorAll('.payment-details-breakdown--item').forEach(item => {
        item.addEventListener('click', function () {
            const total = this.querySelectorAll('strong')[1].textContent;
            const btn   = document.getElementById('totalDue');
            btn.textContent = total;

            // make green
            const wrap = btn.closest('.btn.dropdown-toggle');
            wrap.style.borderColor = '#28a745';
            wrap.style.color = '#28a745';
        });
    });

    // ✅ Attach event to each order_status radio
    document.querySelectorAll('input[name="order_status"]').forEach(radio => {
        radio.addEventListener('change', function (e) {

            // update UI
            
            // updateStatusUI(this);

            // now update backend via fetch()
            const order_id = document.getElementById('order_id').value;
            const status = this.value;
            
            fetch(`/admin/orders/${order_id}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(async res => {
              if (!res.ok) {
                  const errText = await res.text();
                  throw new Error(`HTTP ${res.status}: ${errText}`);
              }
              return res.json();
          })
          .then(data => {
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Success' : 'Error',
                text: data.message,
            }).then(() => {
                location.reload();
            });
            // console.log("Status updated:", data);
            // location.reload(); // 🔄 reload page after successful update
        })
          .catch(err => console.error("Fetch error:", err));
        });
    });

    // ✅ Only update UI on page load, not backend

    const init = document.querySelector('input[name="order_status"]:checked');
    if (init) updateStatusUI(init);
});


    $(document).on('click', '.charge-btn', function(e) {
    e.preventDefault();
    let orderId = $(this).data('order-id');
    let customerName = $(this).data('customer-name');
    let balance = $(this).data('balance');

    $('#cardDetails').text("Loading...");
    $('#customerName').html(customerName);
    $('#showChargeAmount').html(balance);
    $('#chargeOrderId').val(orderId);
    $('#chargeAmount').val(balance);
    $('#chargeModal').modal('show');


    
});

// Handle charge form submit
$('#chargeForm').on('submit', function(e) {

    e.preventDefault();

    let orderId = $('#chargeOrderId').val();
    let amount  = $('#chargeAmount').val();

    $.ajax({
        // url: 'staging/admin/orders/' + orderId + '/charge',
        url: "{{ route('admin.orders.charge', ['order' => '__ORDER_ID__']) }}".replace('__ORDER_ID__', orderId),
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            amount: amount
        },
        success: function(response) {
            $('#chargeModal').modal('hide');
            if(response.message){
                // alert(response.message);

                Swal.fire({
                    icon: response.success ? 'success' : 'error',
                    title: response.success ? 'Success' : 'Error',
                    text: response.message,
                }).then(() => {
                    location.reload();
                });
            } else{
                
                Swal.fire({
                    icon: 'error',
                    title: response.success ? 'Success' : 'Error',
                    text: 'Payment captured successfully!',
                }).then(() => {
                    location.reload();
                });
            }
            
            location.reload();
        },
        error: function(xhr) {


            Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Payment capture failed: ' + xhr.responseJSON.message,
                }).then(() => {
                    location.reload();
                });
        }
    });
});


</script>
<script>
$(document).ready(function(){    
    //Order Email Modal
    $('#email_template_name').change(function() {
        var order_id = $('#order_id').val();
        var order_template_id = $(this).val();

        const data = {
            'order_id': order_id,
            'order_template_id': order_template_id
        }
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{ route('admin.order_template_details') }}",
            type: 'POST',
            data: data,
            success: function(response) {
                console.log(response.event);
                $('#email').val(response.email);
                $('#bcc_mail').val(response.bcc_mail);
                $('#cc_mail').val(response.cc_mail);
                $('#identifier').val(response.email_template.identifier);
                $('#subject').val(response.email_template.subject);
                $('#event').val(JSON.stringify(response.event));
                //$('#header').summernote('code', response.email_template.header);
                $('#body').summernote('code', response.body);
                //$('#footer').summernote('code', response.footer);
                $('#order_template_modal').modal("show");
                $('#email_template_name').val('');
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    // Send Email
    $('#order_mail').on('submit', function(e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('admin.mail_send') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('Success:', response);
                toastr.success("Mail sent successfully")
                $('.modal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    //Order SMS Modal 
    $('#sms_template_name').change(function() {
        var order_id = $('#order_id').val();
        var order_confirmation_id = $(this).val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{ route('admin.order_confirmation_message') }}",
            type: 'POST',
            data: {
                order_id: order_id,
                order_confirmation_id: order_confirmation_id
            },
            success: function(response) {

                $('#mobile_number').val(response.mobile);
                $('#identifier').val(response.confirmation_template.identifier);
                $('#message').summernote('code', response.message);
                $('#order_confirmation_sms').modal("show");
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    // Send SMS 
    $('#order_sms_send').on('submit', function(e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('admin.order_sms_send') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('Success:', response);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });
});

$(document).on('click', '.refund-btn', function(e) {
    e.preventDefault();
    let orderId = $(this).data('order-id');
    let amount = $(this).data('amount');

    $('#refundOrderId').val(orderId);
    $('#refundAmount').val(amount);
    $('#refundModal').modal('show');
});



</script>
<<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const addBtn = document.getElementById("addPaymentBtn");
    const block = document.getElementById("addPaymentBlock");

    addBtn.addEventListener("click", () => {
        block.style.display = block.style.display === "none" ? "block" : "none";
    });

    // Initialize Stripe
    const stripe = Stripe("{{ env('STRIPE_KEY') }}");
    const elements = stripe.elements();
    const style = {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: 'Arial, sans-serif'
        },
        invalid: { color: '#fa755a' }
    };
    const card = elements.create('card', { style });
    card.mount('#card-element');

    // Show validation errors
    card.on('change', function(event) {
        document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
    });

    // Handle payment
    const submitBtn = document.getElementById("addPaymentSubmit");
    submitBtn.addEventListener("click", async function() {
        const amount = document.getElementById("addPaymentAmount").value;
        if (!amount || amount <= 0) {
            alert("Please enter a valid amount");
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Processing...";

        // Create Stripe Payment Method
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: card,
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            submitBtn.disabled = false;
            submitBtn.textContent = "Pay Now";
            return;
        }

        // Extract card details (safe data only)
        const cardData = {
            last4: paymentMethod.card.last4,
            brand: paymentMethod.card.brand,
            exp_month: paymentMethod.card.exp_month,
            exp_year: paymentMethod.card.exp_year,
        };

        // Send payment info to backend
        const response = await fetch("{{ route('admin.orders.addPayment', $order->id) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                payment_method_id: paymentMethod.id,
                amount: amount,
                card_last4: cardData.last4,
                card_brand: cardData.brand,
                card_exp_month: cardData.exp_month,
                card_exp_year: cardData.exp_year,
            })
        });

        const data = await response.json();

        if (data.success) {
            alert("Payment added successfully!");
            location.reload();
        } else {
            alert("Error: " + data.message);
            submitBtn.disabled = false;
            submitBtn.textContent = "Pay Now";
        }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function(e) {
    e.preventDefault();

    // When refund modal is opened
    document.querySelectorAll('.open-refund-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('refundPaymentId').value = this.dataset.id;
            document.getElementById('refundAmount').value = this.dataset.amount;
            document.getElementById('refundReason').value = '';
        });
    });

    // Handle refund form submit
    document.getElementById('refundForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const paymentId = document.getElementById('refundPaymentId').value;
        const amount = document.getElementById('refundAmount').value;
        const reason = document.getElementById('refundReason').value;

        if (!amount || amount <= 0) {
            alert("Please enter a valid refund amount.");
            return;
        }

        const btn = document.querySelector("button[form='refundForm'][type='submit']");
        btn.disabled = true;
        btn.textContent = "Processing...";

        const response = await fetch("{{ route('admin.orders.refundPayment', $order->id) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                payment_id: paymentId,
                amount: amount,
                reason: reason
            })
        });

        const data = await response.json();
        btn.disabled = false;
        btn.textContent = "Confirm Refund";

        if (data.success) {
            alert("Refund successful!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    });

});


</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $(document).on('click', '.open-refund-modal', function(e) {
        e.preventDefault();

        const paymentId = $(this).data('id');
        const amount = $(this).data('amount');

        // Set values inside modal
        $('#refundOrderId').val(paymentId);
        $('#refundAmount').val(amount);

        // Manually show modal (to ensure it opens even if Bootstrap auto-toggle fails)
        const refundModal = new bootstrap.Modal(document.getElementById('refundModal'));
        refundModal.show();
    });
});
</script>


@endsection
</x-admin>

