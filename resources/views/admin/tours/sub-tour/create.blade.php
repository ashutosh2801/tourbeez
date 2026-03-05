<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 26px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #28a745; /* Bootstrap green */
    }

    input:checked + .slider:before {
        transform: translateX(24px);
    }
</style>


<x-admin>
    @section('title')
        {{ 'Tour' }}
    @endsection
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card-primary mb-3">
                <div class="card-header sub-tours">
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h5 class="card-title">Create Sub Tour :- {{ $data->title }}</h5>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="card-tools">
                                <a href="{{ route('admin.tour.index') }}" class="btn btn-sm btn-back">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-primary bg-white border rounded-lg-custom create-sub-tours">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="needs-validation my-0" novalidate action="{{ route('admin.tour.sub-tour-store', [$data->id]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-7">
                                <div class="form-group">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                                        class="form-control" >
                                        
                                    @error('title')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="form-group">
                                    <label for="unique_code" class="form-label">Unique code *</label>
                                    <input type="text" name="unique_code" id="unique_code" value="{{ old('unique_code') ? old('unique_code') : unique_code() }}"
                                        class="form-control" >
                                        
                                    @error('unique_code')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <!-- <div class="col-xl-5"> -->
                                    <div class="form-group">
                                        <label for="slug" class="form-label">Currency *</label>
                                        <select name="currency" class="form-control mr-2" readonly>
                                            @foreach(config('constants.currencies') as $code => $country)
                                                <option value="{{ $code }}" {{ $code == $data->currency ? 'selected' : '' }}>{{ $code }} - {{ $country }}</option> 
                                            @endforeach

                                        </select>
                                    </div>
                                <!-- </div> -->
                            </div>
                            
                            <div class="col-xl-12">
                                <div class="form-group" id="product_pricing">
                                    <label for="category" class="form-label">Product pricing *</label>
                                    
                                    @php
                                        $priceOptions = old('PriceOption', [ ['label' => '', 'price' => '', 'qty_used' => 1] ]);
                                        $count = count($priceOptions);
                                        $parentPriceLabels = $parentTour->pricings->pluck('label')->unique()->values();
                                    @endphp
                                    @foreach ($priceOptions as $index => $option)   
                                    
                                    @if ($index > 0) <div class="priceOptionsWra"> @endif
                                    
                                    <div class="row mb-3" id="priceOptionRow_{{ $index }}">
                                        @if($index == 0)
                                        <div class="col-lg-2">
                                            <select name="price_type" id="pricing" class="form-control">
                                                <option @if(old('price_type')=='PER_PERSON' || old('price_type')=='') echo 'selected'; @endif value="PER_PERSON">By Person</option>
                                                <option @if(old('price_type')=='FIXED') echo 'selected'; @endif value="FIXED">By Fixed</option>
                                            </select>                                                
                                        </div>
                                        @else
                                        <div class="col-lg-2"></div>
                                        @endif
                                        <div class="col-lg-2">
                                            <select name="PriceOption[{{ $index }}][label]"
                                                    class="form-control price-label-select">
                                                <option value="">Select label</option>

                                                @foreach ($parentPriceLabels as $label)
                                                    <option value="{{ $label }}"
                                                        {{ old("PriceOption.$index.label") == $label ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text currency-symbol" id="basic-addon1">$</span>
                                                </div>
                                                <input type="text" placeholder="99.50" name="PriceOption[{{ $index }}][price]" id="PriceOption_price" 
                                                value="{{ old("PriceOption.$index.price", $option['price']) }}" class="form-control price-option-input" >
                                                
                                            </div>  
                                            @error("PriceOption.$index.price")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror                                              
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="input-group quantity_used">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">Quantity used</span>
                                                </div>
                                                <select name="PriceOption[{{ $index }}][qty_used]" id="PriceOption_qty_used" class="form-control mr-2" style="max-width:120px;">
                                                    @for ($i = 0; $i < 55; $i++)
                                                        <option value="{{ $i }}" {{ old("PriceOption.$index.qty_used", $option['qty_used']) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                                <button type="button" class="btn btn-sm btn-success mr-2" onclick="addPriceOption()"><i class="fa fa-plus"></i></button>
                                                @if($index > 0)
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removePriceOption({{ $index }})"><i class="fa fa-minus"></i></button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if ($index > 0) </div> @endif

                                    @endforeach

                                    @if ($count > 0)
                                        <div id="priceOptionsContainer"></div>
                                    @endif

                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="form-group">
                                    <label for="title" class="form-label">Advertised price *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1 currency-symbol">$</span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="99.50" name="advertised_price" id="advertised_price" value="{{ old('advertised_price') }}" style="max-width: 200px;">
                                    </div>
                                        
                                    @error('advertised_price')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="form-group">
                                    <label for="category" class="form-label">Quantity</label>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">Min</span>
                                                </div>
                                                <input type="number" placeholder="Min" name="quantity_min" id="quantity_min" value="{{ old('quantity_min') }}" class="form-control" >
                                            </div>                                                
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">Max</span>
                                                </div>
                                                <input type="number" placeholder="Max" name="quantity_max" id="quantity_max" value="{{ old('quantity_max') }}" class="form-control" >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                                            
                            <!-- <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">Category *</label>
                                    <select name="category[]" id="category" class="form-control aiz-selectpicker"  data-live-search="true" multiple>
                                        @foreach ($category as $cat)
                                            <option value="{{ $cat->id }}"
                                            {{ (collect(old('category'))->contains($cat->id)) ? 'selected':'' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="tour_type" class="form-label">Tour Types *</label>
                                    <select name="tour_type[]" id="tour_type" class="form-control aiz-selectpicker"  data-live-search="true" multiple>
                                        @foreach ($tour_type as $tt)
                                            <option value="{{ $tt->id }}"
                                            {{ (collect(old('tour_type'))->contains($tt->id)) ? 'selected':'' }}>{{ $tt->name }}</option>
                                        @endforeach                                            
                                    </select>
                                    @error('tour_type')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div> -->
                            

                            <div class="col-xl-12">
                                <div class="form-group">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea name="description" id="description" rows="3" class="form-control aiz-text-editor">{{ old('description') }}</textarea>
                                    <small class="form-text text-right">{{ ('Max 240 characters') }}</small>
                                    @error('description')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="long_description" class="form-label">Long description *</label>
                                    <textarea name="long_description" id="long_description" class="form-control aiz-text-editor" >{{ old('long_description') }}</textarea>
                                        @error('long_description')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="other_description" class="form-label">Other description</label>
                                    <textarea name="other_description" id="other_description" class="form-control aiz-text-editor" >{{ old('other_description') }}</textarea>
                                        @error('other_description')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div> -->


                            <div class="col-xl-6">
                                <div class="form-group">
                                    <label class="form-label">{{translate('Featured Image')}}</label>
                                    <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                                        </div>
                                        <div class="form-control file-amount">{{translate('Choose Photo')}}</div>
                                        <input type="hidden" name="image" class="selected-files" >
                                    </div>
                                    <div class="file-preview box"></div>
                                </div>
                            </div>
                            <!-- <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" name="image" id="image" class="form-control" accept="image/*"
                                        >
                                        @error('image')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div> -->
                            <!-- <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="slider-images" class="form-label">Tour Slider Images</label>
                                    <input type="file" name="slider_images[]" id="slider-images" accept="image/*"
                                        class="form-control" multiple>
                                        @error('slider_images')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div> -->
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" id="submit" class="btn btn-success"> <i class="fas fa-save"></i> Save tour</button>
                            </div>
                            <div class="col-md-6 align-buttons">
                                <a href="{{ route('admin.tour.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@section('js')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.currencySymbols = @json(config('constants.currency_symbols'));
</script>
<script>
    const MAX_PRICE_OPTIONS = {{ $parentTour->pricings->pluck('label')->unique()->count() }};
</script>
<script>
function getCurrentPriceOptionCount() {
    return document.querySelectorAll('.price-label-select').length;
}
</script>
<script>
// Get Countries and States
function get_states_by_country() {
    @if(old('country'))
    var country_id = {{ old('country') }}
    @else
    var country_id = $('#country_id').val();
    @endif

    $.post('{{ route('states.get_state_by_country') }}', {
        _token: '{{ csrf_token() }}',
        country_id: country_id
    }, function(data) {
        $('#state_id').html(null);
        $('#state_id').append($('<option>', {
            value: '',
            text: 'Choose One'
        }));
        for (var i = 0; i < data.length; i++) {
            $('#state_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name.toUpperCase()
            }));
        }
        $("#state_id > option").each(function() {

            if (this.value == '{{ old('state') }}' ) {
                $("#state_id").val(this.value).change();
            }
        });

        TB.plugins.bootstrapSelect('refresh');

        get_cities_by_state();
    });
}

function get_cities_by_state() {

    @if(old('state'))
    var state_id = {{ old('state') }}
    @else
    var state_id = $('#state_id').val();
    @endif

    $.post('{{ route('cities.get_cities_by_state') }}', {
        _token: '{{ csrf_token() }}',
        state_id: state_id
    }, function(data) {
        $('#city_id').html(null);
        $('#city_id').append($('<option>', {
            value: '',
            text: 'Choose One'
        }));
        for (var i = 0; i < data.length; i++) {
            $('#city_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name.toUpperCase()
            }));
        }
        $("#city_id > option").each(function() {
            if (this.value == '{{ trim(old('city')) }}') {
                $("#city_id").val(this.value).change();
            }
        });
        TB.plugins.bootstrapSelect('refresh');
    });
}

@if(old('country'))
get_states_by_country();
@endif
@if(old('state'))
get_cities_by_state();
@endif

$('#country_id').on('change', function() {
    get_states_by_country();
});

$('#state_id').on('change', function() {
    get_cities_by_state();
});

$('#pricing').on('change', function() {
    if ($(this).val() == 'PER_PERSON') {
        $('.quantity_used').removeClass('hidden');
    } else {
        $('.quantity_used').addClass('hidden');
        $('.priceOptionsWra').html('');
        priceOptionCount = 1;
    }
});

$('#IsTermsAndConditions').on('click', function() {
    if ($(this).is(':checked')) {
        $('#terms_and_conditions_wra').removeClass('hidden');
    } else {
        $('#terms_and_conditions_wra').addClass('hidden');
    }
});
$('#IsPurchasedAsAGift').on('click', function() {
    if ($(this).is(':checked')) {
        $('#IsPurchasedAsAGift_show').removeClass('hidden');
    } else {
        $('#IsPurchasedAsAGift_show').addClass('hidden');
    }
});
$('#IsExpiryDays').on('click', function() {
    if ($(this).is(':checked')) {
        $('#expiry_days_wra').removeClass('hidden');
    } else {
        $('#expiry_days_wra').addClass('hidden');
    }
});
$('#IsExpiryDate').on('click', function() {
    if ($(this).is(':checked')) {
        $('#expiry_date_wra').removeClass('hidden');
    } else {
        $('#expiry_date_wra').addClass('hidden');
    }
});


let priceOptionCount = {{ old('PriceOption') ? count(old('PriceOption')) : 1 }}
function generateQuantityOptions() {
    let options = '';
    for (let i = 0; i <= 54; i++) {
        options += `<option value="${i}">${i}</option>`;
    }
    return options;
}

function addPriceOption() {
    if (getCurrentPriceOptionCount() >= MAX_PRICE_OPTIONS) {
        Swal.fire({
            icon: 'warning',
            title: 'Pricing limit reached',
            text: 'Please create a new pricing list in the parent tour.',
            confirmButtonColor: '#28a745',
        });
        return;
    }
    const container = document.getElementById('priceOptionsContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('row', 'align-items-end', 'mb-2');
    newRow.setAttribute('id', `priceOptionRow_${priceOptionCount}`);

    newRow.innerHTML = `
        <div class="col-lg-2"></div>
        <div class="col-lg-2">
            <select name="PriceOption[${priceOptionCount}][label]"
                    class="form-control price-label-select" id="PriceOption_${priceOptionCount}_label">
                <option value="">Select label</option>
                @foreach ($parentPriceLabels as $label)
                    <option value="{{ $label }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text currency-symbol">$</span>
                </div>
                <input type="text" placeholder="Price" name="PriceOption[${priceOptionCount}][price]" id="PriceOption_${priceOptionCount}_price" class="form-control">
            </div>
        </div>
        <div class="col-lg-5">
            <div class="input-group quantity_used">
                <div class="input-group-prepend">
                    <span class="input-group-text">Quantity used</span>
                </div>
                <select name="PriceOption[${priceOptionCount}][qty_used]" id="PriceOption_${priceOptionCount}_quantity_used" class="form-control mr-2" style="max-width:120px;">
                    @for ($index = 0; $index < 55; $index++)
                        <option value="{{ $index }}">{{ $index }}</option>
                    @endfor
                </select>
                <button type="button" class="btn btn-sm btn-success mr-2" onclick="addPriceOption()"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn btn-sm btn-danger" onclick="removePriceOption(${priceOptionCount})"><i class="fa fa-minus"></i></button>
            </div>
        </div>`;

    container.appendChild(newRow);
    priceOptionCount++;
    syncLabelOptions();
    updateCurrencySymbol();
}

function removePriceOption(id) {
    const row = document.getElementById(`priceOptionRow_${id}`);
    if (row) {
        row.remove();
        priceOptionCount--;
    }
}

let videosCount = 0;
function addVideos() {
    const container = document.getElementById('videosContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('row', 'align-items-end', 'mb-2');
    newRow.setAttribute('id', `videosRow_${videosCount}`);

    newRow.innerHTML = `
        <div class="col-lg-12">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">https://www.youtube.com/watch?v=</span>
                </div>
                <input type="text" placeholder="" name="videos[]" id="videos_${videosCount}" value="{{ old('videos[]') }}" class="form-control mr-2" >
                <button type="button" class="btn btn-sm btn-success mr-2" onclick="addVideos()"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeVideos(${videosCount})"><i class="fa fa-minus"></i></button>
            </div>
        </div>`;

    container.appendChild(newRow);
    videosCount++;
}

function removeVideos(id) {
    const row = document.getElementById(`videosRow_${id}`);
    if (row) {
        row.remove();
    }
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const advertisedPriceInput = document.getElementById('advertised_price');
    const firstPriceOption = document.querySelector('.price-option-input'); // 👈 FIRST ONLY

    if (firstPriceOption) {
        firstPriceOption.addEventListener('input', function () {
            const value = this.value.trim();

            if (value !== '') {
                advertisedPriceInput.value = value;
            }
        });
    }

});
</script>

<script>
function getSelectedLabels() {
    const set = new Set();

    document.querySelectorAll('.price-label-select').forEach(select => {
        if (select.value) {
            set.add(select.value);
        }
    });

    return set;
}

function syncLabelOptions() {
    const selected = getSelectedLabels();

    document.querySelectorAll('.price-label-select').forEach(select => {
        const current = select.value;

        Array.from(select.options).forEach(option => {
            if (!option.value) return;

            option.disabled =
                option.value !== current &&
                selected.has(option.value);
        });
    });
}

// 🔒 lock BEFORE user clicks
document.addEventListener('focusin', function (e) {
    if (e.target.classList.contains('price-label-select')) {
        syncLabelOptions();
    }
});

// 🔄 lock AFTER change
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('price-label-select')) {
        syncLabelOptions();
    }
});

// initial load
document.addEventListener('DOMContentLoaded', syncLabelOptions);
</script>

<script>
function updateCurrencySymbol() {
    let currency = $('select[name="currency"]').val();
    let symbol = currencySymbols[currency] ?? currency;

    $('.currency-symbol').text(symbol);
}

// On page load
updateCurrencySymbol();

// On currency change
$('select[name="currency"]').on('change', function () {
    
    updateCurrencySymbol();
});
</script>

@endsection
</x-admin>
