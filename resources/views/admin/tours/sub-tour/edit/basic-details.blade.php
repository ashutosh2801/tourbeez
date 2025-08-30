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

<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Basic Details</h3>            
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
        <form class="needs-validation" novalidate action="{{ route('admin.tour.basic_detail_update', $data->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="form-group">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" name="title" id="title" value="{{ old('title') ? : $data->title }}"
                                class="form-control" >
                                
                            @error('title')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="unique_code" class="form-label">Unique code *</label>
                            <input type="text" name="unique_code" id="unique_code" value="{{ old('unique_code') ? old('unique_code') : $data->unique_code }}"
                                class="form-control" >
                                
                            @error('unique_code')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        
                    </div>



                    <div class="col-lg-12">
                        <div class="form-group" id="product_pricing">
                            <label for="category" class="form-label">Product pricing *</label>
                            
                            @php
                                //$priceOptions = old('PriceOption', [ ['label' => '', 'price' => '', 'qty_used' => 0] ]);
                                $priceOptions = old('PriceOption', $data->pricings->map(function ($item) {
                                                    return [
                                                        'id'       => $item->id,
                                                        'label'    => $item->label,
                                                        'price'    => $item->price,
                                                        'qty_used' => $item->quantity_used,
                                                    ];
                                                })->toArray());
                                $count = count($priceOptions);
                                if($count == 0){
                                    $priceOptions = [ ['id'=>'', 'label' => '', 'price' => '', 'qty_used' => 1] ];
                                    $count = 1;
                                }
                            @endphp
                            @foreach ($priceOptions as $index => $option)   
                            
                            @if ($index > 0) <div class="priceOptionsWra"> @endif
                            
                            <div class="row mb-3" id="priceOptionRow_{{ $index }}">

                                <input type="hidden" name="PriceOption[{{ $index }}][id]" id="PriceOption_id" 
                            value="{{ old("PriceOption.$index.id", $option['id']) }}" class="form-control" />

                                @if($index == 0)
                                <div class="col-lg-2">
                                    <select name="price_type" id="pricing" class="form-control">
                                        <option @if(old('price_type')=='PER_PERSON' || $data->price_type=="PER_PERSON" || old('price_type')=='') selected @endif value="PER_PERSON">By Person</option>
                                        <option @if(old('price_type')=='FIXED' || $data->price_type=="FIXED") selected @endif value="FIXED">By Fixed</option>
                                    </select>                                                
                                </div>
                                @else
                                <div class="col-lg-2"></div>
                                @endif

                                <div class="col-lg-2">
                                    <input type="text" placeholder="Adults" name="PriceOption[{{ $index }}][label]" id="PriceOption_name" 
                                    value="{{ old("PriceOption.$index.label", $option['label']) }}" class="form-control" >
                                    @error("PriceOption.$index.label")
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-2">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1">$</span>
                                        </div>
                                        <input type="text" placeholder="99.50" name="PriceOption[{{ $index }}][price]" id="PriceOption_price" 
                                        value="{{ old("PriceOption.$index.price", $option['price']) }}" class="form-control" >
                                        
                                    </div>  
                                    @error("PriceOption.$index.price")
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror                                              
                                </div>
                                <div class="col-lg-5 ">
                                    <div class="input-group quantity_used @if(old('price_type')=='FIXED' || $data->price_type=="FIXED") hidden @endif">
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

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="title" class="form-label">Advertised price *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">$</span>
                                </div>
                                <input type="text" class="form-control" placeholder="99.50" name="advertised_price" id="advertised_price" value="{{ old('advertised_price') ?: $data->price }}">
                            </div>
                                
                            @error('advertised_price')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="category" class="form-label">Quantity</label>
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1">Min</span>
                                        </div>
                                        <input type="number" placeholder="Min" name="quantity_min" id="quantity_min" value="{{ old('quantity_min') ?? $data->detail?->quantity_min }}" class="form-control" >
                                    </div>                                                
                                </div>
                                <div class="col-lg-4">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1">Max</span>
                                        </div>
                                        <input type="number" placeholder="Max" name="quantity_max" id="quantity_max" value="{{ old('quantity_max') ?? $data->detail?->quantity_max }}" class="form-control" >
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="coupon_type" class="form-label">Discount Type & Value</label>
                            <div class="row">
                                <!-- Coupon Type -->
                                <div class="col-lg-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1">Type</span>
                                        </div>
                                        <select name="coupon_type" id="coupon_type" class="form-control">
                                            <option value="">{{ translate('No Coupon') }}</option>
                                            <option value="percentage" {{ $data?->coupon_type == 'percentage' ? 'selected' : ''}}>{{ translate('Percentage') }}</option>
                                            <option value="fixed" {{ $data?->coupon_type == 'fixed' ? 'selected' : ''}}>{{ translate('Fixed Amount') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Coupon Value -->
                                <div class="col-lg-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1">Value</span>
                                        </div>
                                        <input type="number" 
                                               placeholder="Value" 
                                               name="coupon_value" 
                                               id="coupon_value" 
                                               value="{{ old('coupon_value') ?? $data?->coupon_value }}" 
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                    </div>
                                                    
                    <!-- <div class="col-lg-6">
                        <div class="form-group">
                            <label for="category" class="form-label">Category *</label>
                            <select name="category[]" id="category" class="form-control aiz-selectpicker"  data-live-search="true" multiple>
                                @foreach ($category as $cat)
                                    <option value="{{ $cat->id }}"
                                    {{ (collect(old('category', $data->categories?->pluck('id')->toArray()))->contains($cat->id)) ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                                    {{ (collect(old('tour_type', $data->tourtypes?->pluck('id')->toArray()))->contains($tt->id)) ? 'selected' : '' }}>{{ $tt->name }}</option>
                                @endforeach                                            
                            </select>
                            @error('tour_type')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div> -->
                    
                    <?php /*
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="country" class="form-label">Country *</label>
                            @php $countries = \App\Models\Country::where('status',1)->get(); @endphp
                            <select name="country" id="country_id" class="form-control aiz-selectpicker" data-live-search="true" >
                                <option value="">{{translate('Select One')}}</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}"
                                    {{ old('country')==$country->id || $data->country==$country->id ? 'selected':'' }}>{{ strtoupper($country->name) }}</option>
                                @endforeach
                            </select>
                            @error('country')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="state" class="form-label">State *</label>
                            <select name="state" id="state_id" class="form-control aiz-selectpicker" data-live-search="true" >

                            </select>
                            @error('state')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="city" class="form-label">City  * {{ $data->city }}</label>
                            <select name="city" id="city_id" class="form-control aiz-selectpicker" data-live-search="true" >

                            </select>
                            @error('city')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    */ ?>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="description" class="form-label">Brief description *</label>
                            <textarea name="description" id="description" rows="3" class="form-control aiz-text-editor">{{ old('description') ?: $data->detail?->description }}</textarea>
                            <small class="form-text text-right">{{ ('Max 240 characters') }}</small>
                            @error('description')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="long_description" class="form-label">Long description *</label>
                            <textarea name="long_description" id="long_description" class="form-control aiz-text-editor" >{{ old('long_description') ?: $data->detail?->long_description }}</textarea>
                                @error('long_description')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="other_description" class="form-label">Other description</label>
                            <textarea name="other_description" id="other_description" class="form-control aiz-text-editor" >{{ old('other_description') ?: $data->detail?->other_description }}</textarea>
                                @error('other_description')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
 

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{translate('Featured Image')}}</label>
                            <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{translate('Choose Photo')}}</div>
                                <input type="hidden" name="image" class="selected-files" value="{{ $data->main_image?->id }}">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    
                   
                    
                </div>
            </div>            

            <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.index') }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" class="btn btn-primary">Next</a>
            </div>
        </form>
    </div>
</div>

@section('js')
@parent
<script>
// Get Countries and States
function get_states_by_country() {
    @if(old('country'))
    var country_id = {{ old('country') }}
    @elseif( $data->country )
    var country_id = {{ $data->country }}
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

            if (this.value == '{{ old('state', $data->state ?? '') }}' ) {
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
    @elseif( $data->state )
    var state_id = {{ $data->state }}
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
            if (this.value == '{{ old('city', $data->city ?? '') }}' ) {
                $("#city_id").val(this.value).change();
            }
        });
        TB.plugins.bootstrapSelect('refresh');
    });
}

get_states_by_country();
get_cities_by_state();

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


let priceOptionCount = {{ $count ? $count : 1 }}
function generateQuantityOptions() {
    let options = '';
    for (let i = 0; i <= 54; i++) {
        options += `<option value="${i}">${i}</option>`;
    }
    return options;
}

function addPriceOption() {
    const container = document.getElementById('priceOptionsContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('row', 'align-items-end', 'mb-2');
    newRow.setAttribute('id', `priceOptionRow_${priceOptionCount}`);

    newRow.innerHTML = `
        <div class="col-lg-2"></div>
        <div class="col-lg-2">
            <input type="text" placeholder="Label" name="PriceOption[${priceOptionCount}][label]" id="PriceOption_${priceOptionCount}_label" class="form-control">
        </div>
        <div class="col-lg-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
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
@endsection