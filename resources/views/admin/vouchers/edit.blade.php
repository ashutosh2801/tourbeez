<x-admin>
@section('title', 'Edit Voucher')

<style>
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

<!-- HEADER -->
<div class="voucher-list-header card-primary mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col-md-8 col-6">
                <h3 class="card-title">Edit Voucher</h3>
            </div>
            <div class="col-md-4 col-6 text-right">
                <a href="{{ route('admin.vouchers.index') }}" class="btn btn-sm btn-back">Back</a>
            </div>
        </div>
    </div>
</div>

<div class="voucher-list-body card bg-white border rounded-lg-custom">
    <div class="card-body">
        <div class="row">
            {{-- FORM --}}
            <div class="col-md-9">
                <form id="voucherForm"
                      method="POST"
                      action="{{ route('admin.vouchers.update',$voucher->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- CREATE MODE --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Create Mode</label>
                            <select class="form-control help-field" name="createMode" id="createMode">
                                <option value="Automactic"
                                    {{ old('createMode',$voucher->create_mode)=='Automactic'?'selected':'' }}>
                                    Automactic
                                </option>
                                <option value="MANUAL"
                                    {{ old('createMode',$voucher->create_mode)=='MANUAL'?'selected':'' }}>
                                    Manual
                                </option>
                            </select>
                            @error('createMode')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-6 mb-3 d-none" id="manualCodesBox">
                            <label>Voucher Codes</label>
                            <textarea name="codesList" rows="4" class="form-control">{{ old('codesList',$voucher->codes_list) }}</textarea>
                            @error('codesList')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-6 mb-3" id="quantityBox">
                            <label>Quantity</label>
                            <input type="number" min="1" name="quantity"
                                   value="{{ old('quantity',$voucher->quantity) }}"
                                   class="form-control">
                            @error('quantity')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <hr>

                    {{-- DATES --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Issue Date</label>
                            <input type="text" name="issueDate"
                                   class="form-control"
                                   value="{{ old('issueDate',$voucher->issue_date) }}">
                            @error('issueDate')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Expiry Date</label>
                            <input type="text" name="expiryDate"
                                   class="form-control"
                                   value="{{ old('expiryDate',$voucher->expiry_date) }}">
                            @error('expiryDate')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <hr>

                    {{-- TRAVEL --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Travel From</label>
                            <input type="text" name="travelFromDate"
                                   class="form-control"
                                   value="{{ old('travelFromDate',$voucher->travel_from_date) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Travel To</label>
                            <input type="text" name="travelToDate"
                                   class="form-control"
                                   value="{{ old('travelToDate',$voucher->travel_to_date) }}">
                        </div>
                    </div>

                    <hr>

                    {{-- VALID DAYS --}}
                    <div class="mb-3">
                        <label>Redeemable Days</label><br>
                        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i=>$day)
                            <label class="mr-3">
                                <input type="checkbox"
                                       name="validRedemptionDays[]"
                                       value="{{ $i+1 }}"
                                       {{ in_array($i+1, old('validRedemptionDays',$voucher->valid_redemption_days ?? []))?'checked':'' }}>
                                {{ $day }}
                            </label>
                        @endforeach
                    </div>

                    <hr>

                    {{-- AGENT --}}
                    <div class="mb-3">
                        <label>Agent</label>
                        <input type="text" name="agent" class="form-control"
                               value="{{ old('agent',$voucher->agent) }}">
                    </div>

                    <div class="mb-3">
                        <label>Internal Reference</label>
                        <input type="text" name="internalReference" class="form-control"
                               value="{{ old('internalReference',$voucher->internal_reference) }}">
                    </div>

                    <div class="mb-3">
                        <label>Minimum Amount</label>
                        <input type="number" step="0.01" name="minAmount" class="form-control"
                               value="{{ old('minAmount',$voucher->min_amount) }}">
                    </div>

                    {{-- CHECKBOXES --}}
                    <div class="mb-3">
                        <input type="hidden" name="includeTaxesFees" value="0">
                        <label>
                            <input type="checkbox" name="includeTaxesFees" value="1"
                                   {{ old('includeTaxesFees',$voucher->include_taxes_fees)?'checked':'' }}>
                            Include taxes & fees
                        </label>
                    </div>

                    <div class="mb-3">
                        <input type="hidden" name="includeExtras" value="0">
                        <label>
                            <input type="checkbox" name="includeExtras" value="1"
                                   {{ old('includeExtras',$voucher->include_extras)?'checked':'' }}>
                            Include extras
                        </label>
                    </div>

                    <hr>

                    {{-- VALUE TYPE --}}
                    
                    <div class="form-group">
                        <label for="Voucher_valueType">Value <span class="required">*</span></label>
                        <select class="form-control" name="valueType" id="Voucher_valueType">
                            <option value="">Select...</option>
                            @foreach ([
                                'VALUE_LIMITPRODUCT' => 'Fixed amount for one product',
                                'VALUE' => 'Fixed amount for any product',
                                'VALUE_LIMITCATEGORY' => 'Fixed amount for any product within a category',
                                'PRODUCT' => 'Free product'
                            ] as $key => $label)
                                <option value="{{ $key }}"
                                    {{ old('valueType', $voucher->value_type ?? '') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- VALUE TYPE CONTENT --}}
                    <div class="valueTypeContainer" style="display:none">

                        {{-- VOUCHER VALUE --}}
                        <div class="valueType-VALUE row" style="display:none">
                            <label class="col-md-3">Voucher Value</label>
                            <div class="col-md-6 input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="voucherValue"
                                       class="form-control"
                                       value="{{ old('voucherValue', $voucher->voucher_value ?? '') }}">
                            </div>
                        </div>

                        {{-- REUSABLE --}}
                        <div class="valueType-VALUE row" style="display:none">
                            <label class="col-md-3">Reusable</label>
                            <div class="col-md-2 mt-1">
                                <input type="hidden" name="reusable" value="0">
                                <input type="checkbox"
                                       name="reusable"
                                       value="1"
                                       id="reusableCheckbox"
                                       {{ old('reusable', $voucher->reusable ?? 0) ? 'checked' : '' }}>
                            </div>
                        </div>

                        {{-- REMAINING VALUE --}}
                        <div class="valueType-REMAININGVALUE row" style="display:none">
                            <label class="col-md-3">Remaining Value</label>
                            <div class="col-md-6 input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="remainingValue"
                                       class="form-control"
                                       value="{{ old('remainingValue', $voucher->remaining_value ?? '') }}">
                            </div>
                        </div>

                        {{-- PRODUCT --}}
                        <div class="valueType-PRODUCT row mt-2" style="display:none">
                            <label class="col-md-3">Product</label>
                            <div class="col-md-6 input-group">
                                <select class="form-control" name="productId">
                                    <option value="">Select...</option>
                                    @foreach($tours as $tour)
                                        <option value="{{ $tour->id }}"
                                            {{ old('productId', $voucher->product_id ?? '') == $tour->id ? 'selected' : '' }}>
                                            {{ $tour->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- CATEGORY --}}
                        <div class="valueType-CATEGORY row" style="display:none">
                            <label class="col-md-3">Category</label>
                            <div class="col-md-6">
                                <input type="number"
                                       name="categoryId"
                                       class="form-control"
                                       value="{{ old('categoryId', $voucher->category_id ?? '') }}">
                            </div>
                        </div>

                    </div>

                    <hr>

                    {{-- NOTES --}}
                    <div class="mb-3">
                        <label>Internal Notes</label>
                        <textarea name="internalNotes" rows="3" class="form-control">{{ old('internalNotes',$voucher->internal_notes) }}</textarea>
                    </div>

                    <button class="btn btn-success btn-block"><i class="fas fa-save"></i>  Update Voucher</button>
                </form>
            </div>

            {{-- HELP --}}
            <div class="col-md-3">
                <div class="help-panel">
                    <div class="help-title">Field Help</div>
                    <div id="helpContent">Click on a field</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= JS ================= --}}
@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const createMode = document.getElementById('createMode');
    const manualBox = document.getElementById('manualCodesBox');
    const quantityBox = document.getElementById('quantityBox');

    function toggleCreateMode(){
        manualBox.classList.toggle('d-none', createMode.value !== 'MANUAL');
        quantityBox.classList.toggle('d-none', createMode.value !== 'Automactic');
    }

    createMode.addEventListener('change', toggleCreateMode);
    toggleCreateMode();

    const reusable = document.getElementById('reusableCheckbox');
    const remainingBox = document.getElementById('remainingValueBox');

    function toggleRemaining(){
        remainingBox.style.display = reusable.checked ? 'block' : 'none';
    }

    reusable.addEventListener('change', toggleRemaining);
    toggleRemaining();
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const valueTypeSelect = document.getElementById("Voucher_valueType");
    const container = document.querySelector(".valueTypeContainer");
    const reusableCheckbox = document.getElementById("reusableCheckbox");
    const remainingRow = document.querySelector(".valueType-REMAININGVALUE");
    const remainingInput = document.querySelector('input[name="remainingValue"]');
    
    function hideAll() {
        document
            .querySelectorAll(".valueTypeContainer .row")
            .forEach(el => el.style.display = "none");
    }

    function show(selector) {
        document
            .querySelectorAll(selector)
            .forEach(el => el.style.display = "flex");
    }

    function toggleRemainingValue() {
        if (!reusableCheckbox || !remainingRow) return;

        if (reusableCheckbox.checked && valueTypeSelect.value === "VALUE") {
            remainingRow.style.display = "flex";
        } else {
            remainingRow.style.display = "none";
            if (remainingInput) remainingInput.value = "";
        }
    }

    function toggleValueType() {
        const type = valueTypeSelect.value;

        container.style.display = type ? "block" : "none";
        hideAll();

        if (type === "VALUE") {
            show(".valueType-VALUE");
        }

        if (type === "VALUE_LIMITPRODUCT") {
            show(".valueType-VALUE");
            show(".valueType-PRODUCT");
        }

        if (type === "VALUE_LIMITCATEGORY") {
            show(".valueType-VALUE");
            show(".valueType-CATEGORY");
        }

        if (type === "PRODUCT") {
            show(".valueType-PRODUCT");
        }

        toggleRemainingValue();
    }

    valueTypeSelect.addEventListener("change", toggleValueType);
    if (reusableCheckbox) {
        reusableCheckbox.addEventListener("change", toggleRemainingValue);
    }

    // ✅ Run once for EDIT mode
    toggleValueType();
});
</script>

@endsection

</x-admin>
