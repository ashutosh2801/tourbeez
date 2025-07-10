<x-admin>

    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Edit City Info')}}</h5>
                    <a class="btn btn-warning float-right" href="{{ route('admin.cities.index') }}">Back</a>
                </div>
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

                        <div class="form-group mb-3">
                            <label for="name">{{translate('City Name')}}</label>
                            <input type="text" id="name" name="name" value="{{ ucwords($city->name) }}" class="form-control"
                                   required>
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
                                <input type="hidden" name="upload_id" class="selected-files" value="{{ $city->upload_id }}" >
                            </div>
                            <div class="file-preview box"></div>
                        </div>

                        <div class="form-group mb-3 text-right">
                            <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
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
@endsection
</x-admin>