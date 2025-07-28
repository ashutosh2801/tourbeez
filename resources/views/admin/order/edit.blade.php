<x-admin>
    @section('title', 'Order '.$order->order_number)

@section('css')
<style>
    .bs-example{
        margin: 20px;
    }
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
@endphp
    <form action="{{ route('admin.orders.update',$order->id) }}" method="POST">
    @method('PUT')
    @csrf
    <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}" /> 
    <div class="card card-primary">
        <div class="card-header">
            <h5>Created on {{ date__format($order->created_at) }} online on your booking form</h5>
        </div>
        <div class="card-body">
            <div style="padding:0 15px;">

                <div class="row">
                    {{-- <div class="col-lg-2 text-ceneter">
                        <label class="d-block" for="">Balance</label>
                        <select class="form-control" style="border:0" name="order_balance" id="order_balance">
                            <option value="$0.00" >$0.00</option>
                            <option value="Paid" >Paid {{ price_format_with_currency($order->total_amount, $order->currency) }}</option>
                            <option value="Refunded" >Refunded $0.00</option>
                        </select>
                    </div>
                    <div class="col-lg-2 text-ceneter">
                        <label class="d-block" for="">Order Status</label>
                        <select class="form-control" style="border:0" name="order_status" id="order_status">
                            @foreach( order_status_list() as $key => $status)
                            <option value="{{ $key }}" >{{ $status }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="col-lg-5 btngroup">
                        <div class="justify-center item-center">
                            <div class="payment-status order-balance paid">
                                <div class="btn-group">
                                    <label for="totalDue">Balance</label>
                                    <button type="button" class="btn dropdown-toggle arrow" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <strong id="totalDue" class="total-due">{{ price_format_with_currency($order->balance_amount, $order->currency) }}</strong>
                                    </button>
                                    <ul class="dropdown-menu dropdown-value payment-details-breakdown--container">
                                        <li class="payment-details-breakdown--item"><strong class="payment-details-breakdown--text">Paid</strong> <strong class="payment-details-breakdown--text">{{ price_format_with_currency($order->total_amount, $order->currency) }}</strong></li>
                                        <li class="payment-details-breakdown--item"><strong class="payment-details-breakdown--text">Refunded</strong> <strong class="payment-details-breakdown--text">$0.00</strong></li>
                                    </ul>
                                </div>
                            </div>
                            <!-- <div class="order-status" >
                                <div class="btn-group open">
                                    <label for="order_status">Order Status </label>
                                    <button type="button" class="btn dropdown-toggle arrow childOrderEnabled" data-element-to-update=".payment-status" data-selected="CONFIRMED" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Confirmed</button>
                                    <ul class="dropdown-menu dropdown-value">
                                        <li>
                                            <input type="radio" id="NEW" name="order_status" value="NEW" autocomplete="off">
                                            <label for="NEW" class="NEW">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                New                                    </label>
                                        </li>
                                        <li>
                                            <input type="radio" id="ON_HOLD" name="order_status" value="ON_HOLD" autocomplete="off">
                                            <label for="ON_HOLD" class="ON_HOLD">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                On Hold                                    </label>
                                        </li>
                                        <li>
                                            <input type="radio" id="PENDING_SUPPLIER" name="order_status" value="PENDING_SUPPLIER" autocomplete="off">
                                            <label for="PENDING_SUPPLIER" class="PENDING_SUPPLIER">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                Pending supplier                                    </label>
                                        </li>
                                        <li>
                                            <input type="radio" id="PENDING_CUSTOMER" name="order_status" value="PENDING_CUSTOMER" autocomplete="off">
                                            <label for="PENDING_CUSTOMER" class="PENDING_CUSTOMER">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                Pending customer                                    </label>
                                        </li>
                                        <li>
                                            <input type="radio" id="CONFIRMED" name="order_status" value="CONFIRMED" autocomplete="off" checked="checked">
                                            <label for="CONFIRMED" class="CONFIRMED">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                Confirmed                                    </label>
                                        </li>
                                        <li>
                                            <input type="radio" id="CANCELLED" name="order_status" value="CANCELLED" autocomplete="off">
                                            <label for="CANCELLED" class="CANCELLED">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                Cancelled                                    </label>
                                        </li>
                                        <li>
                                            <input type="radio" id="ABANDONED_CART" name="order_status" value="ABANDONED_CART" autocomplete="off">
                                            <label for="ABANDONED_CART" class="ABANDONED_CART">
                                                <i class="fa fa-circle" aria-hidden="true"></i>
                                                Abandoned cart                                    </label>
                                        </li>
                                                                </ul>
                                </div>
                            </div> -->


                            <div class="order-status">
                                <div class="btn-group open">
                                    <label for="order_status">Order Status</label>
                                    <button type="button" class="btn dropdown-toggle arrow childOrderEnabled"
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
                    </div>
                    <div class="col-lg-7 text-right">
                        <select class="form-control" style="width:150px; display:inline-block;" name="email_template_name" id="email_template_name">
                            <option value="" >Email</option>
                            @foreach($email_templates as $email_template)
                                <option value="{{$email_template->id}}" >{{snakeToWords($email_template->identifier)}} -> Send Now</option>
                            @endforeach
                            <!-- <option value="14" >Order Details -> Send Now</option>
                            <option value="Order Cancellation" >Order Cancellation -> Send Now</option>
                            <option value="Payment Receipt" >Payment Receipt -> Send Now</option>
                            <option value="Reminder 1st" >Reminder 1st -> Send Now</option>
                            <option value="Reminder 2nd" >Reminder 2nd -> Send Now</option>
                            <option value="Reminder 3rd" >Reminder 3rd -> Send Now</option>
                            <option value="FollowUp Review" >FollowUp Review -> Send Now</option>
                            <option value="FollowUp Recommend" >FollowUp Recommend -> Send Now</option>
                            <option value="FollowUp Coupon" >FollowUp Coupon -> Send Now</option>
                            <option value="Simple Email" >Simple Email -> Send Now</option> -->
                        </select>

                        <select class="form-control" style="width:150px; display:inline-block;" name="sms_template_name" id="sms_template_name">
                            <option value="" >SMS</option>

                            @foreach($sms_templates as $sms_template)
                           <!--  <option value="14" >Order Confirmation -> Send Now</option>
                            <option value="Reminder" >Reminder -> Send Now</option>
                            <option value="FollowUp" >FollowUp -> Send Now</option>
                            <option value="Simple" >Simple -> Send Now</option> -->

                            <option value="{{$sms_template->id}}" >{{snakeToWords($sms_template->identifier)}} -> Send Now</option>

                            @endforeach
                        </select>

                        <select class="form-control" style="width:150px; display:inline-block;" name="print_template_name" id="print_template_name">
                            <option value="" >Print</option>
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

                                @if($order->user)
                                    <ul class="flex flex-row">
                                        <li><a href="{{ route('admin.user.edit', encrypt($order->user_id) ) }}" class="alink" target="_blank">{{ $order->user?->name }}</a></li>
                                        <li>{{ $order->user?->email }}</li>
                                        <li>{{ $order->user?->phonenumber }}</li>
                                    </ul>
                                @else
                                    <ul class="flex flex-row">
                                        <!-- <li><a href="{{ route('admin.user.edit', encrypt($order->user_id) ) }}" class="alink" target="_blank">{{ $order->customer?->name }}</a></li> -->
                                        <li>{{ $order->customer?->name }}</li>
                                        <li>{{ $order->customer?->email }}</li>
                                        <li>{{ $order->customer?->phone }}</li>
                                    </ul>
                                @endif
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
                                        <tr>
                                            <td><b>Booking fee</b> (included in price)</td>
                                            <td class="text-right">{{ $order->bookingFee ? price_format_with_currency($order->bookingFee->value('value'), $order->currency) : "NA" }} </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total</b></td>
                                            <td class="text-right">{{ price_format_with_currency($order->total_amount, $order->currency) }} {{ $order->currency}}</td>
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
                                <!-- <textarea class="form-control" name="additional_info" id="additional_info" rows="4" placeholder="Additional information">{{ $order->additional_info }}</textarea> -->


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

                                         
                                        @foreach ($order->tour->pickups as $pickup)
                                            <tr>
                                                <td><b>Pickup Location</b></td>
                                                <td class="text-right">{{ $pickup->name }}</td> 
                                                

                                            </tr>
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
                                <table class="table">    
                                    <tr>
                                        <td>Total: {{ price_format_with_currency($order->total_amount, $order->currency) }}</td>
                                    </tr>

                                    <tr>
                                        <td>Stored Credit Card</td>
                                        <td>{{ $order->card_info }}</td>
                                        <td>STRIPE</td>
                                        <td>{{ $order->transaction_id }}</td>
                                    </tr>
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
            <!-- recent action need to update this -->
            <!-- <div class="bs-example">
                <div class="card">
                    <div class="card-body">
                        <h5>Recent actions</h5>
                        <table class="table">    
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:44 PM</td>
                                <td>Vito G confirmed Arya Suresh's order</td>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:43 PM</td>
                                <td>System charged {{ price_format_with_currency($order->total_amount) }} on credit card XXXXXXXXXXXX5959. Reference number is ch_3RSiaLEcMxhlmBMk0dT82PRI</td>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:43 PM</td>
                                <td>Arya Suresh made a new order on your booking form</td>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:43 PM</td>
                                <td>Order created with Credit card payment of {{ price_format_with_currency($order->total_amount) }}</td>
                            </tr>
                        </table>


                        <h5 class="mt-5">Sent emails</h5>
                        <table class="table">    
                            <tr>
                                <th>Sent</th>
                                <th>To</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:44 PM</td>
                                <td>info@discount.tours</td>
                                <td>Discount.Tours</td>
                                <td>Supplier Notification</td>
                                <td>Opened</td>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:44 PM</td>
                                <td>aryaofficial203@gmail.com</td>
                                <td>Arya Suresh</td>
                                <td>Online Booking Confirmation</td>
                                <td>Opened</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div> -->

            <!-- recent action -->
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
                                                            <input type="text" name="cc_mail" class="form-control" placeholder="{{translate('CC Mail')}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-md-12 col-form-label">{{translate('BCC Mail')}}</label>
                                                        <div class="col-md-12">
                                                            <input type="text" name="bcc_mail" class="form-control" placeholder="{{translate('BCC Mail')}}">
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
@endsection

@section('js') 
@parent()  
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
        // Balance dropdown: keep green on select
        document.querySelectorAll('.payment-details-breakdown--item').forEach(item => {
            item.addEventListener('click', function () {
            const total = this.querySelectorAll('strong')[1].textContent;
            const btn    = document.getElementById('totalDue');
            btn.textContent = total;
            // ensure green
            const wrap = btn.closest('.btn.dropdown-toggle');
            wrap.style.borderColor = '#28a745';
            wrap.style.color       = '#28a745';
            });
        });

        // Order status: when a radio changes, update button text + color class
        document.querySelectorAll('input[name="order_status"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const id     = this.id;
            const label  = document.querySelector(`label[for="${id}"]`).textContent.trim();
            const group  = this.closest('.btn-group');
            const button = group.querySelector('button.dropdown-toggle');

            // Update text
            button.textContent = label;

            // Remove old status classes
            button.classList.remove(
                'status-NEW','status-ON_HOLD','status-PENDING_SUPPLIER',
                'status-PENDING_CUSTOMER','status-CONFIRMED',
                'status-CANCELLED','status-ABANDONED_CART'
            );

            // Sanitize ID to use in class
            const safeId = id.replace(/\s+/g, '_').toUpperCase();

            // Add new status class
            button.classList.add(`status-${safeId}`);
            
            // 🔽 Add AJAX here to update order status in backend
               // get order ID from input

            var order_id = $('#order_id').val();

            console.log(order_id);
            const status = this.value;              // selected status

            fetch(`/admin/orders/${order_id}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Status updated.');
                } else {
                    console.error('Failed to update status.');
                    alert('Could not update order status.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong while updating status.');
            });
        });
    });



        // Initialize on page load for whichever is checked
        const init = document.querySelector('input[name="order_status"]:checked');
        if (init) init.dispatchEvent(new Event('change'));
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
                $('#email').val(response.email);
                $('#identifier').val(response.email_template.identifier);
                $('#subject').val(response.email_template.subject);
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
</script>
@endsection
</x-admin>
