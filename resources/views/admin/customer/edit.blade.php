<x-admin>

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
    @section('title', 'Edit User')
    <form action="{{ route('admin.customers.source.update', ['id' => encrypt($user->id),'source' => $source]) }}" method="POST">
        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-header customer-edit-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">{{ $user->name }}</h3>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-back">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body customer-edit-body">
            
         
                <form id="customerForm" action="{{ route('admin.customers.source.update', [
    'id' => encrypt($user->id),
    'source' => $source
]) }}" method="POST">
                @method('PUT')
                @csrf
                <input type="hidden" name="id" value="{{ $user->id }}">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" required value="{{ $user->name }}" readonly>
                            <x-error>name</x-error>
                        </div>
                    </div>
                </div>

                @php
                    $orderCustomer = $user->customer;

                    $orderCustomer = $orderCustomer?? $user;

                @endphp


                @if($orderCustomer ?? false)


                    <!-- <h4 class="mt-4 mb-3">Order Customer Details</h4> -->

                    <!-- <div class="row">
                        <div class="col-lg-6">
                            <label>First Name</label>
                            <input type="text" name="oc_first_name" class="form-control" value="{{ $orderCustomer->first_name }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Last Name</label>
                            <input type="text" name="oc_last_name" class="form-control" value="{{ $orderCustomer->last_name }}">
                        </div>

                        <div class="col-lg-6">
                            <label>Email</label>
                            <input type="email" name="oc_email" class="form-control" value="{{ $orderCustomer->email }}" disabled>
                        </div>

                        <div class="col-lg-6">
                            <label>Phone</label>
                            <input type="text" name="oc_phone" class="form-control" value="{{ $orderCustomer->phone }}">
                        </div>

                        <div class="col-lg-12">
                            <label>Instructions</label>
                            <textarea name="oc_instructions" class="form-control">{{ $orderCustomer->instructions }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Pickup ID</label>
                            <textarea name="oc_pickup_id" class="form-control">{{ $orderCustomer->pickup?->location }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Pickup Name</label>
                            <textarea name="oc_pickup_name" class="form-control">{{ $orderCustomer->pickup_name }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label>Stripe Customer ID</label>
                            <input type="text" name="oc_stripe_customer_id" class="form-control" value="{{ $orderCustomer->stripe_customer_id }}">
                        </div>
                    </div> -->

                @endif
            </div>

            @php
                $orderCustomer = $user->customer;
                $orderCustomer = $orderCustomer?? $user;
            @endphp
        </div>

        @if($orderCustomer ?? false)
            <div class="card card-primary bg-white border rounded-lg-custom customer-edit-body">
                <div class="card-header">
                    <h3 class="card-title">Order Customer Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="oc_first_name" class="form-control" value="{{ $orderCustomer->first_name }}">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="oc_last_name" class="form-control" value="{{ $orderCustomer->last_name }}">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="oc_email" class="form-control" value="{{ $orderCustomer->email }}" readonly>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="oc_phone" class="form-control" value="{{ $orderCustomer->phone }}">
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Instructions</label>
                                <textarea name="oc_instructions" class="form-control">{{ $orderCustomer->instructions }}</textarea>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Pickup ID</label>
                                <select name="oc_pickup_id" class="form-control">
                                    <option value="">Select pickup</option>
                                    @foreach($pickupLocations as $pickuplocation)
                                        <option value="{{$pickuplocation->id}}" {{$orderCustomer->pickup_id == $pickuplocation->id ? 'selected' : ''}}>{{ $pickuplocation->location . " - " .  $pickuplocation->address}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Pickup Name</label>
                                <textarea name="oc_pickup_name" class="form-control">{{ $orderCustomer->pickup_name }}</textarea>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        <!-- </div> -->
    <!-- </div> -->

    @php
        $orderCustomer = $user->customer;
        $orderCustomer = $orderCustomer?? $user;
        $phoneNumber = $orderCustomer->phone;
                      
          if(!str_contains($phoneNumber, '+')){

            $phoneNumber = "+" . $phoneNumber;
          }

          
    @endphp

    

    @if($orderCustomer ?? false)
        <div class="card card-primary bg-white border rounded-lg-custom customer-edit-body">
            <div class="card-header">
                <h3 class="card-title">Order Customer Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <label>First Name</label>
                        <input type="text" name="oc_first_name" class="form-control" value="{{ $orderCustomer->first_name }}">
                    </div>

                    <div class="col-lg-6">
                        <label>Last Name</label>
                        <input type="text" name="oc_last_name" class="form-control" value="{{ $orderCustomer->last_name }}">
                    </div>

                    <div class="col-lg-6">
                        <label>Email</label>
                        <input type="email" name="oc_email" class="form-control" value="{{ $orderCustomer->email }}">
                    </div>
                    

                    <!-- <div class="col-lg-6">
                        <label>Phone</label>
                        <input type="text" name="oc_phone" class="form-control" value="{{ $orderCustomer->phone }}">
                    </div> -->



                    <div class="form-group col-lg-3 col-md-6">
                        <label for="oc_phone_intel">Phone (with country code) *</label>



                        <input 
                            id="oc_phone_intel"
                            name="oc_phone_intel"
                            type="tel"
                            class="form-control"
                            value="{{ $phoneNumber }}"
                        />


                        <!-- Hidden field that stores full E.164 number -->
                        <input type="hidden" name="oc_phone" id="oc_phone">

                        <small class="text-danger d-none" id="error_phone">Invalid phone number</small>
                    </div>

                   

                    <div class="col-lg-12">
                        <label>Instructions</label>
                        <textarea name="oc_instructions" class="form-control">{{ $orderCustomer->instructions }}</textarea>
                    </div>

                    <div class="col-lg-6">
                        <label>Pickup ID</label>


        <div class="card-footer bg-white border rounded-lg-custom">
            <div class="float-right">
                <button class="btn btn-success m-0" type="submit"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
        
    @endif


    <div class="card-footer bg-white border rounded-lg-custom">
        <div class="float-right">
            <button class="btn btn-success m-0" type="submit"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</form>

@section('js') 

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/css/intlTelInput.css"/>




<script>
document.addEventListener("DOMContentLoaded", function () {

    const phoneInput = document.querySelector("#oc_phone_intel");

    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "ca",
        separateDialCode: true,
        nationalMode: false,
        dropdownContainer: document.body,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/utils.js",
    });

    /* ======================================================
       ADD SEARCH BOX INTO DROPDOWN (FIXED)
    ====================================================== */
    phoneInput.addEventListener("open:countrydropdown", function () {

        setTimeout(() => {
            const dropdown = document.querySelector(".iti__country-list");

            if (!dropdown) return;

            // Remove old search (avoid duplicates)
            const oldSearch = dropdown.querySelector(".iti__search-box");
            if (oldSearch) oldSearch.remove();

            // Create search box
            const searchBox = document.createElement("div");
            searchBox.className = "iti__search-box";

            const input = document.createElement("input");
            input.type = "text";
            input.placeholder = "Search country...";
            input.className = "iti__search-input";

            searchBox.appendChild(input);
            dropdown.prepend(searchBox);

            const countries = dropdown.querySelectorAll(".iti__country");

            // 🔥 FIX: prevent dropdown from closing
            searchBox.addEventListener("click", function (e) {
                e.stopPropagation();
            });

            input.addEventListener("click", function (e) {
                e.stopPropagation();
            });

            input.addEventListener("keydown", function (e) {
                e.stopPropagation();
            });

            // Filter logic
            input.addEventListener("input", function () {
                const value = this.value.toLowerCase();

                countries.forEach(country => {
                    const name = country.innerText.toLowerCase();
                    country.style.display = name.includes(value) ? "" : "none";
                });
            });

            // 🔥 FIX: keep focus on input
            input.focus();

        }, 100);
    });

    const form = document.getElementById("customerForm");

    form.addEventListener("submit", function () {
        console.log("FORM SUBMIT TRIGGERED"); // 🔥 test

        const hiddenInput = document.querySelector("#oc_phone");

        const rawValue = phoneInput.value.trim();

        if (!rawValue) {
            hiddenInput.value = "";
            return;
        }

        if (iti.isValidNumber()) {
            hiddenInput.value = iti.getNumber();
        } else {
            hiddenInput.value = rawValue;
        }
    });


});

</script>

@endsection
    

</x-admin>
