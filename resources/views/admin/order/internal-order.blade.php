<x-admin>
@section('title', 'Internal Orders Create')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/css/intlTelInput.css"/>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/utils.js"></script>

<style>
.iti { width: 100%; }

/* Fix flags */
.iti__flag {
    background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/img/flags.png");
}
.iti__flag.iti__flag--2x {
    background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/img/flags@2x.png");
}

/* Search box styling */
.iti__search-box {
    padding: 8px;
    border-bottom: 1px solid #ddd;
}
.iti__search-input {
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>

    @if ($errors->any())
    <div class="alert alert-danger mb-4 p-3 rounded">
        <ul class="mb-0 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    <div class="internal-order-body">
        <!-- ================= Header ================= -->
        <div class="card-primary mb-3">
            <div class="card-header internal-order-head">
                <div class="row">
                    <div class="col-12">
                        <h3 class="card-title text-white w-full">New Order</h3>
                        <small>Created by {{ auth()->user()->name }}</small>
                    </div>
                </div>
            </div>
        </div>

        <form id="orderForm" action="{{ route('admin.orders.store') }}" method="POST">
            @csrf        

            <!-- ================= Balance + Status ================= -->
            <div class="d-flex justify-content-between align-items-center rounded-lg-custom balance-bar border">
                <div>
                    <strong id="totalDue">0.00</strong>
                    <small>Balance</small>
                </div>
                
                <div class="d-flex">
                    <!-- <input type="hidden" name="currency" id="order_currency" value="CAD" /> -->
                   <select readonly name="currency" id="order_currency" class="form-control mr-2">
                        @foreach(config('constants.currencies') as $code => $country)
                            <option @if($code === 'CAD') selected @endif value="{{ $code }}">{{ $code }} - {{ $country }}</option> 
                        @endforeach
                    </select> 
                    <select name="order_status" class="form-control mr-2">
                        <option value="0">New</option> 
                        <option value="4" selected>Pending Customer</option>
                        <option value="3">Pending Supplier</option>
                        <option value="5" >Confirmed</option>
                        <option value="2">On Hold</option>
                        <option value="6">Cancelled</option>
                        <option value="7">Abandoned Cart</option>
                    </select>
                    <!-- <button type="submit" class="btn btn-success w-full">+ Create Order</button> -->
                </div>
            </div>
            
            <div class="accordion" id="accordionExample">         
                <div class="card card-primary rounded-lg-custom border">
                    <div class="card-header order-heads py-0 z-10" id="headingOne">
                        <h2 class="my-0 py-0">
                            <button type="button" class="btn btn-link collapsed py-0 px-0 text-white" 
                                data-toggle="collapse" data-target="#collapseOne">
                                <i class="fa fa-angle-right"></i> Customer Details
                            </button>                                  
                        </h2>
                    </div>
                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                        <div class="card-body">

                            {{-- Existing Customer Dropdown --}}
                            <div class="row d-flex justify-content-between align-items-center">
                                <div class="form-group col-md-5">
                                    <label for="customer">Select Existing Customer</label>
                                    <select name="customer_id" id="customer" class="form-control aiz-selectpicker z-100 border" data-live-search="true">
                                        <option value="">-- Select Customer --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ ucwords($customer->name) }} - {{ $customer->email }} - {{ $customer->phone ?? 'NA' }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-group text-center font-thin text-md mt-3">  OR</div>
                                    <div class="text-center">
                                        <button type="button" id="addNewCustomerBtn" class="btn btn-md btn-success">
                                            <i class="fa fa-user-plus mr-1"></i> Add New Customer
                                        </button>
                                    </div>
                                </div>                            
                            </div>

                            {{-- New Customer Fields (hidden by default) --}}
                            <div id="newCustomerFields" class="border rounded p-3 d-none bg-light">
                                <h5 class="cus-info-head">New Customer Information</h5>
                                <div class="form-group">
                                    <input type="checkbox" name="addToCustomer" id="addToCustomer" />
                                    <label for="addToCustomer">Add to Customer</label>
                                </div>
                                <div class="form-row"> 
                                    <div class="form-group col-lg-3 col-md-6">
                                        <label for="customer_first_name">First Name *</label>
                                        <input type="text" name="customer_first_name" id="customer_first_name"
                                            class="form-control" minlength="2">
                                        <small class="text-danger d-none" id="error_first_name">Enter a valid first name</small>
                                    </div>

                                    <div class="form-group col-lg-3 col-md-6">
                                        <label for="customer_last_name">Last Name *</label>
                                        <input type="text" name="customer_last_name" id="customer_last_name"
                                            class="form-control" minlength="2">
                                        <small class="text-danger d-none" id="error_last_name">Enter a valid last name</small>
                                    </div>

                                    <div class="form-group col-lg-3 col-md-6">
                                        <label for="customer_email">Email *</label>
                                        <input type="email" name="customer_email" id="customer_email"
                                            class="form-control" >
                                        <small class="text-danger d-none" id="error_email">Enter a valid email</small>
                                    </div>

                                    

                                    <div class="form-group col-lg-3 col-md-6">
                                        <label for="customer_phone">Phone (with country code) *</label>
                                        <input 
                                            id="customer_phone"
                                            name="customer_phone"
                                            type="tel"
                                            class="form-control"
                                            
                                        />
                                        <!-- Hidden field that stores full E.164 number -->
                                        

                                        <small class="text-danger d-none" id="error_phone">Invalid phone number</small>
                                        <input type="hidden" name="full_phone" id="full_phone">
                                    </div>




                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- ================= Tour Details ================= -->
                <div class="card card-primary rounded-lg-custom border tour-detail">
                    <div class="card-header order-heads py-0" id="headingTwo">
                        <h2 class="my-0 py-0">
                            <button type="button" class="btn btn-link collapsed py-0 px-0 text-white" 
                                data-toggle="collapse" data-target="#collapseTwo">
                                <i class="fa fa-angle-right"></i> Tour Details
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <td style="border: none; padding: 0;">
                                        <select 
                                            onchange="loadTourDetails(this.value, 0)"
                                            name="tour_id0" 
                                            class="form-control col-12 col-md-6 aiz-selectpicker border" data-live-search="true">
                                            <option value="">Select Tour</option>
                                            @foreach($tours as $tour)
                                                <option value="{{ $tour->id }}">{{ $tour->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div id="tour_details_0"></div>
                            <div id="tourContainer"></div>
                            <button type="button" onclick="addTour()" class="btn btn-md btn-success px-5 mt-3">+ Add Tour</button>
                        </div>
                    </div>
                </div>

                <!-- ================= Additional Information ================= -->
                <div class="card card-primary rounded-lg-custom border">
                    <div class="card-header order-heads py-0" id="headingFour">
                        <h2 class="my-0 py-0">
                            <button type="button" class="btn btn-link collapsed py-0 px-0 text-white"
                                data-toggle="collapse" data-target="#collapseFour">
                                <i class="fa fa-angle-right"></i> Additional Information
                            </button>
                        </h2>
                    </div>
                    <div id="collapseFour" class="collapse show" aria-labelledby="headingFour" data-parent="#accordionExample">
                        <div class="card-body row">
                            <div class="col-12 col-md-6">
                                <textarea class="form-control" name="additional_info" rows="2" placeholder="Add Special Requirements"></textarea>
                                <p style="color:#777;font-size:14px">Special Requirements visible by everybody</p>
                            </div>
                            <div class="col-12 col-md-6">
                                <textarea class="form-control" name="internal_notes" rows="2" placeholder="Add  Internal Notes"></textarea>
                                <p style="color:#777;font-size:14px">Internal Notes only visible by supplier</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= Customer Payment ================= -->
                <div class="card card-primary rounded-lg-custom border">
                    <div class="card-header order-heads py-0" id="headingThree">
                        <h2 class="my-0 py-0">
                            <button type="button" class="btn btn-link collapsed py-0 px-0 text-white" 
                                data-toggle="collapse" data-target="#collapseThree">
                                <i class="fa fa-angle-right"></i> Customer Payment
                            </button>                     
                        </h2>
                    </div>

                    <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordionExample">

                        <div class="card-total p-3 mb-3" style="background: #edf3ff;">
                            Total: <b id="totalPayment">0.00</b>
                            <input type="text" id="total_amount" class="form-control" readonly placeholder="0.00">

                        </div>
                        <div class="card-body pt-0">

                            <div>
                                <div class="mb-2"><label><input type="checkbox" value="1" name="add_ccnow" id="add_ccnow" > Add a credit card to this order</label></div>

                                <div id="card-element-wrapper" class="hidden">
                                    <div id="card-element" class="form-control col-6" style="padding: 10px; height: auto;"></div>

                                    <div class="mt-3"><label><input type="checkbox" value="1" name="charge_ccnow" id="charge_ccnow" /> Charge credit card now</label></div>

                                    <div class="form-group hidden" id="charge_ccnow_amount">
                                        <div class="form-group  col-6">
                                            <label>Amount</label>
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                </div>    
                                                <input type="text" class="form-control decimal" id="addPaymentAmount" name="charge_ccnow_amount" placeholder="0.00">                                            
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded p-3 bg-light">                                   

                                <div id="paymentTemplate">
                                    <div class="row paymentRow my-2 border-b-1 border-blue-300">
                                        <div class="col-12 col-md-2">
                                            <select class="form-control" name="paymentType[]">
                                                <option value="">Payment type...</option>
                                                <option value="CASH">Cash</option>
                                                <option value="CREDITCARD">Credit Card</option>
                                                <option value="ALIPAY">Alipay</option>
                                                <option value="BANKTRANSFER">Bank Transfer</option>
                                                <option value="BANKCHEQUE">Cheque</option>
                                                <option value="REFUND">Refund</option>
                                                <option value="PAYPAL">Paypal</option>
                                                <option value="VOUCHER">Voucher</option>
                                                <option value="PROMO_CODE">Promo code</option>
                                                <option value="FREE">Free of charge</option>
                                                <option value="INVOICE">Invoice</option>
                                                <option value="OTHER">Other</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-3">
                                            <input class="form-control" name="transactionId[]" placeholder="Ref. number" autocomplete="off" />
                                        </div>

                                        <div class="col-12 col-md-3">
                                            <div class="input-group">
                                                <input type="text" class="aiz-date-range form-control"
                                                    name="collection_date[]" data-format="ddd MMM DD, YYYY" data-single="true" autocomplete="off" placeholder="Date">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-2">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="amount[]" placeholder="0.00" autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-2 text-right">
                                            <button type="button" class="btn btn-success btn-sm addRow">+</button>
                                            <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="paymentWrapper"></div>


                            </div>

                        </div>
                    </div>
                </div> 

                <!-- ================= Form Actions ================= -->
                <div class="card card-primary card-footer rounded-lg-custom border" style="display:block">
                    <button style="padding:0.6rem 2rem" type="submit" id="createOrderBtn" class="btn btn-success">+ Create Order</button>
                    <a style="padding:0.6rem 2rem" href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>

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
    </div>
@section('js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/intlTelInput.min.js"></script>

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

function addTour(savedTourId = null, index = null, silentMode = false) {

    showLoader("Loading… Please wait");

    // If index not provided → add new row
    if (index === null) {
        index = tourCount;
    }

    const container = document.getElementById('tourContainer');
    const newRow = document.createElement('div');
    newRow.setAttribute('id', `row_${index}`);

    newRow.innerHTML = `
    <div style="border:1px solid #ccc; margin-bottom:10px; padding:10px">
        <table class="table" width="100%">
            <tr>
                <td width="90%" style="border: none; padding: 0;">
                    <select 
                        onchange="loadTourDetails(this.value, ${index})"
                        name="tour_id[${index}]" 
                        class="form-control aiz-selectpicker border" data-live-search="true">
                        <option value="">Select Tour</option>` + tourOptions() + `</select>
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    <button type="button" onclick="removeTour('row_${index}')" class="btn btn-danger">Remove</button>
                </td>
            </tr>
        </table>
        <div id="tour_details_${index}"></div>
    </div>`;

    container.appendChild(newRow);

    TB.plugins.bootstrapSelect('refresh');

    // Restore selected tour (if coming from localStorage)
    if (savedTourId) {
        newRow.querySelector(`select[name="tour_id[${index}]"]`).value = savedTourId;
        newRow.querySelector(`select[name="tour_id[${index}]"]`)
            .dispatchEvent(new Event("change"));
    }

    // Increase global counter only for user-added rows
    if (!silentMode) {
        tourCount++;
    }

    hideLoader();
}

// ================= Remove Tour Row =================
function removeTour(id) {
    const row = document.getElementById(id);
    if(row) row.remove();
}

// ================= Load Single Tour Details =================
function loadTourDetails(tourId, count) {

    if (!tourId) return;

    showLoader("Loading… Please wait");

    $.ajax({
        url: '{{ route("admin.tour.single") }}',
        type: 'POST',
        data: { id: tourId, tourCount: count, order_currency: document.getElementById('order_currency').value, _token: '{{ csrf_token() }}' },

        success: function(response) {

            const $container = $(`#tour_details_${count}`);
            $container.html(response);

            TB.plugins.dateRange();
            TB.plugins.timePicker();
            TB.plugins.bootstrapSelect('refresh');

            const $dateInput = $container.find(
                '.tour-startdate, .tour_startdate_field, input[name="tour_startdate[]"]'
            ).first();

            if ($dateInput.length) {

                const serverDate =
                    $dateInput.attr('value') ||
                    $dateInput.val() ||
                    '';

                const initialDate = serverDate
                    ? serverDate
                    : moment().format("YYYY-MM-DD");

                $dateInput.val(initialDate);

                $dateInput.off('apply.daterangepicker').on('apply.daterangepicker', function(ev, picker) {
                    const selectedDate = picker.startDate.format("ddd MMM DD, YYYY");
                    $(this).val(selectedDate).trigger('change');

                    const $row = $("#row_" + count);

                    const pretty = moment(selectedDate).format("ddd MMM DD, YYYY");
                    $row.find(".tour_startdate_display").val(pretty);
                    
                    fetchTourSessions(tourId, selectedDate, count);
                });

                setTimeout(() => {
                    try {
                        const drp = $dateInput.data('daterangepicker');
                        if (drp) {

                            // ----------- LIMIT START DATE -------------
                            const tourStartDate = moment(initialDate, "YYYY-MM-DD");
                            const today = moment().startOf('day');

                            const minAllowedDate = moment.max(tourStartDate, today);

                            drp.minDate = minAllowedDate;
                            drp.updateView();
                            drp.updateCalendars();
                            // -------------------------------------------

                            drp.setStartDate(initialDate);
                            drp.setEndDate(initialDate);
                        }
                    } catch (e) {}

                    fetchTourSessions(tourId, initialDate, count);
                    hideLoader();

                }, 250);
                $("input[name^='tour_pricing_qty_'], input[name^='tour_extra_qty_']").each(function () {
                    handleQtyInput.call(this);
                });

            } else {
                console.warn("Date input NOT FOUND for row:", count);
            }
        },

        error: function(err) {
            console.error(err);
        }
    });
}

function handleQtyInput() {
    const row = this.closest("[id^='row_']");
    calculateRowTotal(row);
}

$(document).ready(function () {
    $(document).on("change", "#add_ccnow", function () {
        if (this.checked) {
            $("#card-element-wrapper").show();
        } else {
            $("#card-element-wrapper").hide();
        }
    });
    $(document).on("change", "#charge_ccnow", function () {
        if (this.checked) {
            $("#charge_ccnow_amount").show();
        } else {
            $("#charge_ccnow_amount").hide();
        }
    });
});

// ================= Fetch Tour Sessions =================
function fetchTourSessions(tourId, selectedDate, count) {
    const $container = $(`#tour_details_${count}`);
    const $timeField = $container.find("input[name='tour_starttime[]'], select[name='tour_starttime[]']").first();

    if(!tourId || !selectedDate) return;
    showLoader("Loading… Please wait");
    const $row = $("#row_" + count);
    const pretty = moment(selectedDate).format("ddd MMM DD YYYY");
    $row.find(".tour_startdate_display").val(pretty);
    $.ajax({
        url: "{{ route('admin.tour.sessions') }}",
        type: "GET",
        data: { tour_id: tourId, date: selectedDate },
        dataType: "json",
        success: function(resp) {

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
            $timeField.replaceWith(`<select name="tour_starttime[]" class="form-control tour-time">${options}</select>`);
            hideLoader();
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

    function validatePricing() {

    let hasValidPricing = false;

    document.querySelectorAll("[id^='row_']").forEach((row) => {

        let rowValid = false;

        row.querySelectorAll("input[name^='tour_pricing_qty_']").forEach((qtyInput) => {

            const qty = parseFloat(qtyInput.value) || 0;
            const min = parseFloat(qtyInput.dataset.min) || 0;
            const isOptional = qtyInput.dataset.optional == "1";

            // 🚨 KEY RULE
            // Only NON-OPTIONAL can satisfy
            if (!isOptional) {
                if (qty >= min && qty > 0) {
                    rowValid = true;
                }
            }
        });

        if (rowValid) {
            hasValidPricing = true;
        }
    });

    return hasValidPricing;
}

    
</script>

<script>
document.addEventListener("click", function(e) {
    // Add row
    if (e.target.classList.contains("addRow")) {
        let html = document.querySelector("#paymentTemplate").innerHTML;
        document.querySelector("#paymentWrapper").insertAdjacentHTML("beforeend", html);

        // re-init datepicker if needed
        if ($(".aiz-date-range").length) {
            $(".aiz-date-range").daterangepicker({
                singleDatePicker: true,      // Enable single-date mode
                showDropdowns: true,
                locale: {
                    format: 'ddd MMM DD, YYYY'
                }
            });
        }
    }

    // Remove row
    if (e.target.classList.contains("removeRow")) {
        let row = e.target.closest(".paymentRow");
        row.remove();
    }
});
</script>

<script>
function calculateTotal() {
    let sum = 0;

    $('input[name="amount[]"]').each(function () {
        let val = parseFloat($(this).val());
        if (!isNaN(val)) {
            sum += val;
        }
    });

    $('#total_amount').val(sum.toFixed(2));
    $('#totalDue').text(sum.toFixed(2));    
}

    document.querySelectorAll("input[name='payment_type']").forEach(el => {
        el.addEventListener("click", function () {
            if (this.value === "card") {
                cardFields.style.display = "block";
                transactionFields.style.display = "none";
                cashFields.style.display = "none";
            } else if (this.value === "transaction") {
                cardFields.style.display = "none";
                transactionFields.style.display = "block";
                cashFields.style.display = "none";
            } else if (this.value === "other") {
                cardFields.style.display = "none";
                transactionFields.style.display = "none";
                cashFields.style.display = "block";
            }
        });
    });
    
    $(document).ready(function () {
        $(document).on('click', '#addNewCustomerBtn', function () {
            
             $('#newCustomerFields').removeClass('d-none');
            $('#newCustomerFields').removeClass('d-none');
            $('#customer').val('').trigger('change');

            $("#customer_first_name").prop("required", true);
            $("#customer_last_name").prop("required", true);
            $("#customer_email").prop("required", true);
            $("#customer_phone").prop("required", true);
        });

        $('#customer').on('change', function () {
            if ($(this).val()) {
                // If existing customer selected → hide new fields
                $('#newCustomerFields').addClass('d-none');
                $("#customer_first_name").prop("required", false);
                $("#customer_last_name").prop("required", false);
                $("#customer_email").prop("required", false);
                $("#customer_phone").prop("required", false);
                // remove required
            }
        });

        $(document).on('blur', '.decimal', function () {
            let val = $(this).val();

            // If empty, do nothing
            if (val === "") return;

            // Convert to number and force 2 decimals
            let num = parseFloat(val);

            // If not a valid number → reset to empty or 0.00 (your choice)
            if (isNaN(num)) {
                $(this).val("");
                return;
            }

            // Set back with 2 decimals
            $(this).val(num.toFixed(2));
        });

        $(document).on('blur', 'input[name="amount[]"]', function () {
            let val = $(this).val().trim();

            if (val === "") {
                calculateTotal();
                return;
            }

            let num = parseFloat(val);

            if (isNaN(num)) {
                $(this).val("");
                calculateTotal();
                return;
            }

            $(this).val(num.toFixed(2));
            calculateTotal();
        });
    });
</script>


<script>
document.addEventListener("change", function(e){
    if(e.target.classList.contains("pickup-dropdown")) {
        const otherBox = document.getElementById("pickup-other-box");
        if(e.target.value === "other") {
            otherBox.style.display = "block";
            otherBox.value = "";
        } else {
            otherBox.style.display = "none";
            otherBox.value = " ";
        }
    }
});
</script>

<script>
// =====================================================
// DYNAMIC TOTAL CALCULATION FOR EACH TOUR ROW
// =====================================================


function calculateRowTotal(row) {

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

        if (feeType === "PERCENT") {
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
        document.getElementById("totalDue").innerText = subtotal.toFixed(2);
        document.getElementById("totalPayment").innerText = subtotal.toFixed(2);
        document.getElementById("addPaymentAmount").value = subtotal.toFixed(2);
        subtotalBox.textContent = subtotal.toFixed(2);
    }
}

// =====================================================
// EVENT LISTENERS — trigger on every quantity and extra change
// =====================================================
$(document).on("input", "input[name^='tour_pricing_qty_'], input[name^='tour_extra_qty_']", function () {
    const row = this.closest("[id^='row_']");
    calculateRowTotal(row);
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
function autoPersistForm(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    const STORE_KEY = form.id + "_formdata";

    // Pull saved data safely
    let saved = {};
    try {
        saved = JSON.parse(localStorage.getItem(STORE_KEY) || "{}");
    } catch (e) {
        localStorage.removeItem(STORE_KEY);
        saved = {};
    }

    // ==========================================
    // 1. Detect all dynamic tour indexes safely
    // Only match keys that EXACTLY end with [number]
    // ==========================================
    const indexedKeys = Object.keys(saved).filter(k => /\[\d+\]$/.test(k));

    // Extract all index numbers
    const indexList = [...new Set(
        indexedKeys
            .map(k => {
                const m = k.match(/\[(\d+)\]$/);
                return m ? parseInt(m[1], 10) : null;
            })
            .filter(i => i !== null)
    )];

    // Number of dynamic rows last time
    const dynamicCount = indexList.length;

    // ==========================================
    // 2. Restore dynamic tour rows (if addTour exists)
    // ==========================================
    let dynamicReady = Promise.resolve();

    if (dynamicCount > 0 && typeof addTour === "function") {

        dynamicReady = new Promise(resolve => {

            let current = 0;

            function addNext() {
                if (current >= dynamicCount) return resolve();

                // We only need tour_id for row creation
                const tourId = saved[`tour_id[${current}]`] || null;

                // Create the row (silent mode)
                addTour(tourId, current + 1, true);

                current++;

                // Give AJAX time to load row content
                setTimeout(addNext, 350);
            }

            addNext();
        });
    }

    // ==========================================
    // 3. After rows exist → restore field values
    // ==========================================
    dynamicReady.then(() => {

        setTimeout(() => {

            Object.entries(saved).forEach(([name, value]) => {

                const fields = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);
                if (!fields.length) return;

                fields.forEach(field => {
                    if (field.type === "checkbox" || field.type === "radio") {
                        field.checked = value;
                    } else {
                        field.value = value;
                    }

                    field.dispatchEvent(new Event("change"));
                });
            });

        }, 400); // ensure AJAX/DOM are ready
    });

    // ==========================================
    // 4. Save data before submission
    // ==========================================
    form.addEventListener("submit", () => {

        const data = {};

        [...form.elements].forEach(el => {
            if (!el.name) return;

            const key = el.name;

            if (el.type === "checkbox" || el.type === "radio") {
                data[key] = el.checked;
            } else {
                data[key] = el.value;
            }
        });

        localStorage.setItem(STORE_KEY, JSON.stringify(data));
    });

    // ==========================================
    // 5. Clear saved data if PHP says no errors
    // Set window.hasFormError = true on error pages
    // ==========================================
    if (window.hasFormError === false) {
        localStorage.removeItem(STORE_KEY);
    }
}
</script>

<script>
    window.hasFormError = @json($errors->any() || count(old()) > 0);   
    console.log("hasFormError:", window.hasFormError, "old values:", @json(old()));
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    
    if (window.hasFormError) {
        showLoader('Please wait...');
        // Validation failed → restore old values
        autoPersistForm("#orderForm");

        hideLoader();
    } else {
        // Validation passed → clear old saved values
        const STORE_KEY = "orderForm_formdata"; // form id + "_formdata"
        localStorage.removeItem(STORE_KEY);
    }
});

document.getElementById('order_currency').addEventListener('change', function () {

    const newCurrency = this.value;

    // Loop all selected tours
    document.querySelectorAll("select[name^='tour_id']").forEach(function(select) {
        
        const tourId = select.value;
        if (!tourId) return;
        
        
        loadTourDetails(tourId, 0);
        
    });

});

</script>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const phoneInput = document.querySelector("#customer_phone");
    const hiddenInput = document.querySelector("#full_phone");
    const errorBox = document.querySelector("#error_phone");
    const form = document.getElementById("orderForm");

    let isNewCustomer = false; // 🔥 KEY FLAG

    /* =========================================
       INIT INTL TEL INPUT
    ========================================= */
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "ca",
        separateDialCode: true,
        nationalMode: false,
        formatOnDisplay: true,
        autoPlaceholder: "aggressive",
        dropdownContainer: document.body,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/utils.js",
    });

    /* =========================================
       ADD NEW CUSTOMER CLICK
    ========================================= */
    $(document).on('click', '#addNewCustomerBtn', function () {

        isNewCustomer = true; // ✅ enable validation

        $('#newCustomerFields').removeClass('d-none');
        $('#customer').val('').trigger('change');

        $("#customer_first_name").prop("required", true);
        $("#customer_last_name").prop("required", true);
        $("#customer_email").prop("required", true);
        $("#customer_phone").prop("required", true);
    });

    /* =========================================
       EXISTING CUSTOMER SELECT
    ========================================= */
    $('#customer').on('change', function () {

        if ($(this).val()) {
            isNewCustomer = false; // ❌ disable validation

            $('#newCustomerFields').addClass('d-none');

            $("#customer_first_name").prop("required", false);
            $("#customer_last_name").prop("required", false);
            $("#customer_email").prop("required", false);
            $("#customer_phone").prop("required", false);

            // 🔥 clear phone errors
            errorBox.classList.add("d-none");
            phoneInput.classList.remove("is-invalid");
            hiddenInput.value = "";
        }
    });

    /* =========================================
       SEARCH BOX (FIXED + TYPING WORKS)
    ========================================= */
    phoneInput.addEventListener("open:countrydropdown", function () {

        setTimeout(() => {
            const dropdown = document.querySelector(".iti__country-list");
            if (!dropdown) return;

            // prevent duplicate
            if (dropdown.querySelector(".iti__search-box")) return;

            const searchBox = document.createElement("div");
            searchBox.className = "iti__search-box";
            searchBox.style.cssText = `
                padding:8px;
                border-bottom:1px solid #ddd;
                background:#fff;
                position:sticky;
                top:0;
                z-index:2;
            `;

            const input = document.createElement("input");
            input.type = "text";
            input.placeholder = "Search country...";
            input.className = "iti__search-input";
            input.style.cssText = `
                width:100%;
                padding:6px;
                border:1px solid #ccc;
                border-radius:4px;
            `;

            searchBox.appendChild(input);
            dropdown.prepend(searchBox);

            const countries = dropdown.querySelectorAll(".iti__country");

            /* =========================================
               🔥 CRITICAL FIX: STOP DROPDOWN CLOSE
            ========================================= */
            ["click", "mousedown", "mouseup", "keydown"].forEach(evt => {
                input.addEventListener(evt, function (e) {
                    e.stopPropagation();
                });
            });

            /* =========================================
               SEARCH FILTER
            ========================================= */
            input.addEventListener("input", function () {
                const value = this.value.toLowerCase();

                countries.forEach(country => {
                    const text = country.innerText.toLowerCase();
                    country.style.display = text.includes(value) ? "" : "none";
                });
            });

            input.focus();

        }, 50);
    });

    /* =========================================
       INPUT SANITIZATION (ONLY DIGITS)
    ========================================= */
    phoneInput.addEventListener("input", function () {
        phoneInput.value = phoneInput.value.replace(/[^\d]/g, '');

        // limit max 15 digits
        if (phoneInput.value.length > 15) {
            phoneInput.value = phoneInput.value.slice(0, 15);
        }
    });

    /* =========================================
       VALIDATION FUNCTION
    ========================================= */
    function validatePhone() {

        // ✅ skip validation if NOT new customer
        if (!isNewCustomer) return true;

        const value = phoneInput.value.trim();

        if (!value) {
            errorBox.classList.remove("d-none");
            errorBox.innerText = "Phone number is required";
            phoneInput.classList.add("is-invalid");
            hiddenInput.value = "";
            return false;
        }

        if (!iti.isValidNumber()) {
            errorBox.classList.remove("d-none");
            errorBox.innerText = "Invalid phone number for selected country";
            phoneInput.classList.add("is-invalid");
            hiddenInput.value = "";
            return false;
        }

        // ✅ valid
        errorBox.classList.add("d-none");
        phoneInput.classList.remove("is-invalid");
        phoneInput.classList.add("is-valid");

        hiddenInput.value = iti.getNumber();
        return true;
    }

    /* =========================================
       LIVE VALIDATION (ONLY FOR NEW CUSTOMER)
    ========================================= */
    phoneInput.addEventListener("input", validatePhone);
    phoneInput.addEventListener("blur", validatePhone);
    phoneInput.addEventListener("countrychange", validatePhone);

    /* =========================================
       FINAL FORM SUBMIT CONTROL
    ========================================= */
    form.addEventListener("submit", function (e) {

        // 🔥 IMPORTANT: only validate if new customer
        if (isNewCustomer) {
            if (!validatePhone()) {
                e.preventDefault();
                return;
            }
        }

        // always set value if exists
        if (phoneInput.value.trim() && iti.isValidNumber()) {
            hiddenInput.value = iti.getNumber();
        } else {
            hiddenInput.value = phoneInput.value;
        }
    });

});
</script>






@endsection
</x-admin>
