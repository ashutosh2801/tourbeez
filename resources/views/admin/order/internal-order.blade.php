<x-admin>
@section('title', 'Orders List')


<div class="card">
    <form id="order-form" action="{{ route('admin.orders.internal.store') }}" method="POST">
        @csrf

        <!-- Top Header -->
        <div class="d-flex justify-between items-center bg-dark text-white p-3 mb-3 rounded">
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <select name="Order[product]" id="productSelect" class="form-control">
                                    <option value="">-- Select Product --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 text-right">
                                <strong>Total: $<span id="productTotal">0.00</span></strong>
                            </div>
                        </div>

                        <!-- Addons -->
                        <div id="addonsWrapper" style="display:none;">
                            <h6>Addons</h6>
                            <div id="addonsContainer"></div>
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
                            <select name="payment[type]" class="form-control mr-2">
                                <option value="">Payment type...</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                            <input type="text" name="payment[reference]" class="form-control mr-2" placeholder="Reference number">
                            <input type="date" name="payment[date]" class="form-control mr-2">
                            <input type="number" name="payment[amount]" class="form-control" placeholder="Amount">
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


@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let productSelect = document.getElementById('productSelect');
    let productTotal = document.getElementById('productTotal');
    let totalDue = document.getElementById('totalDue');

    productSelect.addEventListener('change', function() {
        let price = this.options[this.selectedIndex].getAttribute('data-price') || 0;
        productTotal.innerText = parseFloat(price).toFixed(2);
        totalDue.innerText = '$' + parseFloat(price).toFixed(2);

        // Show addons if product has any (mockup logic)
        let addonsWrapper = document.getElementById('addonsWrapper');
        if (this.value) {
            addonsWrapper.style.display = 'block';
            document.getElementById('addonsContainer').innerHTML = `
                <div class="form-check">
                    <input class="form-check-input addon" type="checkbox" data-price="10" value="1">
                    <label class="form-check-label">Addon 1 ($10)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input addon" type="checkbox" data-price="20" value="2">
                    <label class="form-check-label">Addon 2 ($20)</label>
                </div>
            `;
        } else {
            addonsWrapper.style.display = 'none';
        }
    });

    // Dynamic addon calculation
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('addon')) {
            let basePrice = parseFloat(productSelect.options[productSelect.selectedIndex].getAttribute('data-price')) || 0;
            let addonTotal = 0;
            document.querySelectorAll('.addon:checked').forEach(addon => {
                addonTotal += parseFloat(addon.getAttribute('data-price'));
            });
            let grandTotal = basePrice + addonTotal;
            productTotal.innerText = grandTotal.toFixed(2);
            totalDue.innerText = '$' + grandTotal.toFixed(2);
        }
    });
});
</script>
@endsection
</x-admin>
