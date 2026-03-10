<x-admin>
@section('title','Cities')

<div class="row">
    <div class="col-lg-12">
        <div class="card-primary mb-3">
            <div class="card-header city-head">
                <div class="row">
                    <div class="col-md-8 col-6">
                        <h3 class="card-title">{{ translate('All Cities') }}</h3>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addModal">
                                + Add New
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-primary bg-white border rounded-lg-custom city-body">
            <div class="search-section">
                <form id="sort_cities" action="" method="GET">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <input type="text" class="form-control" name="search" value="{{ $sort_search ?? '' }}" placeholder="{{ translate('Search city') }}">
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="has_image" class="form-control">
                                <option value="">{{ translate('Image') }}</option>
                                <option value="1" {{ request('has_image') == '1' ? 'selected' : '' }}>
                                    {{ translate('Has Image') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <select name="has_tour" class="form-control">
                                <option value="">{{ translate('Tour') }}</option>
                                <option value="1" {{ request('has_tour') == '1' ? 'selected' : '' }}>
                                    {{ translate('Has Tour') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <select name="has_latlong" class="form-control">
                                <option value="">{{ translate('Lat/Long') }}</option>
                                <option value="1" {{ request('has_latlong') == '1' ? 'selected' : '' }}>
                                    {{ translate('Has Lat/Long') }}
                                </option>
                            </select>
                            <select name="has_image" class="form-control col-2 ml-1">
                                <option value="">{{ translate('Image') }}</option>
                                <option value="1" {{ request('has_image') == '1' ? 'selected' : '' }}>
                                    {{ translate('Has Image') }}
                                </option>
                            </select>

                            <select name="per_page" class="form-control">
                                @foreach (['All',10, 25, 50, 100] as $number)
                                    <option value="{{ $number }}" {{ request('per_page', 10) == $number ? 'selected' : '' }}>
                                        {{ $number }} per page
                                    </option>
                                @endforeach
                            </select>
                        

                            <div class="input-group-append">
                                <button class="btn btn-primary ml-1" type="submit">
                                    {{ translate('Search') }}
                                </button>
                            </div>



                        </div>
                        <div class="col-md-2 col-12">
                            <button class="btn btn-search" type="submit">
                               <i class="fas fa-search"></i> {{ translate('Search') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <form action="{{ route('admin.cities.updateOrder') }}" method="POST">
                    @csrf
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order</th>
                            <th>Image</th>
                            <th>{{translate('City')}}</th>
                            <th data-breakpoints="md">{{translate('State')}}</th>
                            <th data-breakpoints="md">{{translate('Country')}}</th>
                            <th class="text-right" width="20%">{{translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cities as $key => $city)
                            <tr>
                                <td>{{ ($key+1) + ($cities->currentPage() - 1)*$cities->perPage() }}</td>
                                <td width="10">
                                    <input type="number"
                                           name="orders[{{ $city->id }}]"
                                           value="{{ $city->order }}"
                                           class="form-control form-control-sm"
                                           min="0">
                                </td>

                                <td ><img class="img-md" src="{{ uploaded_asset($city->upload_id) }}" height="45px" alt="{{translate('photo')}}" /></td>
                                <td>{{ucwords($city->name)}}</td>
                                <td>{{ucwords($city->state->name)}}</td>
                                <td>{{ucwords($city->state->country->name)}}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.cities.edit', encrypt($city->id)) }}" class="btn btn-circle btn-sm text-black text-lg" title="{{ translate('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" data-href="{{route('admin.cities.destroy', $city->id)}}" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-right p-3">
                    <button type="submit" class="btn btn-primary">
                        Save City Order
                    </button>
                </div>
                </form>
                <div class="aiz-pagination">
                    {{ $cities->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade add-modal" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

        <div class="card-primary">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8 col-9">
                        <h6 class="m-0">{{translate('Add New City')}}</h6>
                    </div>
                    <div class="col-md-4 col-3">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                
            </div>
            <div class="card-body">
                <form action="{{ route('admin.cities.store') }}" method="POST" >
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
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="name">{{translate('City Name')}}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" id="name" name="name" placeholder="{{ translate('City Name') }}" class="form-control" required>
                                @error('name')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="fetch-latlong-btn" class="btn btn-fetch" data-toggle="tooltip" data-placement="top" title="Fetch latitude and longitude">Lat/Lng Fetch</button>
                            </div>
                        </div>
                    </div>
                    <div class="row d-flex justify-content-between">
                        <div class="form-group mb-3 col-md-6">
                            <label>{{ translate('Latitude') }}</label>
                            <input type="text" id="latitude" name="latitude" class="form-control"
                                   value="">
                        </div>

                        <div class="form-group mb-3 col-md-6">
                            <label>{{ translate('Longitude') }}</label>
                            <input type="text" id="longitude" name="longitude" class="form-control"
                                   value="">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">{{translate('Image')}}</label>
                        <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{translate('Choose Photo')}}</div>
                            <input type="hidden" name="upload_id" class="selected-files" >
                        </div>
                        <div class="file-preview box"></div>
                    </div>

                    <div class="form-group mb-3 text-right">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> {{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
  </div>
</div>

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('js')
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}" async
  defer></script>
<script type="text/javascript">

    function sort_cities(el){
        $('#sort_cities').submit();
    }

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