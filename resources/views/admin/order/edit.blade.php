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
    .modal-wide {
        max-width: 70% !important;
        margin: 10px auto !important;   /* center horizontally */
    }
</style>
@endsection

@php
    $statuses = config('constants.order_statuses');

    $expectEmails = ['order_pending', 'payment_receipt'];
    
@endphp

    <form action="{{ route('admin.orders.update',$order->id) }}" method="POST">
    @method('PUT')
    @csrf
    <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}" /> 
    <input type="hidden" name="order_number" id="order_number" value="{{ $order->order_number }}" /> 


    <div class="card card-primary rounded-lg-custom border order-edit-head">
        <div class="card-header">
            <div class="row">
                <div class="col-md-9">
                    <h5 class="m-0">Created on {{ date__format($order->created_at) }} online on your booking form</h5>
                </div>
                <div class="col-md-3 {{ $order->payments->isNotEmpty() ? '' : 'd-none' }}">
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
                                        <strong class="payment-details-breakdown--text">{{  price_format_with_currency(0, $order->currency) }}</strong>
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
                    <div class="info-blog">
                        <div class="info-stats4">
                            <div class="info-icon flex-shrink-0">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="sale-num">
                                <p>Print</p>
                                <select class="form-control form-option" name="print_template_name" id="print_template_name">
                                    <option value="" >Select</option>

                                    @foreach($email_templates as $email_template)

                                        @if(in_array($email_template->identifier, $expectEmails))
                                            @continue
                                        @endif


                                        <option value="{{$email_template->id}}" >{{snakeToWords($email_template->identifier)}} -> Print Now</option>
                                    @endforeach
                                    <!-- <option value="Order Details" >Order Details -> Send Now</option>
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
                            </div>
                        </div>
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

                                
                            
                               
                                    <ul class="flex flex-row">


                                        <li><a href="{{ route('admin.customers.show', encrypt($order->customer?->id) ) }}" class="alink" target="_blank">{{ $order->customer?->name }}</a></li>
                                        <li>{{ $order->customer?->email }}</li>
                                        <li>{{ $order->customer?->phone }}</li>
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
                                    <!-- <table class="table">
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
                                    </table> -->
                                    <table class="table">
                                        <tr id="row_{{ $row_id }}">
                                            <td width="600"><h3 class="text-lg">{{ $order_tour->tour?->title }}</h3></td>

                                            <td class="text-right" width="200">
                                                <div class="input-group">
                                                    <input type="text"
                                                           class="aiz-date-range form-control tour_startdate"
                                                           name="tour_startdate[]"
                                                           placeholder="Select Date"
                                                           data-single="true"
                                                           data-show-dropdown="true"
                                                           value="{{ $order_tour->tour_date }}">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    </div>
                                                </div>

                                                <div>
                                                    <input type="text" class="tour_startdate_display border-0" readonly>
                                                </div>
                                            </td>

                                            <td class="text-right" width="200">
                                                <div class="input-group">
                                                    <input type="text"
                                                           placeholder="Time"
                                                           name="tour_starttime[]"
                                                           class="form-control aiz-time-picker tour_starttime"
                                                           data-minute-step="1"
                                                           value="{{ $order_tour->tour_time }}">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    </div>
                                                </div>
                                            </td>

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
                                                            if($order_tour->tour?->price_type =='FIXED'){
                                                                $subtotal = $subtotal + $price;
                                                            } else{
                                                                $subtotal = $subtotal + ($result['quantity'] * $price);
                                                            }
                                                            
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td width="60">
                                                            <input type="hidden" name="tour_pricing_id_{{$_tourId}}[]" value="{{ $pricing->id }}" />  
                                                            <input type="number" name="tour_pricing_qty_{{$_tourId}}[]" value="{{ $result['quantity'] ?? 0 }}" style="width:60px" class="form-contorl text-center">
                                                            <input type="hidden" name="tour_pricing_price_{{$_tourId}}[]" value="{{ $price }}" />  
                                                             

                                                            <input type="hidden" name="tour_pricing_type_{{$_tourId}}[]" value="{{ $order_tour->price_type }}" /> 
                                                            <input type="hidden" name="tour_pricing_min_{{$_tourId}}[]" value="{{$pricing->quantity_used}}">
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

                                        $withoutTax = $subtotal;
                                        $i=1;
                                        $taxesfees = $order_tour->tour->taxes_fees;
                                        @endphp 
                                        <tr>
                                            <th>Sub Total </th>
                                            <th class="text-right withouttax-box">  {{ price_format_with_currency($withoutTax, $order->currency) }} </th>
                                        </tr>

                                        @if( $taxesfees )
                                        @foreach ($taxesfees as $key => $item)  
                                        @php
                                        $price      = get_tax($subtotal, $item->fee_type, $item->tax_fee_value);
                                        $tax        = $price ?? 0;
                                        $subtotal   = $subtotal + $tax; 
                                        @endphp 
                                        <tr class="tax-row" data-type="{{ $item->fee_type }} " data-value="{{ $item->tax_fee_value}} ">
                                            <td>{{ $item->label }} ({{ taxes_format($item->fee_type, $item->tax_fee_value) }})</td>
                                            <td class="text-right tax-amount">{{ price_format_with_currency($tax, $order->currency) }}</td>
                                        </tr>

                                        

                                        
                                        @endforeach
                                        @endif

                                        

                                        <tr>
                                            <th>Total </th>
                                            <th class="text-right subtotal-box">  {{ price_format_with_currency($subtotal, $order->currency) }} </th>
                                        </tr>
                                    </table>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <div id="tourContainer"></div>

                                <div class="cummulative-total" style="border:1px solid #e1a604; margin-bottom:10px">
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
                                            <td class="cummulative-total"><b>Total</b></td>
                                            <td class="text-right">{{ price_format_with_currency($order->total_amount, $order->currency) }}</td>
                                        </tr>
                                        <tr class="cummulative-total" style="color: red">
                                            <td><b>Balance</b></td>

                                            @if($order->payment_status ==3)


                                                <td class="text-right cummulative-total"><b>{{ price_format_with_currency($order->balance_amount + $order->booked_amount, $order->currency) }}</b></td>
                                            @else

                                                <td class="text-right cummulative-total"><b>{{ price_format_with_currency($order->balance_amount, $order->currency) }}</b></td>
                                            @endif

                                            
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
                                        <!-- <tr>
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
                                        </tr> -->

                                        @php
                                            $pickName = '';
                                            $instruction = '';
                                            if($order->customer && $order->customer->pickup_name){
                                                $pickName = $order->customer->pickup_name;
                                                $instruction = $order->customer->instructions;
                                            } elseif($order->customer && $order->customer->pickup_id) {
                                                $pickLocation = \App\Models\PickupLocation::find($order->customer->pickup_id);
                                                $pickName = $pickLocation->location . " - " . $pickLocation->address . " - " . $pickLocation->time;
                                                $instruction = $order->customer->instructions;
                                            }
                                        @endphp
                                        <tr>
                                            <td><b>Pickup Location</b></td>
                                            <td class="text-right">{{ $pickName }}</td> 
                                            

                                        </tr>
                                         
                                        <!-- @foreach ($order->tour->pickups as $pickup)
                                           
                                            <tr>
                                                <td><b>Pickup Charge</b></td>
                                                <td class="text-right">{{ $pickup->pickup_charge }}</td>
                                            </tr> 
                                        @endforeach -->

                                        <tr>
                                            <td><b>Intructions</b></td>
                                            <td class="text-right">{{ $instruction }}</td> 
                                            

                                        </tr>
                                        
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- <div class="card  {{ $order->payments->isNotEmpty() ? '' : 'd-none' }}"> -->
                     <div class="card">
                        <div class="card-header bg-secondary py-0" id="headingThree">
                            <h2 class="my-0 py-0">
                                <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" data-toggle="collapse" data-target="#collapseThree"><i class="fa fa-angle-right"></i> Payment Details</button>                     
                            </h2>
                        </div>

                        <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordionExample">
                            <div class="card-body">
                                <!-- <div class="card text-success" ><p>This customer choose to pay {{ ($order->adv_deposite =='full') ? ucwords($order->adv_deposite) : "Partial" }} amount ({{ price_format_with_currency($order->booked_amount, $order->currency) }})</p></div> -->
                                
                                <table class="table">    
                                    <tr>
                                        <td>Payment Type</td>
                                        <td>Ref number</td>
                                        
                                        <td>Total</td>
                                        <td></td>
                                        <td>Balance</td>
                                        <td>Paid</td>
                                        @if($order->payments->isNotEmpty())
                                            <td>Refund</td>
                                        @endif



                                    </tr>
                                    <tr>
                                    <td>{{ ucwords($order->payment_method)}}</td>
                                    <td>{{ ucwords($order->payment_intent_id)}}</td>
                                    
                                    <td>{{ price_format_with_currency($order->total_amount, $order->currency) }}</td>
                                        <td></td>

                                        @if($order->payment_status ==3)


                                            <td>{{ price_format_with_currency($order->balance_amount + $order->booked_amount, $order->currency) }}</td>
                                        @else

                                            <td>{{ price_format_with_currency($order->balance_amount, $order->currency) }}</td>
                                        @endif
                                        
                                        <td>{{ price_format_with_currency($order->booked_amount, $order->currency) }}</td>
                                   <td>
                                        <!-- <button class="btn btn-sm btn-danger refund-btn" 
                                          style="width:150px; display:inline-block;" 
                                          data-order-id="{{ $order->id }}" 
                                          data-amount="{{ $order->booked_amount }}" 
                                          type="button">
                                          Refund
                                        </button> -->
                                        @if($order->payments->isNotEmpty())
                                            <button class="btn btn-sm btn-danger refund-all-btn"
                                                data-order-id="{{ $order->id }}"
                                                data-amount="{{ $order->booked_amount }}" style="width:150px; display:inline-block;">
                                                Refund
                                            </button>
                                        @endif
                                    </td>
                                     </tr>

                                </table>
                                @if($order->payments->isNotEmpty())
                                <h5 class="mt-4">💳 Payment Details</h5>

                                    
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
                                        <!-- <p class="text-muted">No payments have been recorded yet.</p> -->
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
                                    <input hidden type="number" id="addPaymentAmount" class="form-control" min="1" placeholder="Enter amount" value="">
                                </div>

                                <div id="cardFields">
                                    <div class="form-group">
                                        <label for="card-element">Card Details</label>
                                        <div id="card-element" class="form-control col-6" style="padding:10px; height:auto;"></div>
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
                                            @foreach($order->emailHistories->sortByDesc('created_at') as $email)
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
    <div class="modal-dialog modal-lg modal-wide" role="document">
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
                                                    <div class="form-group row sendMailbutton">
                                                        <label class="col-md-12 col-form-label">To</label>
                                                        <div class="col-md-12">
                                                            <input type="text" name="email"  id="email"  class="form-control" placeholder="{{translate('TO')}}" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row sendMailbutton">
                                                        <label class="col-md-12 col-form-label">{{translate('CC Mail')}}</label>
                                                        <div class="col-md-12 sendMailbutton">
                                                            <input type="text" name="cc_mail" id="cc_mail" class="form-control" placeholder="{{translate('CC Mail')}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row sendMailbutton">
                                                        <label class="col-md-12 col-form-label">{{translate('BCC Mail')}}</label>
                                                        <div class="col-md-12">
                                                            <input type="text" name="bcc_mail" id="bcc_mail" class="form-control" placeholder="{{translate('BCC Mail')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row sendMailbutton">
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

                                                    <div class="form-group row" id="print_modal">
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
                        <button type="submit" class="btn btn-primary sendMailbutton">Send Mail</button>
                        <button type="button" class="btn btn-primary printMailbutton">Print PDF</button>
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

          <!-- <div class="mb-3">
            <label>Reason (optional)</label>
            <input type="text" id="refundReason" name="reason" class="form-control">
          </div> -->
        </form>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button> -->
        <button type="submit" form="refundForm" class="btn btn-danger">Confirm Refund</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="refundAllModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Refund  Payment</h5>
      </div>
      <div class="modal-body">
        <form id="refundAllForm">
          <input type="hidden" id="refundAllOrderId" name="order_id">

          <div class="mb-3">
            <label>Refund Amount</label>
            <input type="number" id="refundAllAmount" name="amount" 
                class="form-control" min="0.5" step="0.01" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="refundAllForm" class="btn btn-danger">
            Confirm Refund
        </button>
      </div>
    </div>
  </div>
</div>


<!-- GLOBAL REUSABLE LOADER -->
<div id="globalLoader" 
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(255,255,255,0.6); z-index:99999;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                text-align:center; font-size:18px;">

        <div class="loader-spinner" 
             style="width:40px; height:40px; border:4px solid #ccc; 
                    border-top-color:#3498db; border-radius:50%;
                    animation: spin 0.8s linear infinite; margin:auto;">
        </div>

        <div style="margin-top:10px; font-weight:bold; color:#333;">
            Processing...
        </div>
    </div>
</div>

<!-- Spinner Animation -->
<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>





@endsection

@section('js') 
@parent() 
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

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
                        id="load_order_tour" class="form-control aiz-selectpicker border" 
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
        // const button = group.querySelector('button.dropdown-toggle');

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

            const selectedRadio = this; 
            const previousValue = selectedRadio.getAttribute("data-prev"); // store old value

            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to change the order status?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, change it",
                cancelButtonText: "No, keep previous"
            }).then(result => {

                if (!result.isConfirmed) {
                    // User clicked No → revert selection
                    if (previousValue) {
                        document.querySelector(`input[name="order_status"][value="${previousValue}"]`).checked = true;
                    }
                    location.reload();
                    return; // stop execution
                }

                // User confirmed → proceed with update
                showLoader("Loading… Please wait");

                const order_id = document.getElementById('order_id').value;
                const status = selectedRadio.value;

                const updateStatusRoute = "{{ route('admin.orders.update-status', ['id' => ':id']) }}";
                let url = updateStatusRoute.replace(':id', order_id);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(async res => {
                    hideLoader();
                    if (!res.ok) {
                        const errText = await res.text();
                        throw new Error(`HTTP ${res.status}: ${errText}`);
                    }
                    return res.json();
                })
                .then(data => {
                    hideLoader();
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Success' : 'Error',
                        text: data.message,
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch(err => console.error("Fetch error:", err));
            });

            // save current selected value as previous

            document.querySelectorAll('input[name="order_status"]').forEach(r => {
                if (r.checked) {
                    r.setAttribute("data-prev", r.value);
                }
            });


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
        showLoader("Loading… Please wait");
        var order_id = $('#order_id').val();
        var order_template_id = $(this).val();

        var print_template = false;


        if (print_template) {
            $('.printMailbutton').show();
            $('.sendMailbutton').hide();
        } else {
            $('.printMailbutton').hide();
            $('.sendMailbutton').show();
        }

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
                hideLoader();
                
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
                hideLoader();
                console.error('Error:', error);
            }
        });
    });

     $('#print_template_name').change(function() {
        showLoader("Loading… Please wait");
        var order_id = $('#order_id').val();
        var order_template_id = $(this).val();
        var print_template = true;


        if (print_template) {
            $('.printMailbutton').show();
            $('.sendMailbutton').hide();
        } else {
            $('.printMailbutton').hide();
            $('.sendMailbutton').show();
        }

        const data = {
            'order_id': order_id,
            'order_template_id': order_template_id,
            'print_template': print_template,
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
                hideLoader();
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
                
                $('#print_template_name').val('');

            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $('#print_template_name').val('');

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
<script src="https://js.stripe.com/v3/"></script>
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

    function getTotalFromSubtotalBoxes() {
        let total = 0;

        document.querySelectorAll('.subtotal-box').forEach(el => {
            const val = parseFloat(el.textContent.trim()) || 0;
            total += val;
        });

        return total;
    }

    // Handle payment
    const submitBtn = document.getElementById("addPaymentSubmit");
    submitBtn.addEventListener("click", async function() {

        
        // const amount = document.getElementById("addPaymentAmount").value;

        const amount = getTotalFromSubtotalBoxes();

        
        document.getElementById("addPaymentAmount").value = amount;
        if (!amount || amount <= 0) {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please enter a valid amount',
            }).then(() => {

            });
            
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
        showLoader("Adding Payment. Please wait...");
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

            hideLoader();
            Swal.fire({
                icon: 'success',
                title:'Success',
                text: 'Payment added successfully!',
            }).then(() => {
                location.reload();
            });
            
            
        } else {
            hideLoader();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
            }).then(() => {

            });
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
            
            Swal.fire({
                icon: 'success',
                title:'Success',
                text: 'Please enter a valid refund amount.!',
            }).then(() => {
                
            });
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
            
            Swal.fire({
                icon: 'success',
                title:'Success',
                text: 'Refund successful!',
            }).then(() => {
                location.reload();
            });

            // location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title:'Error',
                text: data.message,
            }).then(() => {
                location.reload();
            });
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

<script>

$(document).on('click', '.printMailbutton', function () {

    showLoader("Generating PDF… Please wait");

    $('#order_template_modal').modal("hide");
    var summernoteContent = $('#body').summernote('code');
    var order_id = $('#order_id').val();

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = summernoteContent;

    tempDiv.style.padding = "20px";
    tempDiv.style.fontFamily = "Arial, sans-serif";
    tempDiv.style.fontSize = "13px";
    tempDiv.style.lineHeight = "1.5";

    // ---------------------------------------
    // 🔥 AUTO-SCALE LARGE WIDTHS (800px, 780px etc.)
    // ---------------------------------------
    $(tempDiv)
        .find('*')
        .each(function () {
            var w = $(this).attr('width');

            if (w) {
                let widthValue = parseInt(w.toString().replace('px', ''));

                if (widthValue > 700) {
                    // scale to safe width
                    $(this).attr('width', "670px");
                }
            }

            // inline style width
            var styleWidth = $(this).css('width');

            if (styleWidth && styleWidth.includes("px")) {
                let wv = parseInt(styleWidth.replace("px", ""));

                if (wv > 700) {
                    $(this).css('width', "670px");
                }
            }

            // display: table often forces wide layout
            if ($(this).css('display') === "table") {
                $(this).css({
                    "max-width": "680px",
                    "width": "100%"
                });
            }
        });

    // ---------------------------------------
    // 🔥 FORCE PAYMENT HISTORY TO NEXT PAGE
    // ---------------------------------------
    $(tempDiv)
        .find('h3')
        .filter(function () {
            return $(this).text().trim().toLowerCase() === "summary";
        })
        .css({
            "page-break-before": "always"
        });

    // PAGE BREAK for "Payment History"
    $(tempDiv)
        .find('h3')
        .filter(function () {
            return $(this).text().trim().toLowerCase() === "payment history";
        })
        .css({
            "page-break-before": "always"
        });

    // ---------------------------------------
    // PDF Options
    // ---------------------------------------
   var identifier = $('#identifier').val();
   var order_number = $('#order_number').val();



    let fileName = `${order_number}-${identifier}.pdf`;
    var opt = {
        margin: 0,
        filename: fileName,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    // html2pdf().set(opt).from(tempDiv).save();

    html2pdf()
        .set(opt)
        .from(tempDiv)
        .save()
        .then(() => hideLoader())
        .catch(() => hideLoader());
});












</script>

<script>
    function showLoader(message = "Processing...") {
        $("#globalLoader").find("div:last").text(message);
        $("#globalLoader").show();
    }

    function hideLoader() {
        $("#globalLoader").hide();
    }
</script>

<script>
    
    function calculateRowTotal(row, hide) {

    let subtotal = 0;
    let withouttax = 0;
    
    // -----------------------------------------
    // 1) PRICING QTY * PRICE
    // -----------------------------------------
    row.querySelectorAll('input[name^="tour_pricing_qty_"]').forEach((qtyInput) => {
        let qty = parseFloat(qtyInput.value) || 0;

        const priceInput = qtyInput.parentElement.querySelector(
            'input[name^="tour_pricing_price_"]'
        );

        const priceTypeInput = qtyInput.parentElement.querySelector(
            'input[name^="tour_pricing_type_"]'
        );

        const price = parseFloat(priceInput.value) || 0;
        const priceType = priceTypeInput.value;

        // -----------------------------------------
        // ADDITION: ENFORCE MIN/MAX IF FIXED
        // -----------------------------------------
        const minQty = qtyInput.getAttribute("min");
        const maxQty = qtyInput.getAttribute("max");


        if (priceType === "FIXED") {

            if (minQty !== null && qty < parseFloat(minQty)) {
                alert("Quantity cannot be less than minimum allowed (" + minQty + ").");
                qty = parseFloat(minQty);
                qtyInput.value = qty;
            }

            if (maxQty !== null && qty > parseFloat(maxQty)) {
                alert("Quantity cannot be more than maximum allowed (" + maxQty + ").");
                qty = parseFloat(maxQty);
                qtyInput.value = qty;
            }

        }
        // -----------------------------------------

        if (priceType === "FIXED") {
            subtotal = price;
        } else {
            subtotal += qty * price;
        }

    });

    // -----------------------------------------
    // 2) ADDONS QTY * PRICE
    // -----------------------------------------
    row.querySelectorAll('input[name^="tour_extra_qty_"]').forEach((qtyInput) => {
        const qty = parseFloat(qtyInput.value) || 0;

        const priceInput = qtyInput.parentElement.querySelector(
            'input[name^="tour_extra_price_"]'
        );

        const price = parseFloat(priceInput.value) || 0;

        subtotal += qty * price;
    });

    withouttax = subtotal;

    // -----------------------------------------
    // 3) TAXES — read tax rows & recalc live
    // -----------------------------------------
    row.querySelectorAll('.tax-row').forEach((taxRow) => {
        const feeType = taxRow.dataset.type;
        const feeValue = parseFloat(taxRow.dataset.value);
        

        let tax = 0;
        
        
        if (feeType === "PERCENT" || feeType === "PERCENT ") {
            
            tax = subtotal * (feeValue / 100);
            
        } else {
            tax = feeValue;
        }

        const formattedTax = new Intl.NumberFormat('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(tax);
        
        taxRow.querySelector('.tax-amount').textContent = formattedTax;

        subtotal += tax;
    });

    // -----------------------------------------
    // 4) UPDATE UI SUBTOTAL
    // -----------------------------------------
    const withouttaxBox = row.querySelector('.withouttax-box');
    if (withouttaxBox) {
        withouttaxBox.textContent = withouttax.toFixed(2);
    }
    const subtotalBox = row.querySelector('.subtotal-box');
    if (subtotalBox) {
        subtotalBox.textContent = subtotal.toFixed(2);
    }
    if(hide){
        $('.cummulative-total').hide();
    }
    
}


// =====================================================
// EVENT LISTENERS — trigger on every quantity change
// =====================================================

$(document).on("input", "input[name^='tour_pricing_qty_'], input[name^='tour_extra_qty_']", function () {

    const row = this.closest("[id^='row_']");
    calculateRowTotal(row, true);
});

$(document).ready(function () {
    $("input[name^='tour_pricing_qty_'], input[name^='tour_extra_qty_']").each(function () {
        const row = this.closest("[id^='row_']");
        calculateRowTotal(row, false);
    });
});

$(document).ready(function () {

    let tourId = $("input[name='tour_id[]']").val();  // from edit row
    let order_id = $("input[name='order_id").val();  // from edit row


    let count  = 0; // or row number if multiple rows

    if (tourId) {
        refreshCalendarAndSession(tourId, count, order_id);
    }
});

function refreshCalendarAndSession23432(tourId, count, order_id) {

    $.ajax({
        url: "{{ route('tour.calendar') }}",
        type: "POST",
        data: {
            id: tourId,
            order_id : order_id,
            _token: "{{ csrf_token() }}"
        },

        success: function(res) {
            const $row = $("#row_" + count);

            // 1. Update date
            // const $dateInput = $row.find("input[name='tour_startdate[]']");

            const $dateInput = $row.find(".tour_startdate");


            $dateInput.val(res.tour_date);

            // Put disabled dates
            $row.find(".disabled-dates").val(JSON.stringify(res.disabled_dates));

            // Re-initialize date picker
            TB.plugins.dateRange();

            setTimeout(() => {
                const drp = $dateInput.data("daterangepicker");

                if (drp) {
                    const initialDate = res.start_date;
                    const today       = moment().startOf('day');
                    const minDate     = moment(initialDate).isAfter(today) 
                        ? moment(initialDate) 
                        : today;

                    drp.minDate = minDate;
                    drp.setStartDate(initialDate);
                    drp.setEndDate(initialDate);
                    drp.updateView();
                    drp.updateCalendars();
                }

                fetchTourSessions(tourId, res.start_date, count);

            }, 200);
        }
    });
}

function refreshCalendarAndSession(tourId, count, order_id) {
    // alert(23432);

    // showLoader("Loading… Please wait");
    $.ajax({
        url: "{{ route('tour.calendar') }}",
        type: "POST",
        data: {
            id: tourId,
            order_id : order_id,
            _token: "{{ csrf_token() }}"
        },

        success: function(res) {

            const $row = $("#row_" + count);
            const $dateInput = $row.find(".tour_startdate");

            // Set initial date
            $dateInput.val(res.tour_date);

            // Set disabled dates
            $row.find(".disabled-dates").val(JSON.stringify(res.disabled_dates));


            const pretty = moment(res.tour_date).format("ddd MMM DD YYYY");
            $row.find(".tour_startdate_display").val(pretty);

            // Reinitialize date picker
            TB.plugins.dateRange();

            // 🔥 ADD THE DATE CHANGE LISTENER HERE
            $dateInput
                .off("apply.daterangepicker")
                .on("apply.daterangepicker", function (ev, picker) {

                    let selectedDate = picker.startDate.format("YYYY-MM-DD");
                    $(this).val(selectedDate).trigger("change");

                    const pretty = moment(selectedDate).format("ddd MMM DD YYYY");
                    $row.find(".tour_startdate_display").val(pretty);

                    fetchTourSessions(tourId, selectedDate, count);
                });

            // Delay only for initial render
            setTimeout(() => {

                const drp = $dateInput.data("daterangepicker");

                if (drp) {
                    // const initialDate = res.start_date;
                    // const today       =  moment().startOf('day');

                    // const minDate = moment(initialDate).isAfter(today)
                    //     ? moment(initialDate)
                    //     : today;

                    // drp.minDate = minDate;
                    // drp.setStartDate(initialDate);
                    // drp.setEndDate(initialDate);
                    // drp.updateView();
                    // drp.updateCalendars();


                    const initialDate = res.tour_date; // KEEP CONSISTENCY
                    const today = moment().startOf("day");

                    // Set min date properly
                    const minDate = moment(res.start_date).isAfter(today)
                        ? moment(res.start_date)
                        : today;

                    // Apply settings to daterangepicker
                    drp.minDate = minDate;
                    drp.setStartDate(initialDate);
                    drp.setEndDate(initialDate);

                    // Move calendar highlight to the correct active date
                    drp.updateElement(); 
                    drp.updateView();
                    drp.updateCalendars();
                }

                

                // Fetch sessions for initial date
                fetchTourSessions(tourId, res.start_date, count, res.tour_time);

            }, 200);

            // hideLoader();
        }
    });
}


function fetchTourSessions(tourId, selectedDate, count, selectedTime =null ) {

    showLoader("Loading… Please wait");
    
   
    // const $container = $(`#tour_details_${count}`);

    const $row = $("#row_" + count);

            // 1. Update date
            // const $dateInput = $row.find("input[name='tour_startdate[]']");

    // const $dateInput = $row.find(".tour_startdate");

    // console.log($container);
    // const $timeField = $container.find("input[name='tour_starttime[]'], select[name='tour_starttime[]']").first();

    const $timeField = $row.find(".tour_starttime, select[name='tour_starttime[]']").first();


    if(!tourId || !selectedDate) return;

    

    $.ajax({
        url: "/admin/tour-sessions",
        type: "GET",
        data: { tour_id: tourId, date: selectedDate },
        dataType: "json",
        success: function(resp) {
            hideLoader();

            
            let options = '';
            if(resp.data && resp.data.length > 0){
                $.each(resp.data, function(i, session){
                    // If your API returns strings, use session; if objects, adapt.
                    options += `<option value="${session}">${session}</option>`;
                });
            } else {
                options = '<option value="">No sessions available</option>';
            }

            // Replace the time field within this container only
            // $timeField.replaceWith(`<select name="tour_starttime[]" class="form-control tour-time">${options}</select>`);

             const newSelect = $(`<select name="tour_starttime[]" class="form-control tour-time">${options}</select>`);

            $timeField.replaceWith(newSelect);
            
            if (selectedTime !== null && selectedTime !== "" && selectedTime !== undefined) {
                newSelect.val(selectedTime);
            }
        },
        error: function(xhr){
            console.error("Failed to fetch sessions:", xhr.responseText);
        }
    });
}

$(document).on('click', '.refund-all-btn', function (e) {
    e.preventDefault();
    $('#refundAllOrderId').val($(this).data('order-id'));
    $('#refundAllAmount').val($(this).data('amount'));
    $('#refundAllModal').modal('show');
});

document.getElementById('refundAllForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const orderId = document.getElementById('refundAllOrderId').value;
    const amount = document.getElementById('refundAllAmount').value;

    const refundMultipleRoute = "{{ route('admin.orders.refundMultiple', ':id') }}";
    const url = refundMultipleRoute.replace(':id', orderId);

    const btn = document.querySelector("button[form='refundAllForm']");
    btn.disabled = true;
    btn.textContent = "Processing...";

    const response = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            amount: amount
        })
    });

    const data = await response.json();
    btn.disabled = false;
    btn.textContent = "Confirm Refund";

    if (data.success) {

        Swal.fire({
            icon: 'success',
            title:'Success',
            text: 'Refund completed successfully!',
        }).then(() => {
            location.reload();
        });

    } else {

        Swal.fire({
            icon: 'Error',
            title:'Error',
            text: data.message,
        }).then(() => {
            location.reload();
        });
        
    }
});




</script>


@endsection
</x-admin>
