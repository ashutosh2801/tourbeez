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
</style>
@endsection
    <form action="{{ route('admin.orders.update',$order->id) }}" method="POST">
    @method('PUT')
    @csrf
    <input type="hidden" name="order_id" value="{{ $order->id }}" /> 
    <div class="card">
        <div class="card-header">
            <h5>Created on {{ date__format($order->created_at) }} online on your booking form</h5>
        </div>
        <div class="card-body">
            <div style="padding:0 15px;">
                <div class="row">
                    <div class="col-lg-2 text-ceneter">
                        <label class="d-block" for="">Balance</label>
                        <select class="form-control" style="border:0" name="order_balance" id="order_balance">
                            <option value="$0.00" >$0.00</option>
                            <option value="Paid $97.07" >Paid $97.07</option>
                            <option value="Refunded $0.00" >Refunded $0.00</option>
                        </select>
                    </div>
                    <div class="col-lg-2 text-ceneter">
                        <label class="d-block" for="">Order Status</label>
                        <select class="form-control" style="border:0" name="order_status" id="order_status">
                            @foreach( order_status_list() as $key => $status)
                            <option value="{{ $key }}" >{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-8 text-right">
                        {{-- <select class="form-control" style="width:150px; display:inline-block;" name="email_template_name" id="email_template_name">
                            <option value="" >Email</option>
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

                        <select class="form-control" style="width:150px; display:inline-block;" name="sms_template_name" id="sms_template_name">
                            <option value="" >SMS</option>
                            <option value="Order Confirmation" >Order Confirmation -> Send Now</option>
                            <option value="Reminder" >Reminder -> Send Now</option>
                            <option value="FollowUp" >FollowUp -> Send Now</option>
                            <option value="Simple" >Simple -> Send Now</option>
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
                        </select> --}}
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
                                    <li><a href="{{ route('admin.user.edit', encrypt($order->user_id) ) }}" class="alink" target="_blank">{{ $order->user?->name }}</a></li>
                                    <li>{{ $order->user?->email }}</li>
                                    <li>{{ $order->user?->phonenumber }}</li>
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
                                                        <td>{{ $pricing->label }} ({{ price_format($price) }})</td>
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
                                                        <td>{{ $extra->name }} ({{ price_format($extra->price) }})</td>
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
                                            <td class="text-right">{{ price_format($tax) }}</td>
                                        </tr>
                                        @endforeach
                                        @endif

                                        <tr>
                                            <th>Subtotal</th>
                                            <th class="text-right"> {{ price_format($subtotal) }} </th>
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
                                            <td class="text-right">$1.84</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total</b></td>
                                            <td class="text-right">{{ price_format($order->total_amount) }}</td>
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
                                <textarea class="form-control" name="additional_info" id="additional_info" rows="4" placeholder="Additional information">{{ $order->additional_info }}</textarea>
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
                                        <td>Total: {{ price_format($order->total_amount) }}</td>
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
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <a style="padding:0.6rem 2rem" href="" class="btn btn-outline-danger">Delete</a>
                    </div>
                </div>
            </div>

            <div class="bs-example">
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
                                <td>System charged CAD97.07 on credit card XXXXXXXXXXXX5959. Reference number is ch_3RSiaLEcMxhlmBMk0dT82PRI</td>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:43 PM</td>
                                <td>Arya Suresh made a new order on your booking form</td>
                            </tr>
                            <tr>
                                <td>May 25, 2025, 1:43 PM</td>
                                <td>Order created with Credit card payment of CAD97.07</td>
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
            </div>
        </div>
    </div>
    </form>
@section('js')   
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
<<<<<<< HEAD
            url: "{{ route('tour.single') }}",
=======
            url: '{{ route('tour.single') }}',
>>>>>>> 8ff6bb47c16adae29e0a83dc2f62fc7b343f06b7
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
@endsection
</x-admin>
