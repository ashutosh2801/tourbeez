<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
</style>

<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Price Schedule</h3>            
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

        <form action="{{ route('admin.tour.shedule-pricing', $data->id) }}" method="POST">
            @csrf

            <div class="card-body">
                <div class="row">

                    {{-- TITLE --}}
                    <div class="col-xl-7">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" value="{{ $data->title }}" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="col-xl-7">
                        <div class="form-group">
                            <label>Report Group</label>
                            <div>
                                <select class="form-control" name="report_group">
                                    @foreach(report_group_tours() as $groupId => $groupName)
                                        <option value="{{ $groupId }}" {{ $data->report_group == $groupId ? 'selected' : '' }}>
                                            {{ $groupName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- PRODUCT PRICING --}}
                    <div class="col-xl-7">
                        <div class="form-group">
                            <label>Transport Cost</label>
                            <div class="input-group">
                                        <span class="input-group-text">$</span>
                            <input  class="form-control" type=""
                                       name="transport_cost"
                                       value="{{ $data->transport_cost }}">
                                   </div>
                        </div>
                    </div>

                    <div class="col-xl-12">
                        <div class="form-group">
                            <label>Product pricing</label>

                            @php
                                $priceOptions = old('PriceOption', $data->pricings->map(function ($item) {
                                    return [
                                        'id'       => $item->id,
                                        'label'    => $item->label,
                                        'price'    => $item->price,
                                        'qty_used' => $item->quantity_used,
                                        'selling_price' => $item->selling_price,
                                    ];
                                })->toArray());

                                $count = count($priceOptions);
                                if($count == 0){
                                    $priceOptions = [ ['id'=>'', 'label'=>'', 'price'=>'', 'qty_used'=>1] ];
                                }
                            @endphp

                            @foreach ($priceOptions as $index => $option)

                            <div class="row mb-3">

                                {{-- ID --}}
                                <input type="hidden"
                                       name="PriceOption[{{ $index }}][id]"
                                       value="{{ $option['id'] }}">

                                {{-- PRICE TYPE (unchanged) --}}
                                @if($index == 0)
                                <div class="col-xl-2">
                                    <select class="form-control" disabled>
                                        <option selected>{{$data->price_type=="FIXED" ? "FIXED" : "By Person"}}</option>
                                    </select>
                                </div>
                                @else
                                <div class="col-xl-2"></div>
                                @endif

                                {{-- LABEL (readonly) --}}
                                <div class="col-xl-2">
                                    <input type="text"
                                           value="{{ $option['label'] }}"
                                           class="form-control"
                                           readonly>
                                </div>

                                {{-- ORIGINAL PRICE (readonly) --}}
                                <div class="col-xl-2">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text"
                                               value="{{ $option['price'] }}"
                                               class="form-control"
                                               readonly>
                                    </div>
                                </div>

                                

                                {{-- SELLING PRICE (ONLY EDITABLE) --}}
                                <div class="col-xl-3">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text"
                                               name="PriceOption[{{ $index }}][selling_price]"
                                               value="{{ old("PriceOption.$index.selling_price", $option['selling_price']) }}"
                                               class="form-control"
                                               required>
                                    </div>
                                </div>

                            </div>

                            @endforeach

                        </div>
                    </div>

                </div>
            </div>

            {{-- FOOTER --}}
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Save</button>

                <!-- <a href="{{ route('admin.tour.index') }}" class="btn btn-secondary">Back</a>
                <a href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" class="btn btn-secondary">Next</a> -->
            </div>

        </form>
    </div>
</div>

@section('js')
@parent

<script>
    window.currencySymbols = @json(config('constants.currency_symbols'));
</script>

{{-- ONLY KEEP THIS (currency logic) --}}
<script>
function updateCurrencySymbol() {
    let currency = $('select[name="currency"]').val();
    let symbol = currencySymbols[currency] ?? currency;
    $('.currency-symbol').text(symbol);
}

updateCurrencySymbol();

$('select[name="currency"]').on('change', function () {
    updateCurrencySymbol();
});
</script>

@endsection