<x-admin>
    @section('title','Update Tax or Fee')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card-primary mb-3">
                <div class="card-header taxes-fee-head">
                    <div class="row">
                        <div class="col-md-8 col-7">
                            <h3 class="card-title text-white">Update Tax and Fee</h3>
                        </div>
                        <div class="col-md-4 col-5">
                            <div class="card-tools">
                                <a href="{{ route('admin.taxes.index') }}" class="btn btn-back btn-sm">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card taxes-fee-body">
                <div class="card-primary bg-white border rounded-lg-custo">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="needs-validation" novalidate action="{{ route('admin.taxes.update', $taxfee->id) }}" 
                    method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="card-body">

                            <div class="form-group mb-3">
                                <label for="name">Label *</label>
                                <input type="text" class="form-control" id="label" name="label"
                                    placeholder="Enter label" required value="{{ $taxfee->label }}">
                                @error('name')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror 
                            </div>

                            <div class="form-group mb-3">
                                <label for="pickup_time">Tax or Fee *</label>
                                <select class="form-control aiz-selectpicker" id="tax_fee_type" name="tax_fee_type" onchange="taxfeeType(this.value)">
                                    <option {{ $taxfee->tax_fee_type == 'TAX' ? 'selected' : '' }} value="TAX">Tax</option>                                    
                                    <option {{ $taxfee->tax_fee_type == 'FEE' ? 'selected' : '' }} value="FEE">Fee</option>                                        
                                </select>
                                @error('tax_fee_type')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group hidden mb-3" id="taxfeeType">
                                <label for="pickup_time">Value *</label>
                                <select class="form-control aiz-selectpicker" name="fee_type" id="fee_type" onchange="taxPercent(this.value)">
                                    
                                    <option {{ $taxfee->fee_type == 'FIXED_PER_ORDER' ? 'selected' : '' }} value="FIXED_PER_ORDER">Fixed per order item</option>
                                    <option {{ $taxfee->fee_type == 'PERCENT' ? 'selected' : '' }} value="PERCENT">Percent</option>
                                    <option {{ $taxfee->fee_type == 'FIXED_PER_QUANTITY' ? 'selected' : '' }} value="FIXED_PER_QUANTITY">Fixed per quantity</option>
                                    <option {{ $taxfee->fee_type == 'FIXED_PER_DURATION' ? 'selected' : '' }} value="FIXED_PER_DURATION">Fixed per duration</option>
                                </select>
                                @error('tax_fee_type')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="name" id="percentTitle">Percent *</label>
                                <div class="input-group ">                                    
                                    <input type="text" class="form-control col-md-12" id="tax_fee_value" name="tax_fee_value"
                                        placeholder="Enter value" required value="{{ $taxfee->tax_fee_value }}" />
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="fee_type_symb">%</span>
                                    </div>
                                </div>
                                @error('name')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror 
                            </div>

                        </div>
                        <div class="card-footer" style="justify-content: flex-end;">
                            <button type="submit" class="btn btn-success float-right"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@section('js')
<script>
function taxfeeType(value) {
    $('#percentTitle').text('Percent *');
    if(value === 'TAX') {
        $('#fee_type_symb').html('%');
        $('#taxfeeType').addClass('hidden');
        $('#fee_type').val('PERCENT');
    }
    else if(value === 'FEE') {
        $('#fee_type_symb').html('CAD');
        $('#taxfeeType').removeClass('hidden');
    }
}
function taxPercent(value) {
    if(value === 'PERCENT') {
        $('#percentTitle').text('Percent *');
        $('#fee_type_symb').html('%');
    }
    else {
        $('#percentTitle').text('Tax Amount *');
        $('#fee_type_symb').html('CAD');
    }
}

taxfeeType('{{ $taxfee->tax_fee_type }}');
taxPercent('{{ $taxfee->fee_type }}');
</script>
@endsection
</x-admin>
