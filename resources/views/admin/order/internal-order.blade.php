<x-admin>
@section('title', 'Internal Orders Create')

<div class="card">
    <form id="orderForm" action="{{ route('admin.orders.store') }}" method="POST">
        @csrf

        <!-- ================= Header ================= -->
        <div class="card-header d-flex justify-content-between align-items-center bg-secondary p-3 mb-3 rounded text-black">
            <div class="form-group">
                <h4 class="m-0">New Order</h4>
                <small>Created by {{ auth()->user()->name }}</small>
            </div>
        </div>

        <!-- ================= Balance + Status ================= -->
        <div class="d-flex justify-content-between align-items-center bg-secondary p-3 mb-3 rounded">
            <div>
                <strong id="totalDue">$0.00</strong><br>
                <small>Balance</small>
            </div>
            <div>
                <select name="order_status" class="form-control">
                    <option value="CONFIRMED" selected>Confirmed</option>
                    <option value="NEW">New</option>
                    <option value="ON_HOLD">On Hold</option>
                    <option value="PENDING_SUPPLIER">Pending Supplier</option>
                    <option value="PENDING_CUSTOMER">Pending Customer</option>
                    <option value="CANCELLED">Cancelled</option>
                    <option value="ABANDONED_CART">Abandoned Cart</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Order</button>
        </div>

        <div class="accordion" id="accordionExample">

            <!-- ================= Customer Details ================= -->
           <!--  <div class="card">
                <div class="card-header bg-secondary py-0" id="headingOne">
                    <h2 class="my-0 py-0">
                        <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                            data-toggle="collapse" data-target="#collapseOne">
                            <i class="fa fa-angle-right"></i> Customer Details
                        </button>                                  
                    </h2>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="customer">Select Customer</label>
                            <select name="customer_id" id="customer" class="form-control aiz-selectpicker" data-live-search="true">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->email }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="card">
<div class="card">
    <div class="card-header bg-secondary py-0" id="headingOne">
        <h2 class="my-0 py-0">
            <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                data-toggle="collapse" data-target="#collapseOne">
                <i class="fa fa-angle-right"></i> Customer Details
            </button>                                  
        </h2>
    </div>
    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
        <div class="card-body">

            {{-- Existing Customer Dropdown --}}
            <div class="form-group">
                <label for="customer">Select Existing Customer</label>
                <select name="customer_id" id="customer" class="form-control aiz-selectpicker" data-live-search="true">
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->email }}</option>
                    @endforeach
                </select>
            </div>

            <div class="text-center my-3">
                <button type="button" id="addNewCustomerBtn" class="btn btn-sm btn-primary">
                    <i class="fa fa-user-plus"></i> Add New Customer
                </button>
            </div>

            {{-- New Customer Fields (hidden by default) --}}
            <div id="newCustomerFields" class="border rounded p-3 d-none bg-light">
                <h5 class="mb-3">New Customer Information</h5>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="customer_first_name">First Name</label>
                        <input type="text" name="customer_first_name" id="customer_first_name" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="customer_last_name">Last Name</label>
                        <input type="text" name="customer_last_name" id="customer_last_name" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="customer_email">Email</label>
                        <input type="email" name="customer_email" id="customer_email" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="customer_phone">Phone</label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="pickup_id">Pickup ID</label>
                        <input type="text" name="pickup_id" id="pickup_id" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="pickup_name">Pickup Name</label>
                        <input type="text" name="pickup_name" id="pickup_name" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="additional_info">Instructions <span class="text-danger">*</span></label>
                    <textarea name="additional_info" id="additional_info" class="form-control" required></textarea>
                </div>
            </div>

        </div>
    </div>
</div>


            <!-- ================= Tour Details ================= -->
            <div class="card">
                <div class="card-header bg-secondary py-0" id="headingTwo">
                    <h2 class="my-0 py-0">
                        <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                            data-toggle="collapse" data-target="#collapseTwo">
                            <i class="fa fa-angle-down"></i> Tour Details
                        </button>
                    </h2>
                </div>
                <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                        <div id="tourContainer"></div>
                        <button type="button" onclick="addTour()" class="btn btn-info mb-2">+ Add Tour</button>
                    </div>
                </div>
            </div>

            <!-- ================= Additional Information ================= -->
            <div class="card">
                <div class="card-header bg-secondary py-0" id="headingFour">
                    <h2 class="my-0 py-0">
                        <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                            data-toggle="collapse" data-target="#collapseFour">
                            <i class="fa fa-angle-right"></i> Additional Information
                        </button>
                    </h2>
                </div>
                <div id="collapseFour" class="collapse show" aria-labelledby="headingFour" data-parent="#accordionExample">
                    <div class="card-body">
                        <textarea class="form-control" name="additional_info" rows="4" placeholder="Additional information"></textarea>
                    </div>
                </div>
            </div>

            <!-- ================= Payment Details ================= -->
            <div class="card">
                <div class="card-header bg-secondary py-0" id="headingThree">
                    <h2 class="my-0 py-0">
                        <button type="button" class="btn btn-link collapsed fs-21 py-0 px-0" 
                            data-toggle="collapse" data-target="#collapseThree">
                            <i class="fa fa-angle-right"></i> Payment Details
                        </button>                     
                    </h2>
                </div>

                <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordionExample">
                    <div class="card-body">

                        {{-- Choose Payment Option --}}
                        <div class="form-group">
                            <label><strong>Payment Method</strong></label><br>
                            <label class="mr-3">
                                <input type="radio" name="payment_type" value="card" checked> Credit Card (Stripe)
                            </label>
                            <label>
                                <input type="radio" name="payment_type" value="transaction"> Transaction
                            </label>
                        </div>

                        {{-- Stripe Credit Card Fields --}}
                        <div id="cardFields">
                            <div class="form-group">
                                <label for="card-element">Card Details</label>
                                <div id="card-element" class="form-control" style="padding: 10px; height: auto;"></div>
                                <small id="card-errors" class="text-danger mt-2"></small>
                            </div>
                        </div>

                        {{-- Transaction Fields (inline) --}}
                        <div id="transactionFields" style="display:none;">
                            <div class="form-row align-items-center">
                                <div class="form-group col-md-6">
                                    <label for="transaction_id">Transaction ID</label>
                                    <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction ID">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="payment_method">Payment Type</label>
                                    <select name="payment_method" class="form-control">
                                        <option value="">Select Type</option>
                                        <option value="stripe">Stripe</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="bank">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= Form Actions ================= -->
            <div class="card-footer" style="display:block">
                <button style="padding:0.6rem 2rem" type="submit" id="createOrderBtn" class="btn btn-success">Create Order</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

@section('js')
<script>
let tourCount = 1;

// ================= Tour Options =================
function tourOptions() {
    let options = '';
    @foreach($tours as $tour)
        options += `<option value="{{ $tour->id }}">{{ $tour->title }}</option>`;
    @endforeach
    return options;
}

// ================= Add Tour Row =================
function addTour() {
    const container = document.getElementById('tourContainer');
    const newRow = document.createElement('div');
    newRow.setAttribute('id', `row_${tourCount}`);

    newRow.innerHTML = `
    <div style="border:1px solid #ccc; margin-bottom:10px; padding:10px">
        <table class="table">
            <tr>
                <td>
                    <select onchange="loadTourDetails(this.value, ${tourCount})" 
                        name="tour_id[]" 
                        class="form-control aiz-selectpicker" data-live-search="true">
                        <option value="">Select Tour</option>` + tourOptions() + `</select>
                </td>
                <td>
                    <button type="button" onclick="removeTour('row_${tourCount}')" class="btn btn-danger">Remove</button>
                </td>
            </tr>
        </table>
        <div id="tour_details_${tourCount}"></div>
    </div>`;

    container.appendChild(newRow);
    TB.plugins.bootstrapSelect('refresh');
    tourCount++;
}

// ================= Remove Tour Row =================
function removeTour(id) {
    const row = document.getElementById(id);
    if(row) row.remove();
}

// ================= Load Single Tour Details =================
function loadTourDetails(tourId, count) {
    if(!tourId) return;

    $.ajax({
        url: '{{ route("tour.single") }}',
        type: 'POST',
        data: { id: tourId, tourCount: count, _token: '{{ csrf_token() }}' },
        success: function(response){
            const $container = $(`#tour_details_${count}`);
            $container.html(response);

            TB.plugins.dateRange();
            TB.plugins.timePicker();
            TB.plugins.bootstrapSelect('refresh');

            $container.find(".aiz-date-range").each(function(){
                $(this).off("apply.daterangepicker").on("apply.daterangepicker", function(ev, picker){
                    const selectedDate = picker.startDate.format("YYYY-MM-DD");
                    $('#tour_startdate').val(selectedDate).trigger('change');
                    
                    fetchTourSessions(tourId, selectedDate, count);
                });
            });
        },
        error: function(err){
            console.error(err);
        }
    });
}

// ================= Fetch Tour Sessions =================
function fetchTourSessions(tourId, selectedDate, count) {
    const $container = $(`#tour_details_${count}`);
    const $timeField = $container.find("input[name='tour_starttime[]'], select[name='tour_starttime[]']");

    if(!tourId || !selectedDate) return;

    $.ajax({
        url: "/admin/tour-sessions",
        type: "GET",
        data: { tour_id: tourId, date: selectedDate },
        dataType: "json",
        success: function(resp) {
            let options = '<option value="">-- Select Session --</option>';
            if(resp.data && resp.data.length > 0){
                $.each(resp.data, function(i, session){
                    options += `<option value="${session}">${session}</option>`;
                });
            } else {
                options = '<option value="">No sessions available</option>';
            }

            $timeField.replaceWith(`<select name="tour_starttime[]" class="form-control">${options}</select>`);
        },
        error: function(xhr){
            console.error("Failed to fetch sessions:", xhr.responseText);
        }
    });
}
</script>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ env('STRIPE_KEY') }}");
    const elements = stripe.elements();
    const style = {
        base: { fontSize: '16px', color: '#32325d', fontFamily: 'Arial, sans-serif' },
        invalid: { color: '#fa755a' }
    };
    const card = elements.create('card', { style });
    card.mount('#card-element');

    card.on('change', function(event) {
        document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
    });

    // Handle form submit
    const form = document.getElementById('orderForm');
    form.addEventListener('submit', async function(event) {
        const selectedPayment = document.querySelector("input[name='payment_type']:checked").value;
        if (selectedPayment === "card") {
            event.preventDefault();

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: card,
            });

            if (error) {
                document.getElementById('card-errors').textContent = error.message;
            } else {
                let hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'payment_intent_id');
                hiddenInput.setAttribute('value', paymentMethod.id);
                form.appendChild(hiddenInput);
                form.submit();
            }
        }
    });

    // Toggle fields
    document.querySelectorAll("input[name='payment_type']").forEach(el => {
        el.addEventListener("change", function() {
            if (this.value === "card") {
                document.getElementById("cardFields").style.display = "block";
                document.getElementById("transactionFields").style.display = "none";
            } else {
                document.getElementById("cardFields").style.display = "none";
                document.getElementById("transactionFields").style.display = "block";
            }
        });
    });
    $(document).ready(function () {
        $('#addNewCustomerBtn').on('click', function () {
            $('#newCustomerFields').removeClass('d-none');
            $('#customer').val('').trigger('change'); // clear existing dropdown
            $('#additional_info').attr('required', true); // make message field required
        });

        $('#customer').on('change', function () {
            if ($(this).val()) {
                // If existing customer selected → hide new fields
                $('#newCustomerFields').addClass('d-none');
                $('#additional_info').attr('required', false); // remove required
            }
        });
    });

</script>
@endsection
</x-admin>
