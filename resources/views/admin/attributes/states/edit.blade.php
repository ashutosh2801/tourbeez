<x-admin>
    <div class="row">
        <div class="col-lg-12">
            <div class="card-primary mb-3">
                <div class="card-header edit-state-head">
                    <div class="row">
                        <div class="col-md-8 col-6">
                            <h3 class="card-title">{{translate('Edit State Info')}}</h3>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="card-tools">
                                <a class="btn btn-sm btn-back" href="{{ route('admin.states.index') }}">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-primary bg-white border rounded-lg-custom edit-state-body">
                <div class="card-body">
                    <form action="{{ route('admin.states.update', $state->id) }}" method="POST">
                        <input name="_method" type="hidden" value="PATCH">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">{{translate('Country')}}</label>
                            <select class="form-control aiz-selectpicker" data-live-search="true" name="country_id" required>
                                @foreach($countries as $country)
                                    <option value="{{$country->id}}" @if($country->id == $state->country_id) selected @endif>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('religion')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="name">{{translate('State Name')}}</label>
                            <input type="text" id="name" name="name" value="{{ ucwords($state->name) }}" class="form-control"
                                   required>
                           @error('name')
                               <small class="form-text text-danger">{{ $message }}</small>
                           @enderror
                        </div>

                        <div class="form-group mb-3 text-right">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> {{translate('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin>