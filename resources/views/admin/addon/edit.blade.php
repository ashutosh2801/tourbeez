<x-admin>
    @section('title','Update Extra')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card-primary mb-3">
                <div class="card-header addon-edit-head">
                    <div class="row">
                        <div class="col-md-8 col-6">
                            <h3 class="card-title">Update Extra</h3>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="card-tools">
                                <a href="{{ route('admin.addon.index') }}" class="btn btn-back btn-sm">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-primary bg-white border rounded-lg-custom addon-edit-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="needs-validation" novalidate action="{{ route('admin.addon.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required value="{{ old('name', $data->name) }}">
                            </div>
                        </div>
                        @error('name')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror


                        


                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="price">Currency</label>
                            </div>
                            <div class="col-md-6 price-input">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <select name="currency" class="form-control mr-2">
                                        @foreach(config('constants.currencies') as $code => $country)
                                            <option value="{{ $code }}" {{ $code == $data->currency ? 'selected' : '' }}>{{ $code }} - {{ $country }}</option> 
                                        @endforeach

                            </select>
                                </div>
                            </div>
                            
                            
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="price">Price</label>
                            </div>
                            <div class="col-md-6 price-input">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="price" name="price"
                                    placeholder="Enter price" required value="{{ old('price', $data->price) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-control" name="customer_choice" id="customer_choice">
                                    <option value="">Customer's choice</option>
                                    <option {{ old('customer_choice' ? 'selected' : '' ) }} value="FIXED">Per Order</option>
                                    <option {{ old('customer_choice' ? 'selected' : '' ) }} value="QUANTITY">Per Quantity</option>
                                </select>
                            </div>
                            
                        </div>
                        @error('price')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                        @error('customer_choice')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror

                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="meta_description">Description</label>
                                <textarea type="text" class="form-control" rows="3" id="description" name="description" placeholder="Enter description" required>{{ old('description', $data->description) }}</textarea>
                            </div>
                        </div>
                        @error('description')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                        
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="canonical_url">Availibility</label>
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-white p-0" id="get_url">
                                    <label><input onclick="visible_limit()" {{ old('is_availibility' ? 'checked' : '' ) }} type="checkbox" id="addon_is_availibility" name="is_availibility" value="1" /> This extra has limited availability</label>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group {{ old('availibility', $data->availability) ? '' : 'd-none' }}" id="visible_limit">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Limit</span>
                                    </div>
                                    <input type="text" class="form-control col-md-7" id="availibility" name="availibility" placeholder="Enter limit" value="{{  old('availibility', $data->availability) }}">
                                </div>
                            </div>                        
                        </div>
                        

                        <div class="form-group" style="max-width:400px">
                            <label class="form-label">{{translate('Image')}}</label>
                            <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{translate('Choose Photo')}}</div>
                                <input type="hidden" name="image" class="selected-files" value="{{  old('image', $data->image) }}" >
                            </div>
                            <div class="file-preview box"></div>
                        </div>


                        <!-- <div class="form-group">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*"
                                >
                            @error('image')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div> -->
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right"><i class="fas fa-save"></i> Save addon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@section('js')
<script type="text/javascript">
function visible_limit() {
    $('#visible_limit').toggleClass('d-none');
}
</script>
@endsection
</x-admin>
