<x-admin>
    @section('title', 'Edit Voucher')

    <style>
        /* Help Panel */
        .help-panel {
            position: sticky;
            top: 20px;
            background: #f7f9fc;
            border-left: 3px solid #3c8dbc;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .help-title {
            font-weight: bold;
            color: #3c8dbc;
            margin-bottom: 10px;
        }
    </style>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">Edit Voucher</h3>
            <a href="{{ route('admin.vouchers.index') }}" class="btn btn-sm btn-dark">Back</a>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Form -->
                <div class="col-md-9">
                    <form id="voucherForm" action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- CREATE MODE --}}
                        <div class="form-group">
                            <label>Create Mode</label>
                            <select class="form-control help-field" name="createMode" id="createMode"
                                    data-help="Select how voucher codes will be created. Rezdy: auto, Manual: enter manually.">
                                <option value="REZDY" {{ $voucher->createMode === 'REZDY' ? 'selected' : '' }}>Rezdy</option>
                                <option value="MANUAL" {{ $voucher->createMode === 'MANUAL' ? 'selected' : '' }}>Manual</option>
                            </select>
                            @error('createMode') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- MANUAL CODES --}}
                        <div class="form-group d-none" id="manualCodesBox">
                            <label>Voucher Codes</label>
                            <textarea name="codesList" class="form-control help-field" rows="4"
                                      data-help="Enter voucher codes manually (one per line).">{{ old('codesList', $voucher->codesList) }}</textarea>
                            @error('codesList') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- QUANTITY --}}
                        <div class="form-group" id="quantityBox">
                            <label>Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity', $voucher->quantity) }}" min="1"
                                   class="form-control help-field"
                                   data-help="Number of vouchers to generate automatically for Rezdy mode.">
                            @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <hr>

                        {{-- ISSUE DATE --}}
                        <div class="form-group">
                            <label>Issue Date</label>
                            <input type="text"
                                   class="aiz-date-range form-control help-field"
                                   name="issueDate" data-single="true" readonly
                                   value="{{ old('issueDate', $voucher->issueDate) }}"
                                   data-help="The date when voucher becomes active.">
                            @error('issueDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- EXPIRY --}}
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text"
                                   class="aiz-date-range form-control help-field"
                                   name="expiryDate" data-single="true" readonly
                                   value="{{ old('expiryDate', $voucher->expiryDate) }}"
                                   data-help="The date when voucher expires.">
                            @error('expiryDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <hr>

                        {{-- TRAVEL DATES --}}
                        <div class="form-group">
                            <label>Travel Date From</label>
                            <input type="text"
                                   class="aiz-date-range form-control help-field"
                                 name="travelFromDate" data-single="true" readonly
                                   value="{{ old('travelFromDate', $voucher->travelFromDate) }}"
                                   data-help="Earliest travel date allowed.">
                            @error('travelFromDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Travel Date To</label>
                            <input type="text"
                                   class="aiz-date-range form-control help-field"
                                   name="travelToDate" data-single="true" readonly
                                   value="{{ old('travelToDate', $voucher->travelToDate) }}"
                                   data-help="Latest travel date allowed.">
                            @error('travelToDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <hr>

                        {{-- VALID DAYS --}}
                        <div class="form-group help-field" data-help="Select the days when voucher can be used.">
                            <label>Valid Redemption Days</label><br>
                            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $day)
                                <label class="mr-3">
                                    <input type="checkbox" name="validRedemptionDays[]" value="{{ $i+1 }}"
                                        {{ in_array($i+1, old('validRedemptionDays', $voucher->validRedemptionDays ?? [])) ? 'checked' : '' }}> {{ $day }}
                                </label>
                            @endforeach
                            @error('validRedemptionDays') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <hr>

                        {{-- AGENT --}}
                        <div class="form-group">
                            <label>Agent</label>
                            <input type="text" name="agent" class="form-control help-field"
                                   value="{{ old('agent', $voucher->agent) }}"
                                   data-help="Optional agent name. Not required.">
                        </div>

                        {{-- INTERNAL REFERENCE --}}
                        <div class="form-group">
                            <label>Internal Reference</label>
                            <input type="text" name="internalReference" class="form-control help-field"
                                   value="{{ old('internalReference', $voucher->internalReference) }}"
                                   data-help="Internal staff-only reference. Not shown to customers.">
                        </div>

                        {{-- MIN AMOUNT --}}
                        <div class="form-group">
                            <label>Minimum Amount</label>
                            <input type="number" step="0.01" name="minAmount"
                                   class="form-control help-field"
                                   value="{{ old('minAmount', $voucher->minAmount) }}"
                                   data-help="Minimum cart total required to redeem voucher.">
                            @error('minAmount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- INCLUDE TAXES --}}
                        <div class="form-group">
                            <label>Include Taxes & Fees?</label>
                            <select name="includeTaxesFees" class="form-control help-field"
                                    data-help="If yes, taxes are included when applying discount.">
                                <option value="1" {{ $voucher->includeTaxesFees ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$voucher->includeTaxesFees ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        {{-- INCLUDE EXTRAS --}}
                        <div class="form-group">
                            <label>Include Extras?</label>
                            <select name="includeExtras" class="form-control help-field"
                                    data-help="If yes, voucher will apply to extras also.">
                                <option value="0" {{ !$voucher->includeExtras ? 'selected' : '' }}>No</option>
                                <option value="1" {{ $voucher->includeExtras ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>

                        <hr>

                        {{-- VALUE TYPE --}}
                        <div class="form-group">
                            <label>Voucher Value Type</label>
                            <select name="valueType" id="valueType" class="form-control help-field"
                                    data-help="Select how the voucher gives value.">
                                <option value="">-- Select --</option>
                                <option value="VALUE" {{ $voucher->valueType === 'VALUE' ? 'selected' : '' }}>Value (Cash)</option>
                                <option value="VALUE_LIMITPRODUCT" {{ $voucher->valueType === 'VALUE_LIMITPRODUCT' ? 'selected' : '' }}>Value (Specific Product)</option>
                                <option value="VALUE_LIMITCATEGORY" {{ $voucher->valueType === 'VALUE_LIMITCATEGORY' ? 'selected' : '' }}>Value (Category)</option>
                                <option value="PRODUCT" {{ $voucher->valueType === 'PRODUCT' ? 'selected' : '' }}>Product</option>
                            </select>
                            @error('valueType') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- VOUCHER VALUE --}}
                        <div class="form-group d-none" id="voucherValueBox">
                            <label>Voucher Value</label>
                            <input type="number" step="0.01" name="voucherValue"
                                   class="form-control help-field"
                                   value="{{ old('voucherValue', $voucher->voucherValue) }}"
                                   data-help="Amount of discount the voucher gives. Required if Value type is 'Value (Cash)'.">
                            @error('voucherValue') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- PRODUCT ID --}}
                        <div class="form-group d-none" id="productBox">
                            <label>Product ID</label>
                            <input type="number" name="productId"
                                   class="form-control help-field"
                                   value="{{ old('productId', $voucher->productId) }}"
                                   data-help="ID of the product this voucher applies to. Required if Value type is Product or Value Specific Product.">
                        </div>

                        {{-- CATEGORY ID --}}
                        <div class="form-group d-none" id="categoryBox">
                            <label>Category ID</label>
                            <input type="number" name="categoryId"
                                   class="form-control help-field"
                                   value="{{ old('categoryId', $voucher->categoryId) }}"
                                   data-help="ID of the category this voucher applies to. Required if Value type is Value (Category).">
                        </div>

                        <hr>

                        {{-- NOTES --}}
                        <div class="form-group">
                            <label>Internal Notes</label>
                            <textarea name="internalNotes" rows="3"
                                      class="form-control help-field"
                                      data-help="Internal staff-only notes. Not shown to customer.">{{ old('internalNotes', $voucher->internalNotes) }}</textarea>
                        </div>

                        <button class="btn btn-primary btn-block">Update Voucher</button>
                    </form>
                </div>

                <!-- Help Panel -->
                <div class="col-md-3">
                    <div class="help-panel">
                        <div class="help-title">Field Help</div>
                        <div id="helpContent">Click on a field to see help.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const helpContent = document.getElementById('helpContent');

                // HELP PANEL
                document.querySelectorAll('.help-field').forEach(field => {
                    field.addEventListener('focus', function () {
                        helpContent.innerText = this.dataset.help || 'No help available.';
                    });
                    field.addEventListener('blur', function () {
                        helpContent.innerText = 'Click on a field to see help.';
                    });
                });

                // CREATE MODE
                function toggleCreateMode() {
                    const mode = document.getElementById('createMode').value;
                    document.getElementById('manualCodesBox').classList.toggle('d-none', mode !== 'MANUAL');
                    document.getElementById('quantityBox').classList.toggle('d-none', mode !== 'REZDY');
                }
                const createModeSelect = document.getElementById('createMode');
                createModeSelect.addEventListener('change', toggleCreateMode);
                toggleCreateMode();

                // VALUE TYPE
                function toggleValueType() {
                    const type = document.getElementById('valueType').value;
                    document.getElementById('voucherValueBox').classList.toggle('d-none', type !== 'VALUE');
                    document.getElementById('productBox').classList.toggle('d-none', !(type === 'PRODUCT' || type === 'VALUE_LIMITPRODUCT'));
                    document.getElementById('categoryBox').classList.toggle('d-none', type !== 'VALUE_LIMITCATEGORY');
                }
                const valueTypeSelect = document.getElementById('valueType');
                valueTypeSelect.addEventListener('change', toggleValueType);
                toggleValueType();

                // FORM VALIDATION
                document.getElementById('voucherForm').addEventListener('submit', function(e) {
                    let errors = [];

                    const createMode = createModeSelect.value;
                    const valueType = valueTypeSelect.value;

                    if (createMode === 'MANUAL') {
                        const codes = document.querySelector('[name="codesList"]').value.trim();
                        if (!codes) errors.push("Voucher codes required for manual mode.");
                    } else {
                        const qty = document.querySelector('[name="quantity"]').value;
                        if (qty < 1) errors.push("Quantity must be at least 1.");
                    }

                    if (!valueType) errors.push("Voucher value type is required.");

                    if (valueType === 'VALUE') {
                        const v = document.querySelector('[name="voucherValue"]').value;
                        if (!v || v <= 0) errors.push("Voucher value is required for Value type.");
                    }

                    if (valueType === 'PRODUCT' || valueType === 'VALUE_LIMITPRODUCT') {
                        const pid = document.querySelector('[name="productId"]').value;
                        if (!pid || pid <= 0) errors.push("Product ID is required for this voucher type.");
                    }

                    if (valueType === 'VALUE_LIMITCATEGORY') {
                        const cid = document.querySelector('[name="categoryId"]').value;
                        if (!cid || cid <= 0) errors.push("Category ID is required for this voucher type.");
                    }

                    if (errors.length > 0) {
                        e.preventDefault();
                        alert(errors.join("\n"));
                    }
                });
            });
        </script>
    @endsection
</x-admin>
