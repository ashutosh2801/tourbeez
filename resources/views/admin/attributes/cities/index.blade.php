<x-admin>
@section('title','Cities')

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header row gutters-5">
                <div class="col text-center text-md-left">
                    <h5 class="mb-md-0 h6">{{ translate('All Cities') }}</h5>
                </div>
                <div class="col-md-4">
                    <form class="" id="sort_cities" action="" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
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
                                <td><img class="img-md" src="{{ uploaded_asset($city->upload_id) }}" height="45px" alt="{{translate('photo')}}" /></td>
                                <td>{{ucwords($city->name)}}</td>
                                <td>{{ucwords($city->state->name)}}</td>
                                <td>{{ucwords($city->state->country->name)}}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.cities.edit', encrypt($city->id)) }}" class="btn btn-soft-info btn-icon btn-circle btn-sm" title="{{ translate('Edit') }}">
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
                <div class="aiz-pagination">
                    {{ $cities->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Add New City')}}</h5>
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

                    <div class="form-group mb-3">
                        <label for="name">{{translate('City Name')}}</label>
                        <input type="text" id="name" name="name" placeholder="{{ translate('City Name') }}"
                               class="form-control" required>
                       @error('name')
                           <small class="form-text text-danger">{{ $message }}</small>
                       @enderror
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
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('js')
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
@endsection
</x-admin>