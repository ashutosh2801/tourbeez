<x-admin>
@section('title', 'Internal Orders Create')

<style>
  #travellerModal {
    position: fixed !important;
    top: 0; 
    left: 0; 
    right: 0; 
    bottom: 0;
    display: none;
  }
  #travellerModal.show {
    display: flex !important;
  }
</style>

<div class="card">
    <form id="order-form" action="{{ route('admin.orders.internal.store') }}" method="POST">
        @csrf

        <!-- Top Header -->
        <div class="d-flex justify-content-between align-items-center bg-dark text-white p-3 mb-3 rounded">
            <div>
                <h4 class="m-0">New Order</h4>
                <small>Created by {{ auth()->user()->name }}</small>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center bg-dark text-white p-3 mb-3 rounded">
            <div>
                <strong id="totalDue">$0.00</strong><br>
                <small>Balance</small>
            </div>
            <div>
                <select name="Order[status]" class="form-control">
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

        <!-- Accordion Sections -->
        <div class="accordion" id="orderAccordion">

            <!-- Customer Section -->
            <div class="card mb-2">
                <div class="card-header" id="headingCustomer">
                    <h5 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseCustomer">
                            Customer
                        </button>
                    </h5>
                </div>
                <div id="collapseCustomer" class="collapse show" data-parent="#orderAccordion">
                    <div class="card-body row">
                        <div class="col-md-3"><input type="text" name="Order[customer][firstName]" class="form-control" placeholder="First Name"></div>
                        <div class="col-md-3"><input type="text" name="Order[customer][lastName]" class="form-control" placeholder="Last Name"></div>
                        <div class="col-md-3"><input type="email" name="Order[customer][email]" class="form-control" placeholder="Email"></div>
                        <div class="col-md-3"><input type="text" name="Order[customer][mobile]" class="form-control" placeholder="Mobile"></div>
                    </div>
                </div>
            </div>

            <!-- Agent Section -->
            <div class="card mb-2">
                <div class="card-header" id="headingAgent">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseAgent">
                        Agent
                    </button>
                </div>
                <div id="collapseAgent" class="collapse" data-parent="#orderAccordion">
                    <div class="card-body">
                        <input type="text" name="Order[agent]" class="form-control" placeholder="Search for agent companies">
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="card mb-2">
                <div class="card-header" id="headingProducts">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseProducts">
                        Products
                    </button>
                </div>
                <div id="collapseProducts" class="collapse" data-parent="#orderAccordion">
                    <div class="card-body">
                        <!-- Product Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <select name="Order[product]" id="productSelect" class="form-control">
                                    <option value="">-- Select Product --</option>
                                    @foreach($products as $product)

                                        <option value="{{ $product->slug }}" data-price="{{ $product->price }}" data-id="{{ $product->id }}">{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                             
                                <div class="col-md-2" id="dateWrapper" style="display:none;">
                                    
                                    <input type="date" id="productDate" name="Order[productDate]" class="form-control" placeholder="Select Date">
                                </div>
                            
                            
                                <div class="col-md-2" id="sessionWrapper" style="display:none;">
                                    <!-- <label for="productSession">Select Session</label> -->
                                    <select id="productSession" name="Order[session]" class="form-control">
                                        <option value="">-- Select Session --</option>
                                    </select>
                                </div>
                                <div id="pricingWrapper" class="mt-4"></div>
                                <div class="relative">
                                <input type="text" id="travellerInput" readonly required
                                    class="w-full text-black border rounded-lg border-gray-300 px-3 py-2 outline-none cursor-pointer text-sm lg:text-base"
                                    value="">
                            </div>
                            
                            
                        </div>

                        <!-- Addons -->
                        <div id="addonsWrapper" style="display:none;">
                            <h6>Addons</h6>
                            <div id="addonsContainer"></div>
                        </div>
                        <div class="col-md-6 text-right">
                                <strong>Total: $<span id="productTotal">0.00</span></strong>
                            </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mb-2">
                <div class="card-header" id="headingInfo">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseInfo">
                        Additional Information
                    </button>
                </div>
                <div id="collapseInfo" class="collapse" data-parent="#orderAccordion">
                    <div class="card-body">
                        <textarea name="Order[specialRequirements]" class="form-control" placeholder="Special Requirements"></textarea>
                        <textarea name="Order[internalNotes]" class="form-control mt-2" placeholder="Internal Notes (only visible by supplier)"></textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="card mb-2">
                <div class="card-header" id="headingPayment">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapsePayment">
                        Customer Payment
                    </button>
                </div>
                <div id="collapsePayment" class="collapse" data-parent="#orderAccordion">
                    <div class="card-body">
                        <div class="form-inline mb-2">
                            <select name="Order[payment][type]" class="form-control mr-2">
                                <option value="">Payment type...</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                            <input type="text" name="Order[payment][reference]" class="form-control mr-2" placeholder="Reference number">
                            <input type="date" name="Order[payment][date]" class="form-control mr-2">
                            <input type="number" name="Order[payment][amount]" class="form-control" placeholder="Amount" id="paymentAmount">
                        </div>
                        <strong>Total Payment: $<span id="paymentTotal">0.00</span></strong>
                    </div>
                </div>
            </div>

        </div>

        <!-- Submit -->
        <div class="mt-3">
            <button type="submit" class="btn btn-success">Create Order</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- Traveller Input -->


<!-- Modal -->
<div id="travellerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
  <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Select Travellers</h3>

    <!-- Dynamic pricing options will be inserted here -->
    <div id="travellerOptions"></div>

    <div class="mt-6 flex justify-end gap-3">
      <button type="button" id="closeModal" class="px-4 py-2 rounded-md border">Cancel</button>
      <button type="button" id="applyTravellers" class="px-4 py-2 rounded-md bg-blue-600 text-white">Apply</button>
    </div>
  </div>
</div>

@section('js')

<script>
$(document).ready(function () {
    console.log("✅ Order form JS loaded");

    // Map product slugs to IDs for sessions and addons
    console.log("Product ID Map:");

    // Dummy Data (ideally fetched from server)
    const sessionsData = {
        1: ["Morning 9 AM", "Afternoon 1 PM", "Evening 5 PM"],
        2: ["10 AM - Half Day", "2 PM - Half Day"],
        3: ["Full Day 9 AM - 6 PM"]
    };

    const addonsData = {
        1: [{ id: "a1", title: "Lunch", price: 20 }, { id: "a2", title: "Guide", price: 50 }],
        2: [{ id: "a3", title: "Transport", price: 40 }],
        3: [{ id: "a4", title: "VIP Access", price: 100 }]
    };

    // Cache DOM elements
    const $productSelect = $("#productSelect");
    const $productTotalSpan = $("#productTotal");
    const $totalDueStrong = $("#totalDue");
    const $dateWrapper = $("#dateWrapper");
    const $sessionWrapper = $("#sessionWrapper");
    const $addonsWrapper = $("#addonsWrapper");
    const $productDateInput = $("#productDate");
    const $productSessionSelect = $("#productSession");
    const $addonsContainer = $("#addonsContainer");
    const $paymentAmount = $("#paymentAmount");
    const $paymentTotal = $("#paymentTotal");
    let bookingTimeout;

    // 🔥 Central calculation function for product total
    function recalcTotal() {
        const basePrice = parseFloat($productSelect.find(":selected").data("price")) || 0;
        let addonTotal = 0;

        $(".addon-checkbox:checked").each(function () {
            addonTotal += parseFloat($(this).data("price") || 0);
        });

        const total = basePrice + addonTotal;
        $productTotalSpan.text(total.toFixed(2));
        $totalDueStrong.text(`$${total.toFixed(2)}`);
    }

    // Update payment total
    function updatePaymentTotal() {
        const paymentAmount = parseFloat($paymentAmount.val()) || 0;
        $paymentTotal.text(paymentAmount.toFixed(2));
    }

    // Helper: Reset form state
    function resetForm() {
        $dateWrapper.hide();
        $sessionWrapper.hide();
        $addonsWrapper.hide();
        $productDateInput.val("");
        $productSessionSelect.html('<option value="">-- Select Session --</option>');
        $addonsContainer.empty();
        recalcTotal();
    }

    // Helper: Basic date validation (future date only)
    function isValidDate(dateInput) {
        const selectedDate = new Date($(dateInput).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normalize to start of day
        return selectedDate > today;
    }

    // Initial setup
    if (!$productSelect.val()) {
        resetForm();
    } else {
        $productSelect.trigger("change"); // Trigger change if product pre-selected
    }
    recalcTotal();
    updatePaymentTotal();

    // Handle product change
// Handle product change
$productSelect.on("change", function () {
    console.log("🔄 Product change event fired");

    const productSlug = $(this).val();
    $dateWrapper.show();
    $sessionWrapper.show();

    console.log("➡️ Selected product:", productSlug);

    if (!productSlug) {
        resetForm();
        return;
    }

    // 1️⃣ Fetch tour details
    $.ajax({
        url: `/admin/tour/${productSlug}/fetch_one`,
        type: "GET",
        dataType: "json",
        success: function (response) {
            console.log("✅ Fetch one response:", response);

            // Reset downstream elements
            $productDateInput.val("");
            $addonsWrapper.hide();
            $productSessionSelect.html('<option value="">-- Select Session --</option>');
            $addonsContainer.empty();

            // Set base price
            if (response.price) {
                $productSelect.find(":selected").data("price", response.price);
            }

            // Populate sessions
            if (response.sessions && response.sessions.length > 0) {
                $.each(response.sessions, function (index, session) {
                    $productSessionSelect.append(
                        `<option value="${session}">${session}</option>`
                    );
                });
                $sessionWrapper.show();
            }

            // Populate addons
            if (response.addons && response.addons.length > 0) {
                $.each(response.addons, function (index, addon) {
                    $addonsContainer.append(`
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input addon-checkbox"
                                data-price="${addon.price}" id="addon-${addon.id}" 
                                name="Order[addons][]" value="${addon.id}">
                            <label class="form-check-label" for="addon-${addon.id}">
                                ${addon.title} (+$${addon.price})
                            </label>
                        </div>
                    `);
                });
                $addonsWrapper.show();
            }

            recalcTotal();

            // 2️⃣ Now fetch booking/pricing
            loadTravellerOptions(productSlug);
        },
        error: function (xhr, status, error) {
            console.error("❌ Fetch one API error:", error);
            resetForm();
        }
    });

    // Function to load booking/pricing options
    function loadTravellerOptions(slug) {
        $.ajax({
            url: `/admin/tour/${slug}/booking`,
            type: "GET",
            success: function (response) {
                console.log("✅ Fetch booking response:", response);

                if (!response.data.pricings || response.data.pricings.length === 0) {
                    $("#travellerOptions").html("<p>No pricing available for this tour.</p>");
                    return;
                }

                let html = "";
                response.data.pricings.forEach(item => {
                    html += `
                        <div class="flex justify-between items-center mb-4">
                            <input type="hidden" name="tour_pricing_id[]" value="${item.id}">
                            <span class="text-sm text-black">${item.label} 
                                <small class="text-gray-500">($${item.price})</small>
                            </span>
                            <div class="flex items-center space-x-6 text-center">
                                <button type="button" class="decrease w-6 h-6 rounded-full bg-gray-200 text-blue-900 border border-blue-900">–</button>
                                <span class="text-black w-6 quantity">${item.quantity_used}</span>
                                <button type="button" class="increase w-6 h-6 rounded-full bg-gray-200 text-blue-900 border border-blue-900" max="10">+</button>
                            </div>
                        </div>
                    `;
                });

                $("#travellerOptions").html(html);
            },
            error: function () {
                $("#travellerOptions").html("<p class='text-red-500'>⚠️ Could not load pricing</p>");
            }
        });
    }
});

    

    // Handle date change → show sessions
    // Handle date change → fetch sessions from server
    $productDateInput.on("change", function () {
        const productSlug = $productSelect.val();
        // const productId = $productSelect.data("id");

        const productId = $productSelect.find(":selected").data("id");
        const selectedDate = $(this).val();
        console.log($productSelect);
        if (!productId || !selectedDate) {
            console.warn("⚠️ Missing product or date");
            $sessionWrapper.hide();
            return;
        }

        console.log("📅 Date selected:", selectedDate, "for product:", productId);

        $.ajax({
            url: "/admin/tour-sessions", // adjust prefix if needed
            type: "GET",
            data: {
                tour_id: productId,
                date: selectedDate
            },
            dataType: "json",
            success: function (response) {
                console.log("✅ Session API response:", response);

                $productSessionSelect.html('<option value="">-- Select Session --</option>');

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, session) {

                        console.log(session);
                        $productSessionSelect.append(
                            `<option value="${session}">${session}</option>`
                        );
                    });
                    $sessionWrapper.show();
                } else {
                    $productSessionSelect.html('<option value="">No sessions available</option>');
                    $sessionWrapper.show();
                }



            },
            error: function (xhr, status, error) {
                console.error("❌ Failed to fetch sessions:", error);
                $sessionWrapper.hide();
            }
        });

          // 0.5 second delay
    });


    // Handle session change → show addons
    $productSessionSelect.on("change", function () {
        const productSlug = $productSelect.val();
        const productId = productIdMap[productSlug] || null;
        const productAddons = addonsData[productId] || [];
        console.log("Session selected:", { slug: productSlug, id: productId, addons: productAddons });

        $addonsContainer.empty();
        if (productAddons.length > 0) {
            $.each(productAddons, function (index, addon) {
                $addonsContainer.append(`
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input addon-checkbox"
                            data-price="${addon.price}" id="${addon.id}" name="Order[addons][]" value="${addon.id}">
                        <label class="form-check-label" for="${addon.id}">
                            ${addon.title} (+$${addon.price})
                        </label>
                    </div>
                `);
            });
            $addonsWrapper.show();
        } else {
            $addonsContainer.html('<p class="text-muted">No addons available.</p>');
            $addonsWrapper.show();
        }

        recalcTotal();
    });

    // Handle addon check/uncheck (delegated event)
    $(document).on("change", ".addon-checkbox", function () {
        console.log("Addon toggled:", $(this).val());
        recalcTotal();
    });

    // Handle payment amount change
    $paymentAmount.on("input", function () {
        updatePaymentTotal();
    });

    // Form submission validation
    $("#order-form").on("submit", function (e) {
        const productSlug = $productSelect.val();
        if (!productSlug) {
            e.preventDefault();
            alert("Please select a product.");
            return false;
        }
        if (!$productDateInput.val()) {
            e.preventDefault();
            alert("Please select a date.");
            return false;
        }
        if (!$productSessionSelect.val()) {
            e.preventDefault();
            alert("Please select a session.");
            return false;
        }
    });
    $(document).on("click", ".increase", function () {
        let $qty = $(this).siblings(".quantity");
        let current = parseInt($qty.text());
        let max = parseInt($(this).attr("max")) || 10;

        if (current < max) {
            $qty.text(current + 1);
        }
    });

    // Decrease button
    $(document).on("click", ".decrease", function () {
        let $qty = $(this).siblings(".quantity");
        let current = parseInt($qty.text());

        if (current > 0) {
            $qty.text(current - 1);
        }
    });
    $(document).on("click", "#travellerInput", () => {
        $("#travellerModal").removeClass("hidden");
    });

    // Close modal (Cancel button + overlay)
    $(document).on("click", "#closeModal, #travellerModal", function (e) {
        if (e.target.id === "closeModal" || e.target.id === "travellerModal") {
            $("#travellerModal").addClass("hidden");
        }
    });
});
</script>
@endsection
</x-admin>