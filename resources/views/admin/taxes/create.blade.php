<x-admin>
    @section('title','New Tax or Fee')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-primary">
                    <div class="card-header">
                        <h3 class="card-title">New Tax or Fee</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.taxes.index') }}" class="btn btn-info btn-sm">Back</a>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="needs-validation" novalidate action="{{ route('admin.taxes.store') }}" 
                    method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            <div class="form-group mb-3">
                                <label for="name">Label *</label>
                                <input type="text" class="form-control" id="label" name="label"
                                    placeholder="Enter label" required>
                                @error('name')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror 
                            </div>

                            <div class="form-group mb-3">
                                <label for="pickup_time">Tax or Fee *</label>
                                <select class="form-control aiz-selectpicker" id="tax_fee_type" name="tax_fee_type" onchange="taxfeeType(this.value)">
                                    <option value="TAX">Tax</option>                                    
                                    <option value="FEE">Fee</option>                                        
                                </select>
                                @error('tax_fee_type')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group hidden mb-3" id="taxfeeType">
                                <label for="pickup_time">Value *</label>
                                <select class="form-control aiz-selectpicker" name="fee_type" id="fee_type" onchange="taxPercent(this.value)">
                                    <option value="PERCENT">Percent</option>
                                    <option value="FIXED_PER_ORDER">Fixed per order item</option>
                                    <option value="FIXED_PER_QUANTITY">Fixed per quantity</option>
                                    <option value="FIXED_PER_DURATION">Fixed per duration</option>
                                </select>
                                @error('tax_fee_type')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="name" id="percentTitle">Percent *</label>
                                <div class="input-group ">                                    
                                    <input type="text" class="form-control" id="tax_fee_value" name="tax_fee_value"
                                        placeholder="Enter value" style="max-width: 200px;" required />
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="fee_type_symb">%</span>
                                    </div>
                                </div>
                                @error('name')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror 
                            </div>

                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary float-right">Save</button>
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
        $('#fee_type_symb').html('$');
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
        $('#fee_type_symb').html('$');
    }
}
</script>
@endsection
</x-admin>
