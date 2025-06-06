<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Location</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-unstyled">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="needs-validation" novalidate action="{{ route('admin.tour.location_update', $data->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="card-body">
                <div class="row">                   
                    
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label for="country" class="form-label">Country *</label>
                            @php $countries = \App\Models\Country::where('status',1)->get(); @endphp
                            <select name="country" id="country_id" class="form-control aiz-selectpicker" data-live-search="true" >
                                <option value="">{{translate('Select One')}}</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}"
                                    {{ old('country')==$country->id || $data->location?->country_id==$country->id ? 'selected':'' }}>{{ strtoupper($country->name) }}</option>
                                @endforeach
                            </select>
                            @error('country')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="form-group">
                            <label for="state" class="form-label">State *</label>
                            <select name="state" id="state_id" class="form-control aiz-selectpicker" data-live-search="true" >

                            </select>
                            @error('state')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="form-group">
                            <label for="city" class="form-label">City *</label>
                            <select name="city" id="city_id" class="form-control aiz-selectpicker" data-live-search="true" >

                            </select>
                            @error('city')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="form-group">
                            <label for="destination" class="form-label">Tourism destination*</label>
                            <input type="text" name="destination" id="destination" value="{{ old('destination') ? : $data->location?->destination }}"
                                class="form-control" placeholder="Ex: Niagara Falls" autocomplete="off">
                                
                            @error('destination')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="address" class="form-label">Address *</label>
                            <input type="text" name="address" id="autocomplete" value="{{ old('address') ? old('address') : $data->location?->address }}"
                                class="form-control" placeholder="Ex: Niagara Falls State Park" autocomplete="off">
                                
                            @error('address')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div> 

                    <div class="col-lg-2">
                        <div class="form-group">
                            <label for="postal_code" class="form-label">Postal/ZIP code*</label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') ? old('postal_code') : $data->location?->postal_code }}"
                                class="form-control" placeholder="Ex: 14303" autocomplete="off">
                                
                            @error('postal_code')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div> 

                </div>
            </div>
            <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}" class="btn btn-primary">Next</a>
            </div>
            </form>
        </div>
    </div>
</div>

@section('js')
@parent
<script>
// Get Countries and States
function get_states_by_country() {
    @if(old('country'))
    var country_id = {{ old('country') }}
    @elseif( $data->location?->country_id )
    var country_id = {{ $data->location?->country_id }}
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

            if (this.value == '{{ old('state', $data->location?->state_id ?? '') }}' ) {
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
    @elseif( $data->location?->state_id )
    var state_id = {{ $data->location?->state_id }}
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
            if (this.value == '{{ old('city', $data->location?->city_id ?? '') }}' ) {
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
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script>
<script>
    function initAutocompleteById() {
        const input = document.getElementById('autocomplete');

        if (input) {
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode'],
                componentRestrictions: { country: "ca" }, // restrict to Canada
            });

            autocomplete.addListener("place_changed", function () {
                const place = autocomplete.getPlace();
                console.log("Selected address:", place.formatted_address);
            });
        } else {
            console.warn("Input with ID 'address-input' not found.");
        }
    }

    window.onload = initAutocompleteById;
</script>
@endsection
