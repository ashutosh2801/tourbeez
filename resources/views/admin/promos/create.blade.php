<x-admin>
    @section('title', 'Create Promo')
    <div class="row justify-content-center">
        <div class="col-12">

            <!-- TOP HEADER -->
            <div class="card-primary mb-3">
                <div class="card-header create-extra-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="card-title">Create Promo</h3>
                        </div>
                        <div class="col-md-4">
                            <div class="card-tools text-right">
                                <a href="{{ route('admin.promos.index') }}" class="btn btn-back">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM CARD -->
            <div class="card-primary bg-white border rounded-lg-custom">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="promoForm" action="{{ route('admin.promos.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <div class="card-body col-md-9">

                            {{-- CODE --}}
                            <div class="form-group">
                                <label>Promo Code <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('Promos.code') is-invalid @enderror"
                                    name="Promos[code]"
                                    id="Promos_code"
                                    placeholder="Enter promo code"
                                    value="{{ old('Promos.code') }}"
                                    required
                                    data-help="help_promo_code"
                                >
                                @error('Promos.code')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                                <div class="invalid-feedback d-none" id="err_Promos_code">Promo code is required.</div>
                            </div>

                            {{-- STATUS (keep same name) --}}
                            <!-- <div class="form-group">
                                <label>Status</label>
                                <select
                                    class="form-control"
                                    name="Promos[status]"
                                    id="Promos_status"
                                    data-help="help_status"
                                >
                                    <option value="ISSUED" {{ old('Promos.status') == 'ISSUED' ? 'selected' : '' }}>Issued</option>
                                    <option value="EXPIRED" {{ old('Promos.status') == 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div> -->

                            {{-- APPLIED ON --}}
                            <div class="form-group">
                                <label>Applied once per</label>
                                <select
                                    class="form-control"
                                    name="Promos[quantityRule]"
                                    id="Promos_quantityRule"
                                    data-help="help_quantity_rule"
                                >
                                    <option value="ORDER" {{ old('Promos.quantityRule') == 'ORDER' ? 'selected' : '' }}>Order</option>
                                    <option value="PRODUCT" {{ old('Promos.quantityRule') == 'PRODUCT' ? 'selected' : '' }}>Product</option>
                                    <option value="QUANTITY" {{ old('Promos.quantityRule') == 'QUANTITY' ? 'selected' : '' }}>Quantity</option>
                                </select>
                            </div>

                            {{-- VALIDITY DATE --}}
                            <label>Validity Date</label>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="input-group date form_date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">From</span>
                                        </div>
                                        <input
                                            type="text"
                                            class="aiz-date-range form-control"
                                            name="Promos[issueDate]"
                                            id="Promos_issueDate"
                                            placeholder="Select Date"
                                            data-single="true"
                                            data-show-dropdown="true"
                                            value="{{ old('Promos.issueDate') }}"
                                            data-help="help_start_date"
                                            readonly
                                        >
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="input-group date form_date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">To</span>
                                        </div>
                                        <input
                                            type="text"
                                            class="aiz-date-range form-control"
                                            name="Promos[expiryDate]"
                                            id="Promos_expiryDate"
                                            placeholder="Select Date"
                                            data-single="true"
                                            data-show-dropdown="true"
                                            value="{{ old('Promos.expiryDate') }}"
                                            data-help="help_end_date"
                                            readonly
                                        >
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TRAVEL DATE --}}
                            <label>Travel Date</label>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="input-group date form_date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">From</span>
                                        </div>
                                        <input
                                            type="text"
                                            class="aiz-date-range form-control"
                                            name="Promos[travelFromDate]"
                                            id="Promos_travelFromDate"
                                            placeholder="Select Date"
                                            data-single="true"
                                            data-show-dropdown="true"
                                            value="{{ old('Promos.travelFromDate') }}"
                                            data-help="help_travel_from"
                                            readonly
                                        >
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="input-group date form_date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">To</span>
                                        </div>
                                        <input
                                            type="text"
                                            class="aiz-date-range form-control"
                                            name="Promos[travelToDate]"
                                            id="Promos_travelToDate"
                                            placeholder="Select Date"
                                            data-single="true"
                                            data-show-dropdown="true"
                                            value="{{ old('Promos.travelToDate') }}"
                                            data-help="help_travel_to"
                                            readonly
                                        >
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- REDEEM DAYS --}}
                            <div class="form-group">
                                <label>Can only be redeemed on</label><br>
                                @foreach(['MON','TUE','WED','THU','FRI','SAT','SUN'] as $day)
                                    <label class="mr-3">
                                        <input
                                            type="checkbox"
                                            name="validRedemptionDaysIndex[]"
                                            value="{{ $loop->iteration }}"
                                            data-help="help_valid_days"
                                            {{ (is_array(old('validRedemptionDaysIndex')) && in_array($loop->iteration, old('validRedemptionDaysIndex'))) ? 'checked' : '' }}
                                        >
                                        <strong>{{ $day }}</strong>
                                    </label>
                                @endforeach
                            </div>

                            {{-- REDEMPTION LIMIT --}}
                            <div class="form-group">
                                <label>Redemption Limit</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select id="limit_selection" class="form-control" name="Promos[redemptionLimit]" data-help="help_usage_limit">
                                            <option value="UNLIMITED" {{ old('Promos.redemptionLimit') == 'UNLIMITED' ? 'selected' : '' }}>Unlimited</option>
                                            <option value="LIMITED" {{ old('Promos.redemptionLimit') == 'LIMITED' ? 'selected' : '' }}>Limited</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 {{ old('Promos.redemptionLimit') == 'LIMITED' ? '' : 'd-none' }}" id="limit_input">
                                        <input
                                            type="text"
                                            class="form-control @error('Promos.maxUses') is-invalid @enderror"
                                            name="Promos[maxUses]"
                                            id="Promos_maxUses"
                                            placeholder="Enter limit"
                                            value="{{ old('Promos.maxUses') }}"
                                            data-help="help_usage_limit"
                                        >
                                        @error('Promos.maxUses')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                        <div class="invalid-feedback d-none" id="err_Promos_maxUses">Please enter a valid integer limit.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- MIN AMOUNT --}}
                            <div class="form-group">
                                <label>Minimum Order Amount</label>
                                <div class="input-group col-md-6 p-0">
                                    <span class="input-group-text">$</span>
                                    <input
                                        type="text"
                                        name="Promos[minAmount]"
                                        id="Promos_minAmount"
                                        class="form-control @error('Promos.minAmount') is-invalid @enderror"
                                        placeholder="0.00"
                                        value="{{ old('Promos.minAmount') }}"
                                        data-help="help_min_order"
                                    >
                                </div>
                                @error('Promos.minAmount')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- INCLUDE TAXES --}}
                            <div class="form-group">
                                <label>
                                    <input type="hidden" name="Promos[includeTaxesAndFees]" value="0">
                                    <input type="checkbox" name="Promos[includeTaxesAndFees]" value="1" {{ old('Promos.includeTaxesAndFees', 1) ? 'checked' : '' }} data-help="help_taxes"> Include taxes & fees
                                </label>
                            </div>

                            {{-- INCLUDE EXTRAS --}}
                            <div class="form-group">
                                <label>
                                    <input type="hidden" name="Promos[includeExtras]" value="0">
                                    <input type="checkbox" name="Promos[includeExtras]" value="1" {{ old('Promos.includeExtras', 1) ? 'checked' : '' }} data-help="help_extras"> Include extras
                                </label>
                            </div>

                            {{-- INTERNAL --}}
                            <div class="form-group">
                                <label>
                                    <input type="hidden" name="Promos[internal]" value="0">
                                    <input type="checkbox" name="Promos[internal]" value="1" {{ old('Promos.internal') ? 'checked' : '' }} data-help="help_internal"> Hide code from customers
                                </label>
                            </div>

                            {{-- ======================= VALUE TYPE SECTION ======================= --}}
                            <div class="card mt-4 p-3">

                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label required">Value <span class="text-danger">*</span></label>

                                    <div class="col-md-10">
                                        <select
                                            class="form-control @error('Promos.valueType') is-invalid @enderror"
                                            name="Promos[valueType]"
                                            id="Promos_valueType"
                                            data-help="help_discount_type"
                                            required
                                        >
                                            <option value="">Select...</option>
                                            <option value="VALUE_LIMITPRODUCT" {{ old('Promos.valueType') == 'VALUE_LIMITPRODUCT' ? 'selected' : '' }}>Fixed amount for one product</option>
                                            <option value="VALUE" {{ old('Promos.valueType') == 'VALUE' ? 'selected' : '' }}>Fixed amount for any product</option>
                                            <option value="VALUE_LIMITCATALOG" {{ old('Promos.valueType') == 'VALUE_LIMITCATALOG' ? 'selected' : '' }}>Fixed amount for category</option>
                                            <option value="PERCENT_LIMITPRODUCT" {{ old('Promos.valueType') == 'PERCENT_LIMITPRODUCT' ? 'selected' : '' }}>% discount for one product</option>
                                            <option value="PERCENT" {{ old('Promos.valueType') == 'PERCENT' ? 'selected' : '' }}>% discount for any product</option>
                                            <option value="PERCENT_LIMITCATALOG" {{ old('Promos.valueType') == 'PERCENT_LIMITCATALOG' ? 'selected' : '' }}>% discount for category</option>
                                        </select>

                                        @error('Promos.valueType')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                        <div class="invalid-feedback d-none" id="err_Promos_valueType">Please select a value type.</div>

                                        <small class="text-muted d-block mt-2">
                                            <strong>Fixed amount for one product</strong> — use this when discount applies only to one specific product.  
                                            <br><strong>Fixed amount for any product</strong> — apply a flat discount to entire cart.  
                                            <br><strong>% discount</strong> — percentage-based discount for product/cart/category.  
                                        </small>
                                    </div>
                                </div>

                                {{-- VALUE TYPE: FIXED AMOUNT --}}
                                <div class="valueType valueType-VALUE row mt-3" style="display:none;">
                                    <label class="col-md-2 col-form-label">Value</label>
                                    <div class="col-md-4 input-group">
                                        <span class="input-group-text">$</span>
                                        <input
                                            type="text"
                                            name="Promos[voucherValue]"
                                            id="Promos_voucherValue"
                                            class="form-control"
                                            value="{{ old('Promos.voucherValue') }}"
                                            data-help="help_discount_value"
                                        >
                                    </div>
                                </div>

                                {{-- VALUE TYPE: PERCENT --}}
                                <div class="valueType valueType-PERCENT row mt-3" style="display:none;">
                                    <label class="col-md-2 col-form-label">Discount</label>
                                    <div class="col-md-4 input-group">
                                        <input
                                            type="text"
                                            name="Promos[valuePercent]"
                                            id="Promos_valuePercent"
                                            class="form-control"
                                            value="{{ old('Promos.valuePercent') }}"
                                            data-help="help_discount_value"
                                        >
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                {{-- VALUE TYPE: PRODUCT --}}
                                <div class="valueType valueType-PRODUCT row mt-3" style="display:none;">
                                    <label class="col-md-2 col-form-label">Product</label>
                                    <div class="col-md-6">
                                        <select name="Product[id]" id="Product_id" class="form-control" data-help="help_applicable_product">
                                            <option value="">Select...</option>
                                            @foreach($tours as $tour)
                                            
                                                <option value="{{$tour->id}}">{{ $tour->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- PRODUCT QUANTITIES --}}
                                <div class="valueType valueType-PRODUCT-quantities-holder row mt-3" style="display:none;">
                                    <label class="col-md-2 col-form-label">Quantities</label>
                                    <div class="col-md-6 valueType-PRODUCT-quantities">
                                        {{-- JS dynamically adds quantity selectors --}}
                                    </div>
                                </div>

                                {{-- CATEGORY --}}
                                <div class="valueType valueType-CATALOG row mt-3" style="display:none;">
                                    <label class="col-md-2 col-form-label">Category</label>
                                    <div class="col-md-6">
                                        <select name="Category[id]" id="Category_id" class="form-control" data-help="help_applicable_product">
                                            <option value="">Select...</option>

                                            @foreach($categories as $category)
                                                <option value="{{ $category->id}}">{{ $category->name}}</option>
                                            @endforeach
                                            
                                        </select>
                                    </div>
                                </div>

                                {{-- PRICE OPTION --}}
                                <div class="valueType valueType-PRODUCT-price-option-holder row mt-3" style="display:none;">
                                    <label class="col-md-2 col-form-label">Apply Discount To</label>
                                    <div class="col-md-6 choose-price-options valueType-PRODUCT-price-option">
                                        {{-- JS will load product price options --}}
                                    </div>
                                </div>

                            </div>

                            {{-- ======================= INTERNAL NOTES ======================= --}}
                            <div class="form-group row mt-4">
                                <label for="Promos_internalNotes" class="col-md-2 col-form-label">Internal Notes</label>
                                <div class="col-md-10">
                                    <textarea class="form-control" rows="5" name="Promos[internalNotes]" id="Promos_internalNotes" data-help="help_description">{{ old('Promos.internalNotes') }}</textarea>
                                </div>
                            </div>

                        </div>

                        <!-- SIDE HELP PANEL -->
                        <div class="col-md-3 side-panel">
                            <div class="well contextual-help-wrap">
                                <div id="help_promo_code" class="help-item" style="display:none;">
                                    <h4>Promo Code</h4>
                                    <p>The code customers enter at checkout. Must be unique and contain only alphanumeric characters.</p>
                                </div>

                                <div id="help_status" class="help-item" style="display:none;">
                                    <h4>Status</h4>
                                    <p>This sets whether the promo is issued or expired. Expired promos cannot be applied.</p>
                                </div>

                                <div id="help_quantity_rule" class="help-item" style="display:none;">
                                    <h4>Applied once per</h4>
                                    <p>How many times the discount should apply: per order, per product, or per quantity.</p>
                                </div>

                                <div id="help_start_date" class="help-item" style="display:none;">
                                    <h4>Start Date</h4>
                                    <p>The date this promo becomes valid for bookings.</p>
                                </div>

                                <div id="help_end_date" class="help-item" style="display:none;">
                                    <h4>End Date</h4>
                                    <p>The date after which the promo will no longer be valid.</p>
                                </div>

                                <div id="help_travel_from" class="help-item" style="display:none;">
                                    <h4>Travel From</h4>
                                    <p>Start of travel period this promo applies to (optional).</p>
                                </div>

                                <div id="help_travel_to" class="help-item" style="display:none;">
                                    <h4>Travel To</h4>
                                    <p>End of travel period this promo applies to (optional).</p>
                                </div>

                                <div id="help_valid_days" class="help-item" style="display:none;">
                                    <h4>Valid Redemption Days</h4>
                                    <p>Select which weekdays customers can redeem the promo. Leave empty to allow all days.</p>
                                </div>

                                <div id="help_usage_limit" class="help-item" style="display:none;">
                                    <h4>Usage Limit</h4>
                                    <p>Set a maximum number of total uses. Use 'Unlimited' to allow unlimited redemptions.</p>
                                </div>

                                <div id="help_min_order" class="help-item" style="display:none;">
                                    <h4>Minimum Order Amount</h4>
                                    <p>Promo will only apply if the order amount is equal to or greater than this value.</p>
                                </div>

                                <div id="help_taxes" class="help-item" style="display:none;">
                                    <h4>Include Taxes & Fees</h4>
                                    <p>If checked, discount is applied including taxes and fees calculations.</p>
                                </div>

                                <div id="help_extras" class="help-item" style="display:none;">
                                    <h4>Include Extras</h4>
                                    <p>If checked, discount also applies to extras (add-ons).</p>
                                </div>

                                <div id="help_internal" class="help-item" style="display:none;">
                                    <h4>Internal</h4>
                                    <p>Internal promos are hidden from customers and can be manually applied by staff.</p>
                                </div>

                                <div id="help_discount_type" class="help-item" style="display:none;">
                                    <h4>Discount Type</h4>
                                    <p>Choose a value type — fixed amounts or percentage. Selecting a value type will show the related input fields.</p>
                                </div>

                                <div id="help_discount_value" class="help-item" style="display:none;">
                                    <h4>Discount Value</h4>
                                    <p>Enter the numeric value of the discount (dollars or percentage depending on type).</p>
                                </div>

                                <div id="help_applicable_product" class="help-item" style="display:none;">
                                    <h4>Applicable Product/Category</h4>
                                    <p>Select which product or category the promo applies to when using product/category limited value types.</p>
                                </div>

                                <div id="help_description" class="help-item" style="display:none;">
                                    <h4>Internal Notes</h4>
                                    <p>Notes for your team about this promo — not visible to customers if internal is checked.</p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button id="promoSaveBtn" type="submit" class="btn btn-success float-right">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

@section('js')
<script>
    $(document).ready(function () {

        // ------------- HELP PANEL SHOW/HIDE -------------
        // Map fields with data-help attribute to show the right help block
        $('[data-help]').on('focus change', function () {
            // hide all help-items first
            $('.help-item').hide();

            // get help id and show it
            var helpId = $(this).data('help');
            if (helpId) {
                $('#' + helpId).show();
            }
        });

        // Also show help when checkboxes/selects are clicked
        $('input[type="checkbox"], select').on('change', function () {
            $('.help-item').hide();
            var helpId = $(this).data('help');
            if (helpId) {
                $('#' + helpId).show();
            }
        });

        // hide help when clicking outside fields
        $(document).on('click', function (e) {
            if (!$(e.target).closest('[data-help], .contextual-help-wrap').length) {
                $('.help-item').hide();
            }
        });

        // ------------- REDEMPTION LIMIT SHOW/HIDE -------------
        function toggleLimitInput() {
            if ($('#limit_selection').val() === 'LIMITED') {
                $('#limit_input').removeClass('d-none');
            } else {
                $('#limit_input').addClass('d-none');
                $('#Promos_maxUses').val('');
                $('#err_Promos_maxUses').addClass('d-none');
            }
        }
        $('#limit_selection').on('change', toggleLimitInput);
        toggleLimitInput(); // initial call

        // ------------- VALUE TYPE SHOW/HIDE -------------
        function hideAllValueTypes() {
            $('.valueType').hide();
        }

        function showValueTypeBlocks(selected) {
            hideAllValueTypes();

            if (!selected) return;

            if (selected.indexOf('VALUE') === 0) {
                $('.valueType-VALUE').show();
            }

            if (selected.indexOf('PERCENT') === 0) {
                $('.valueType-PERCENT').show();
            }

            if (selected.indexOf('PRODUCT') !== -1) {
                $('.valueType-PRODUCT').show();
                $('.valueType-PRODUCT-quantities-holder').show();
                $('.valueType-PRODUCT-price-option-holder').show();
            } else {
                $('.valueType-PRODUCT-quantities-holder').hide();
                $('.valueType-PRODUCT-price-option-holder').hide();
            }

            if (selected.indexOf('CATALOG') !== -1) {
                $('.valueType-CATALOG').show();
            }
        }

        $('#Promos_valueType').on('change', function () {
            var val = $(this).val();
            showValueTypeBlocks(val);

            // show help for discount type
            $('.help-item').hide();
            var helpId = $(this).data('help');
            if (helpId) {
                $('#' + helpId).show();
            }
        });

        // initialize based on old inputs (if any)
        showValueTypeBlocks($('#Promos_valueType').val());

        // ------------- SIMPLE CLIENT-SIDE VALIDATION -------------
        function showFieldError(fieldId, message) {
            var $el = $('#' + fieldId);
            $el.addClass('is-invalid');
            $('#err_' + fieldId).text(message).removeClass('d-none');
        }

        function clearFieldError(fieldId) {
            $('#' + fieldId).removeClass('is-invalid');
            $('#err_' + fieldId).addClass('d-none');
        }

        $('#promoForm').on('submit', function (e) {
            // clear previous errors
            $('.invalid-feedback').addClass('d-none');
            $('.is-invalid').removeClass('is-invalid');

            var valid = true;

            // required: Promos[code]
            if (!$.trim($('#Promos_code').val())) {
                valid = false;
                showFieldError('Promos_code', 'Promo code is required.');
            } else {
                clearFieldError('Promos_code');
            }

            // required: Promos[valueType]
            if (!$.trim($('#Promos_valueType').val())) {
                valid = false;
                showFieldError('Promos_valueType', 'Please select a value type.');
            } else {
                clearFieldError('Promos_valueType');
            }

            // if limited selected -> maxUses must be integer > 0
            if ($('#limit_selection').val() === 'LIMITED') {
                var maxUsesVal = $.trim($('#Promos_maxUses').val());
                if (!maxUsesVal || !/^\d+$/.test(maxUsesVal) || parseInt(maxUsesVal) <= 0) {
                    valid = false;
                    showFieldError('Promos_maxUses', 'Please enter a valid integer limit.');
                } else {
                    clearFieldError('Promos_maxUses');
                }
            }

            // value-type specific checks
            var vt = $('#Promos_valueType').val();
            if (vt) {
                if (vt.indexOf('VALUE') === 0) {
                    var voucherValue = $.trim($('#Promos_voucherValue').val());
                    if (!voucherValue || !/^\d+(\.\d+)?$/.test(voucherValue)) {
                        valid = false;
                        showFieldError('Promos_voucherValue', 'Enter a valid amount.');
                    } else {
                        clearFieldError('Promos_voucherValue');
                    }
                }
                if (vt.indexOf('PERCENT') === 0) {
                    var percentVal = $.trim($('#Promos_valuePercent').val());
                    if (!percentVal || !/^\d+(\.\d+)?$/.test(percentVal) || parseFloat(percentVal) <= 0) {
                        valid = false;
                        showFieldError('Promos_valuePercent', 'Enter a valid percent (> 0).');
                    } else {
                        clearFieldError('Promos_valuePercent');
                    }
                }
                // product/category selection checks can be added similarly if required
            }

            if (!valid) {
                e.preventDefault();
                // scroll to first invalid element for convenience
                var $firstInvalid = $('.is-invalid').first();
                if ($firstInvalid.length) {
                    $('html, body').animate({ scrollTop: $firstInvalid.offset().top - 80 }, 300);
                }
                return false;
            }

            // allow normal submit to go through (server-side will validate too)
            return true;
        });

        // Clear field error on input
        $('input, select, textarea').on('input change', function () {
            var id = $(this).attr('id');
            if (id) {
                clearFieldError(id);
            }
        });

    });
</script>
@endsection
</x-admin>
