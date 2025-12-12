<x-admin>
@section('title', 'Edit Promo')

<div class="row justify-content-center">
    <div class="col-12">

        <!-- TOP HEADER -->
        <div class="card-primary mb-3">
            <div class="card-header create-extra-header">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="card-title">Edit Promo</h3>
                    </div>
                    <div class="col-md-4">
                        <div class="card-tools text-right">
                            <a href="{{ route('admin.promos.index') }}" class="btn btn-dark">Back</a>
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

            <form id="promoForm" action="{{ route('admin.promos.update', $promo->id) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="card-body col-md-9">

                        {{-- CODE --}}
                        <div class="form-group">
                            <label>Promo Code <span class="text-danger">*</span></label>
                            <input type="text" name="Promos[code]" id="Promo_code"
                                class="form-control @error('Promos.code') is-invalid @enderror"
                                value="{{ old('Promos.code', $promo->code) }}" data-help="help_promo_code">
                            @error('Promos.code')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- STATUS --}}
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="Promos[status]" id="Promo_status" data-help="help_status">
                                <option value="ISSUED" {{ old('Promos.status', $promo->status)=='ISSUED' ? 'selected' : '' }}>Issued</option>
                                <option value="EXPIRED" {{ old('Promos.status', $promo->status)=='EXPIRED' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>

                        {{-- APPLIED ON --}}
                        <div class="form-group">
                            <label>Applied once per</label>
                            <select class="form-control" name="Promos[quantityRule]" id="Promo_quantityRule" data-help="help_quantity_rule">
                                <option value="ORDER" {{ old('Promos.quantityRule', $promo->quantityRule)=='ORDER' ? 'selected' : '' }}>Order</option>
                                <option value="PRODUCT" {{ old('Promos.quantityRule', $promo->quantityRule)=='PRODUCT' ? 'selected' : '' }}>Product</option>
                                <option value="QUANTITY" {{ old('Promos.quantityRule', $promo->quantityRule)=='QUANTITY' ? 'selected' : '' }}>Quantity</option>
                            </select>
                        </div>

                        {{-- VALIDITY DATE --}}
                        <label>Validity Date</label>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="date" name="Promos[issueDate]" id="Promo_issueDate" class="form-control" value="{{ old('Promos.issueDate', $promo->issueDate) }}" data-help="help_start_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="date" name="Promos[expiryDate]" id="Promo_expiryDate" class="form-control" value="{{ old('Promos.expiryDate', $promo->expiryDate) }}" data-help="help_end_date">
                            </div>
                        </div>

                        {{-- TRAVEL DATE --}}
                        <label>Travel Date</label>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="date" name="Promos[travelFromDate]" id="Promo_travelFromDate" class="form-control" value="{{ old('Promos.travelFromDate', $promo->travelFromDate) }}" data-help="help_travel_from">
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="date" name="Promos[travelToDate]" id="Promo_travelToDate" class="form-control" value="{{ old('Promos.travelToDate', $promo->travelToDate) }}" data-help="help_travel_to">
                            </div>
                        </div>

                        {{-- REDEEM DAYS --}}
                        <div class="form-group">
                            <label>Can only be redeemed on</label><br>
                            @foreach(['MON','TUE','WED','THU','FRI','SAT','SUN'] as $day)
                            <label class="mr-3">
                                <input type="checkbox" name="validRedemptionDaysIndex[]" value="{{ $loop->iteration }}" {{ in_array($loop->iteration, old('validRedemptionDaysIndex', $promo->validRedemptionDaysIndex ?? [])) ? 'checked' : '' }} data-help="help_valid_days">
                                <strong>{{ $day }}</strong>
                            </label>
                            @endforeach
                        </div>

                        {{-- REDEMPTION LIMIT --}}
                        <div class="form-group">
                            <label>Redemption Limit</label>
                            <select id="limit_selection" class="form-control" name="Promos[redemptionLimit]" data-help="help_usage_limit">
                                <option value="UNLIMITED" {{ old('Promos.redemptionLimit', $promo->redemptionLimit)=='UNLIMITED' ? 'selected' : '' }}>Unlimited</option>
                                <option value="LIMITED" {{ old('Promos.redemptionLimit', $promo->redemptionLimit)=='LIMITED' ? 'selected' : '' }}>Limited</option>
                            </select>
                            <input type="number" name="Promos[maxUses]" id="limit_input" class="form-control mt-2 {{ old('Promos.redemptionLimit', $promo->redemptionLimit)=='LIMITED' ? '' : 'd-none' }}" placeholder="Enter max uses" value="{{ old('Promos.maxUses', $promo->maxUses) }}">
                        </div>

                        {{-- MIN AMOUNT --}}
                        <div class="form-group">
                            <label>Minimum Order Amount</label>
                            <input type="number" name="Promos[minAmount]" id="Promo_minAmount" class="form-control" value="{{ old('Promos.minAmount', $promo->minAmount) }}" data-help="help_min_order">
                        </div>

                        {{-- INCLUDE TAXES --}}
                        <div class="form-group">
                            <input type="hidden" name="Promos[includeTaxesAndFees]" value="0">
                            <label>
                                <input type="checkbox" name="Promos[includeTaxesAndFees]" value="1" {{ old('Promos.includeTaxesAndFees', $promo->includeTaxesAndFees) ? 'checked' : '' }}> Include taxes & fees
                            </label>
                        </div>

                        {{-- INCLUDE EXTRAS --}}
                        <div class="form-group">
                            <input type="hidden" name="Promos[includeExtras]" value="0">
                            <label>
                                <input type="checkbox" name="Promos[includeExtras]" value="1" {{ old('Promos.includeExtras', $promo->includeExtras) ? 'checked' : '' }}> Include extras
                            </label>
                        </div>

                        {{-- INTERNAL --}}
                        <div class="form-group">
                            <input type="hidden" name="Promos[internal]" value="0">
                            <label>
                                <input type="checkbox" name="Promos[internal]" value="1" {{ old('Promos.internal', $promo->internal) ? 'checked' : '' }}> Hide code from customers
                            </label>
                        </div>

                        {{-- VALUE TYPE --}}
                        <div class="form-group">
                            <label>Value Type</label>
                            <select name="Promos[valueType]" id="Promo_valueType" class="form-control" data-help="help_discount_type">
                                <option value="">Select...</option>
                                <option value="VALUE" {{ old('Promos.valueType', $promo->valueType)=='VALUE' ? 'selected' : '' }}>Fixed amount</option>
                                <option value="PERCENT" {{ old('Promos.valueType', $promo->valueType)=='PERCENT' ? 'selected' : '' }}>Percent</option>
                            </select>
                        </div>

                        {{-- VALUE --}}
                        <div class="form-group valueType valueType-VALUE {{ old('Promos.valueType', $promo->valueType)=='VALUE' ? '' : 'd-none' }}">
                            <label>Value</label>
                            <input type="number" name="Promos[voucherValue]" class="form-control" value="{{ old('Promos.voucherValue', $promo->voucherValue) }}" placeholder="Fixed amount">
                        </div>

                        <div class="form-group valueType valueType-PERCENT {{ old('Promos.valueType', $promo->valueType)=='PERCENT' ? '' : 'd-none' }}">
                            <label>Percent</label>
                            <input type="number" name="Promos[valuePercent]" class="form-control" value="{{ old('Promos.valuePercent', $promo->valuePercent) }}" placeholder="% Discount">
                        </div>

                        {{-- INTERNAL NOTES --}}
                        <div class="form-group">
                            <label>Internal Notes</label>
                            <textarea name="Promos[internalNotes]" class="form-control" rows="5">{{ old('Promos.internalNotes', $promo->internalNotes) }}</textarea>
                        </div>

                    </div>

                    <!-- HELP PANEL -->
                    <div class="col-md-3 side-panel">
                        <div class="well contextual-help-wrap">
                            <div id="help_promo_code" class="help-item" style="display:none;"><h4>Promo Code</h4><p>The code customers enter at checkout. Must be unique.</p></div>
                            <div id="help_status" class="help-item" style="display:none;"><h4>Status</h4><p>Active or expired status.</p></div>
                            <div id="help_quantity_rule" class="help-item" style="display:none;"><h4>Applied once per</h4><p>Where promo applies: order, product, or quantity.</p></div>
                            <div id="help_start_date" class="help-item" style="display:none;"><h4>Start Date</h4><p>Date from which promo is active.</p></div>
                            <div id="help_end_date" class="help-item" style="display:none;"><h4>End Date</h4><p>Date after which promo expires.</p></div>
                            <div id="help_travel_from" class="help-item" style="display:none;"><h4>Travel From</h4><p>Travel period start (optional).</p></div>
                            <div id="help_travel_to" class="help-item" style="display:none;"><h4>Travel To</h4><p>Travel period end (optional).</p></div>
                            <div id="help_valid_days" class="help-item" style="display:none;"><h4>Valid Redemption Days</h4><p>Select allowed days for redemption.</p></div>
                            <div id="help_usage_limit" class="help-item" style="display:none;"><h4>Usage Limit</h4><p>Limit total usage count or unlimited.</p></div>
                            <div id="help_min_order" class="help-item" style="display:none;"><h4>Minimum Order Amount</h4><p>Promo applies only above this amount.</p></div>
                            <div id="help_discount_type" class="help-item" style="display:none;"><h4>Discount Type</h4><p>Choose fixed or percent.</p></div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success float-right">Update Promo</button>
                </div>

            </form>
        </div>
    </div>
</div>

@section('js')
<script>
$(document).ready(function(){

    // Contextual Help
    $('[data-help]').on('focus change click', function () {
        $('.help-item').hide();
        let helpId = $(this).data('help');
        if(helpId) $('#'+helpId).show();
    });
    $(document).on('click', function(e){
        if(!$(e.target).closest('[data-help], .contextual-help-wrap').length){
            $('.help-item').hide();
        }
    });

    // Toggle max uses input
    function toggleLimitInput(){
        if($('#limit_selection').val() === 'LIMITED'){
            $('#limit_input').removeClass('d-none');
        } else {
            $('#limit_input').addClass('d-none').val('');
        }
    }
    $('#limit_selection').on('change', toggleLimitInput);
    toggleLimitInput();

    // Toggle valueType fields
    function showValueTypeBlocks(selected){
        $('.valueType').hide();
        if(!selected) return;
        if(selected=='VALUE') $('.valueType-VALUE').show();
        if(selected=='PERCENT') $('.valueType-PERCENT').show();
    }
    showValueTypeBlocks($('#Promo_valueType').val());
    $('#Promo_valueType').on('change', function(){
        showValueTypeBlocks($(this).val());
    });

});
</script>
@endsection

</x-admin>
