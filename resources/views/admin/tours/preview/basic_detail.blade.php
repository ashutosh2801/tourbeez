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
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" name="title" id="title" value="{{ old('title') ? : $data->title }}"
                                class="form-control" >
                                
                            @error('title')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4">
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
                                    $priceOptions = [ ['id'=>'', 'label' => '', 'price' => '', 'qty_used' => 0] ];
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
                                        <option @if(old('price_type')=='PER_PERSON' || old('price_type')=='') echo 'selected'; @endif value="PER_PERSON">By Person</option>
                                        <option @if(old('price_type')=='FIXED') echo 'selected'; @endif value="FIXED">By Fixed</option>
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

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="title" class="form-label">Advertised price *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">$</span>
                                </div>
                                <input type="text" class="form-control" placeholder="99.50" name="advertised_price" id="advertised_price" value="{{ old('advertised_price') ?: $data->price }}" style="max-width: 200px;">
                            </div>
                                
                            @error('advertised_price')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="category" class="form-label">Quantity</label>
                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1">Min</span>
                                        </div>
                                        <input type="number" placeholder="Min" name="quantity_min" id="quantity_min" value="{{ old('quantity_min') ?? $data->detail?->quantity_min }}" class="form-control" >
                                    </div>                                                
                                </div>
                                <div class="col-lg-2">
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
                    </div>
                    
                    

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
                        <div class="form-group mb-5">
                            <label for="videos" class="form-label">Videos</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">https://www.youtube.com/watch?v=</span>
                                </div>
                                <input type="text" placeholder="" name="videos[]" id="videos" value="{{ old('videos[]') }}" class="form-control mr-2" >
                                <button type="button" class="btn btn-sm btn-success " onclick="addVideos()"><i class="fa fa-plus"></i></button>
                            </div>
                            <div id="videosContainer"></div>
                        </div>
                    </div>


                    <div class="col-lg-12">
                        <div class="form-group mb-4">
                            <label for="IsPurchasedAsAGift" class="form-label"><input type="checkbox" name="IsPurchasedAsAGift" id="IsPurchasedAsAGift" {{ old('IsPurchasedAsAGift') || $data->detail?->IsPurchasedAsAGift ? 'checked' : '' }} /> Can be purchased as a gift</label>
                            <div class="row {{ old('IsPurchasedAsAGift') || $data->detail?->IsPurchasedAsAGift ?? 'hidden' }}" id="IsPurchasedAsAGift_show">
                                <div class="col-lg-4">
                                    <label style="font-weight:400"><input type="checkbox" {{ old('IsExpiryDays') || $data->detail?->IsExpiryDays ? 'checked' : '' }} name="IsExpiryDays" id="IsExpiryDays" value="1" /> Gift Card expires a number of days after</lable><br />
                                    <div class="input-group {{ old('expiry_days') || $data->detail?->expiry_days ?: 'hidden' }}" id="expiry_days_wra">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">expires</span>
                                        </div>
                                        <input type="text" placeholder="0" name="expiry_days" id="expiry_days" value="{{ old('expiry_days') }}" class="form-control" >
                                        <div class="input-group-append">
                                            <span class="input-group-text">days after purchase</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label style="font-weight:400"><input type="checkbox" {{ old('IsExpiryDate') || $data->detail?->IsExpiryDate ? 'checked' : '' }} name="IsExpiryDate" id="IsExpiryDate" value="1" /> Gift Card expires on a specific date</label><br />

                                    <div class="input-group {{ old('expiry_date') || $data->detail?->expiry_date ?: 'hidden' }}" id="expiry_date_wra">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">expires on</span>
                                        </div>
                                        <input type="text" class="aiz-date-range form-control" id="expiry_date" name="expiry_date" placeholder="Select Date" data-single="true" data-show-dropdown="true" value="{{ old('expiry_date') ?? $data->detail?->expiry_date }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label style="font-weight:400"><input type="checkbox" name="gift_tax_fees" id="gift_tax_fees" {{ old('gift_tax_fees') || $data->detail?->gift_tax_fees ? 'checked' : '' }} value="1" /> Gift is inclusive of all taxes & fees</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="IsTermsAndConditions" class="form-label"><input type="checkbox" name="IsTerms" id="IsTermsAndConditions" {{ old('IsTerms') || $data->detail?->IsTerms ? 'checked' : '' }} value="1" /> Add product-specific terms and conditions</label>
                            <div id="terms_and_conditions_wra" {{ old('terms_and_conditions') || $data->detail?->terms_and_conditions ?  'class="hidden"' :'' }} style=" overflow: hidden;">
                                <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control  aiz-text-editor" >{{ old('terms_and_conditions')  ?? $data->detail?->terms_and_conditions }}</textarea>
                            </div>
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