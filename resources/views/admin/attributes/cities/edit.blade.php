<x-admin>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-primary mb-3">
                <div class="card-header edit-city-head">
                    <div class="row">
                        <div class="col-md-8 col-6">
                            <h3 class="card-title">{{translate('Edit City Info')}}</h3>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="card-tools">
                                <a class="btn btn-sm btn-back" href="{{ route('admin.cities.index') }}">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-primary bg-white border rounded-lg-custom edit-city-body">
                <div class="card-body">
                    <form action="{{ route('admin.cities.update', $city->id) }}" method="POST" >
                        <input name="_method" type="hidden" value="PATCH">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">{{translate('Country')}}</label>
                            <select class="form-control aiz-selectpicker" id="country_id" data-live-search="true" name="country_id" required>
                                @foreach($countries as $country)
                                    <option value="{{$country->id}}">{{ ucwords($country->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="name">{{translate('State')}}</label>
                            <select class="form-control aiz-selectpicker" name="state_id"  data-live-search="true"  id="state_id"  required>

                            </select>
                            @error('state_id')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                         <div class="row d-flex justify-content-between align-items-center">
                            <div class="form-group mb-3 col-md-10">
                                <label for="name">{{translate('City Name')}}</label>
                                <input type="text" id="name" name="name" value="{{ ucwords($city->name) }}" class="form-control"
                                       required>
                               @error('name')
                                   <small class="form-text text-danger">{{ $message }}</small>
                               @enderror


                            </div>
                            <div class="col-md-2 mt-2">
                            <button type="button"
                                    id="fetch-latlong-btn"
                                    class="btn btn-outline-primary btn-sm w-100" data-toggle="tooltip"
                                    data-placement="top"
                                    title="Fetch latitude and longitude">
                               Lat/Lng Fetch
                            </button>

                            </div>
                        </div>
                        <div class="row d-flex justify-content-between">
                            <div class="form-group mb-3 col-md-6">
                                <label>{{ translate('Latitude') }}</label>
                                <input type="text" id="latitude" name="latitude" class="form-control"
                                       value="{{ $city->latitude ?? '' }}">
                            </div>

                            <div class="form-group mb-3 col-md-6">
                                <label>{{ translate('Longitude') }}</label>
                                <input type="text" id="longitude" name="longitude" class="form-control"
                                       value="{{ $city->longitude ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">{{translate('Image')}}</label>
                            <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{translate('Choose Photo')}}</div>
                                <input type="hidden" name="upload_id" class="selected-files" value="{{ $city->upload_id }}" >
                            </div>
                            <div class="file-preview box"></div>
                        </div>

                        <div class="form-group mb-3 text-right">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> {{translate('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



@section('js')
    <script type="text/javascript">
        function get_state_by_country(){
            var country_id = $('#country_id').val();
            $.post('{{ route('states.get_state_by_country') }}',{_token:'{{ csrf_token() }}', country_id:country_id}, function(data){
                $('#state_id').html(null);
                for (var i = 0; i < data.length; i++) {
                    $('#state_id').append($('<option>', {
                        value: data[i].id,
                        text: data[i].name
                    }));
                    TB.plugins.bootstrapSelect('refresh');
                }
            });

        }

        $(document).ready(function(){
            $("#country_id > option").each(function() {
                if(this.value == '{{$city->state->country_id}}'){
                    $("#country_id").val(this.value).change();
                }
            });
            get_state_by_country();
        });

        $('#country_id').on('change', function() {
            get_state_by_country();
        });

    </script>
    <script>
    function fetchLatLongFromGoogle() {
        
        let country = $('#country_id option:selected').text();
        let state   = $('#state_id option:selected').text();
        let city    = $('#name').val();

        if (!city || !state || !country) {
            return;
        }

        let address = `${city}, ${state}, ${country}`;

        let geocoder = new google.maps.Geocoder();

        geocoder.geocode({ address: address }, function (results, status) {
            if (status === 'OK') {
                let location = results[0].geometry.location;
                $('#latitude').val(location.lat());
                $('#longitude').val(location.lng());
            } else {
                console.warn('Geocoding failed:', status);
            }
        });
    }

    // Trigger when city name loses focus
    $('#name').on('blur', function () {
        fetchLatLongFromGoogle();
    });

    // Trigger when state changes
    $('#state_id').on('change', function () {
        fetchLatLongFromGoogle();
    });


    $('#fetch-latlong-btn').on('click', function () {
        fetchLatLongFromGoogle();
    });

</script>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

@endsection
</x-admin>