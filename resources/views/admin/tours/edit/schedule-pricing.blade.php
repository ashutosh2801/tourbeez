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

                    <div class="col-xl-12">
                        <div class="form-group">
                            <label>Product pricing</label>

                            @php
                                $priceOptions = $data->pricings->map(function ($item, $index) {

                                    $old = old("PriceOption.$index", []);

                                    return [
                                        'id'             => $item->id,
                                        'label'          => $item->label,
                                        'price'          => $item->price,
                                        'qty_used'       => $item->quantity_used,
                                        'selling_price'  => $old['selling_price'] ?? $item->selling_price,
                                        'extra_included' => $old['extra_included'] ?? $item->extra_included,
                                    ];

                                })->toArray();

                                $count = count($priceOptions);
                                if($count == 0){
                                    $priceOptions = [ ['id'=>'', 'label'=>'', 'price'=>'', 'qty_used'=>1] ];
                                }
                            @endphp
                            <div class="row font-weight-bold mb-2 border-bottom pb-2">
                                <div class="col-xl-2">Type</div>
                                <div class="col-xl-2">Label</div>
                                <div class="col-xl-2 text-left">Customer Price</div>
                                <div class="col-xl-2 text-left">Selling Price</div>
                                <div class="col-xl-2 text-left">Extra Included</div>
                                <div class="col-xl-2 text-left">Total</div>
                            </div>



                            @foreach ($priceOptions as $index => $option)

                               <div class="row mb-3 align-items-center">

                                <input type="hidden"
                                       name="PriceOption[{{ $index }}][id]"
                                       value="{{ $option['id'] }}">

                                {{-- Type --}}
                                <div class="col-xl-2">
                                    @if($index==0)
                                        <input class="form-control"
                                               value="{{ $data->price_type=='FIXED' ? 'FIXED' : 'By Person' }}"
                                               readonly>
                                    @endif
                                </div>

                                {{-- Label --}}
                                <div class="col-xl-2">
                                    <input class="form-control"
                                           value="{{ $option['label'] }}"
                                           readonly>
                                </div>

                                {{-- Customer Price --}}
                                <div class="col-xl-2">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input class="form-control"
                                               value="{{ $option['price'] }}"
                                               readonly>
                                    </div>
                                </div>

                                {{-- Selling Price --}}
                                <div class="col-xl-2">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>

                                        <input type="number"
                                               step="0.01"
                                               name="PriceOption[{{ $index }}][selling_price]"
                                               value="{{ old("PriceOption.$index.selling_price",$option['selling_price']) }}"
                                               class="form-control selling-price"
                                               required>
                                    </div>
                                </div>

                                {{-- Extra Included --}}
                                <div class="col-xl-2">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>

                                        <input type="number"
                                               step="0.01"
                                               name="PriceOption[{{ $index }}][extra_included]"
                                               value="{{ old("PriceOption.$index.extra_included",$option['extra_included'] ?? 0) }}"
                                               class="form-control extra-included">
                                    </div>
                                </div>

                                {{-- Total --}}
                                <div class="col-xl-2">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>

                                        <input type="text"
                                               class="form-control total-price"
                                               readonly>
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

function updateTotals() {

    $('.selling-price').each(function(){

        let row = $(this).closest('.row');

        let selling = parseFloat(row.find('.selling-price').val()) || 0;
        let extra   = parseFloat(row.find('.extra-included').val()) || 0;

        row.find('.total-price').val((selling + extra).toFixed(2));

    });

}

updateTotals();

$(document).on('input', '.selling-price, .extra-included', function () {
    updateTotals();
});
</script>

@endsection