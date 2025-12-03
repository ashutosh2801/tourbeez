<x-admin>
@section('title', 'Internal Orders Create')

<div class="card">

    @if ($errors->any())
    <div class="alert alert-danger mb-4 p-3 rounded">
        <ul class="mb-0 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- ================= Header ================= -->
    <div class="card-header d-flex justify-content-between align-items-center bg-secondary p-3 mb-3 rounded">
        <div class="form-group">
            <h4 class="m-0">New Order</h4>
            <small>Created by {{ auth()->user()->name }}</small>
        </div>
    </div>

    <form id="orderForm" class="p-2" action="{{ route('admin.orders.store') }}" method="POST">
        @csrf        

        <!-- ================= Balance + Status ================= -->
        <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded z-10">
            <div>
                <strong id="totalDue">$0.00</strong><br>
                <small>Balance</small>
            </div>
            <div>
                <select name="order_status" class="form-control">
                    <option value="0">New</option> 
                    <option value="4" >Pending Customer</option>
                    <option value="3">Pending Supplier</option>
                    <option value="5" selected>Confirmed</option>
                    <option value="2">On Hold</option>
                    <option value="6">Cancelled</option>
                    <option value="7">Abandoned Cart</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Order</button>
        </div>

        <div class="accordion" id="accordionExample">           
            <div class="card" style="overflow: visible;">
                <div class="card">
                    <div class="card-header bg-secondary py-0 z-10" id="headingOne">
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
                            <div class="row d-flex justify-content-between align-items-center">
                                <div class="form-group col-md-5">
                                    <label for="customer">Select Existing Customer</label>
                                    <select name="customer_id" id="customer" class="form-control aiz-selectpicker z-100 border" data-live-search="true">
                                        <option value="">-- Select Customer --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ ucwords($customer->name) }} - {{ $customer->email }} - {{ $customer->phone ?? 'NA' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-2 text-center font-thin text-lg">  OR</div>

                                <div class="text-center my-3 col-md-5 ">
                                    <button type="button" id="addNewCustomerBtn" class="btn btn-md btn-primary">
                                        <i class="fa fa-user-plus"></i> Add New Customer
                                    </button>
                                </div>
                            </div>

                            {{-- New Customer Fields (hidden by default) --}}
                            <div id="newCustomerFields" class="border rounded p-3 d-none bg-light">
                                <h5 class="mb-3">New Customer Information</h5>

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

                                        <!-- Allow typing "+" -->
                                        <input 
                                            id="customer_phone"
                                            name="customer_phone"
                                            type="tel"
                                            class="form-control"
                                            autocomplete="tel"
                                            inputmode="tel"
                                        />

                                        <!-- Hidden field that stores full E.164 number -->
                                        <input type="hidden" id="full_phone" name="full_phone">

                                        <small class="text-danger d-none" id="error_phone">Invalid phone number</small>
                                    </div>
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
                                <i class="fa fa-angle-right"></i> Tour Details
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                        <div class="card-body">
                            <div style="border:1px solid #ccc; margin-bottom:10px; padding:10px">
                                <table class="table">
                                    <tr>
                                        <td style="border: none; padding: 0;">
                                            <select 
                                                onchange="loadTourDetails(this.value, 0)"
                                                name="tour_id0" 
                                                class="form-control aiz-selectpicker border" data-live-search="true">
                                                <option value="">Select Tour</option>
                                                @foreach($tours as $tour)
                                                    <option value="{{ $tour->id }}">{{ $tour->title }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                                <div id="tour_details_0"></div>
                            </div>
                            <div id="tourContainer"></div>
                            <button type="button" onclick="addTour()" class="btn btn-sm btn-info px-5">+ Add Tour</button>
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
                                    <input type="radio" name="payment_type" value="card"> Credit Card (Stripe)
                                </label>
                                <label class="mr-3">
                                    <input type="radio" name="payment_type" value="transaction"> Cash
                                </label>
                                <label>
                                    <input type="radio" name="payment_type" value="other"> Other
                                </label>
                            </div>

                            {{-- Stripe Credit Card Fields --}}
                            <div id="cardFields" style="display:none;">
                                <div class="form-group">
                                    <label for="card-element">Card Details</label>
                                    <div id="card-element" class="form-control col-6" style="padding: 10px; height: auto;"></div>
                                    <div class="mt-3"><label><input type="checkbox" value="1" name="charge_ccnow" id="charge_ccnow" /> Charge credit card now</label></div>
                                    <small id="card-errors" class="text-danger mt-2"></small>
                                </div>
                                <div class="form-group hidden" id="charge_ccnow_amount">
                                    <div class="form-group  col-6">
                                        <label>Amount</label>
                                        <div class="input-group">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                            </div>    
                                            <input type="text" class="form-control" id="addPaymentAmount" name="charge_ccnow_amount" placeholder="0.00">                                            
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>

                            {{-- Transaction Fields (inline) --}}
                            <div id="transactionFields" style="display:none;">
                                <div class="form-row align-items-center">
                                    <div class="form-group col-md-6">
                                        <label for="transaction_id">Ref. Number</label>
                                        <input type="text" name="transaction_id" class="form-control" placeholder="Enter Ref. Number">
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

                            <div id="cashFields" style="display:none;">
                                <div class="form-group col-md-6">
                                    <label for="transaction_id">Other</label>
                                    <input type="text" name="other" class="form-control" placeholder="Enter other payment details" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div> 

                <!-- ================= Form Actions ================= -->
                <div class="card-footer" style="display:block">
                    <button style="padding:0.6rem 2rem" type="submit" id="createOrderBtn" class="btn btn-primary">Create Order</button>
                    <a style="padding:0.6rem 2rem" href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
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
        url: '{{ route("tour.single") }}',
        type: 'POST',
        data: { id: tourId, tourCount: count, _token: '{{ csrf_token() }}' },

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
                    const selectedDate = picker.startDate.format("YYYY-MM-DD");
                    $(this).val(selectedDate).trigger('change');

                    const $row = $("#row_" + count);

                    const pretty = moment(selectedDate).format("ddd MMM DD YYYY");
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

    

</script>

<script>
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
                // remove required
            }
        });
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /* ======================================================
       INTL TEL INPUT INITIALIZATION
    ====================================================== */
    const phoneInput = document.querySelector("#customer_phone");

    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "auto",
        separateDialCode: true,
        nationalMode: false,
        geoIpLookup: function (callback) {
            fetch("https://ipapi.co/json/")
                .then(res => res.json())
                .then(data => callback(data.country_code))
                .catch(() => callback("US"));
        },
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/utils.js",
    });




    /* ======================================================
       ALLOW + AUTO-DETECT COUNTRY FROM FULL NUMBER
    ====================================================== */
    phoneInput.addEventListener("input", function () {
        let value = this.value.trim();

        // Allow the first character to be "+"
        if (value.startsWith("+")) {
            // Remove non-numeric characters except +
            value = value.replace(/[^0-9+]/g, "");
            this.value = value;

            // Auto-detect country if number has enough digits
            if (value.length > 3) {
                iti.setNumber(value);
            }
            return; // stop here, do not apply numeric restrictions below
        }
    });
    document.getElementById("full_phone").value = iti.getNumber();
    /* ======================================================
       BLOCK LETTERS — only numbers allowed
    ====================================================== */
    phoneInput.addEventListener("keypress", function (e) {
        const char = String.fromCharCode(e.which);

        // Allow "+" only as first character
        if (char === "+" && this.value.length === 0) return;

        if (!/[0-9]/.test(char)) {
            e.preventDefault();
        }
    });

    /* ======================================================
       BLOCK INVALID PASTE (allow + at start)
    ====================================================== */


    /* ======================================================
       UPDATE HIDDEN FULL NUMBER
    ====================================================== */
    function updateFullNumber() {
        document.getElementById("full_phone").value = iti.getNumber();
    }

    phoneInput.addEventListener("input", updateFullNumber);
    phoneInput.addEventListener("countrychange", updateFullNumber);

    /* ======================================================
       FIELD VALIDATIONS
    ====================================================== */
    function validateFields() {
        let valid = true;

        // FIRST NAME
        const first = document.getElementById("customer_first_name");
        if (!/^[A-Za-z]{2,}$/.test(first.value.trim())) {
            document.getElementById("error_first_name").classList.remove("d-none");
            valid = false;
        } else {
            document.getElementById("error_first_name").classList.add("d-none");
        }

        // LAST NAME
        const last = document.getElementById("customer_last_name");
        if (!/^[A-Za-z]{2,}$/.test(last.value.trim())) {
            document.getElementById("error_last_name").classList.remove("d-none");
            valid = false;
        } else {
            document.getElementById("error_last_name").classList.add("d-none");
        }

        // EMAIL
        const email = document.getElementById("customer_email");
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value.trim())) {
            document.getElementById("error_email").classList.remove("d-none");
            valid = false;
        } else {
            document.getElementById("error_email").classList.add("d-none");
        }

        // PHONE VALIDATION (Intl Tel Input)
        if (!iti.isValidNumber()) {
            document.getElementById("error_phone").classList.remove("d-none");
            valid = false;
        } else {
            document.getElementById("error_phone").classList.add("d-none");
        }

        return valid;
    }

    /* ======================================================
       FORM SUBMIT VALIDATION
    ====================================================== */
    document.querySelector("form").addEventListener("submit", function (e) {
        updateFullNumber(); // always update before form submit

        if (!validateFields()) {
            e.preventDefault();
            alert("Please correct the highlighted fields.");
        }
    });

});

</script>
<script>
document.addEventListener("change", function(e){
    if(e.target.classList.contains("pickup-dropdown")) {
        // const tour = e.target.dataset.tour;

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

function calculateRowTotal34234(row) {    

    let subtotal = 0;
    let withouttax = 0;

    // -----------------------------------------
    // 1) PRICING QTY * PRICE
    // -----------------------------------------
    row.querySelectorAll('input[name^="tour_pricing_qty_"]').forEach((qtyInput) => {
        const qty = parseFloat(qtyInput.value) || 0;

        const priceInput = qtyInput.parentElement.querySelector(
            'input[name^="tour_pricing_price_"]'
        );

        

        const priceTypeInput = qtyInput.parentElement.querySelector(
            'input[name^="tour_pricing_type"]'
        );

        const price = parseFloat(priceInput.value) || 0;
        const priceType = priceTypeInput.value;
        
        if(priceType === "FIXED"){
            subtotal = price;
        } else{
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
// FIXED_PER_ORDER
    let tax = 0;
    
    if (feeType === "PERCENT") {
        tax = subtotal * (feeValue / 100);
    } else {
        tax = feeValue;
    }

    // Format tax for UI
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
        document.getElementById("totalDue").innerText = subtotal.toFixed(2);
    }
}


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

            // if (minQty !== null && qty < parseFloat(minQty)) {
            //     alert("Quantity cannot be less than minimum allowed (" + minQty + ").");
            //     qty = parseFloat(minQty);
            //     qtyInput.value = qty;
            // }

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

        taxRow.querySelector('.tax-amount').textContent = '$'+formattedTax;

        subtotal += tax;
    });

    // -----------------------------------------
    // 4) UPDATE UI SUBTOTAL
    // -----------------------------------------
    const withouttaxBox = row.querySelector('.withouttax-box');
    if (withouttaxBox) {
        withouttaxBox.textContent = '$'+withouttax.toFixed(2);
    }
    const subtotalBox = row.querySelector('.subtotal-box');
    if (subtotalBox) {
        document.getElementById("totalDue").innerText = '$'+subtotal.toFixed(2);
        document.getElementById("addPaymentAmount").value = subtotal.toFixed(2);
        subtotalBox.textContent = '$'+subtotal.toFixed(2);

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
</script>



@endsection
</x-admin>
