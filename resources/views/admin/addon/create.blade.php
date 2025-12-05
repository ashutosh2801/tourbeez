<x-admin>
    @section('title','Create Extra')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card-primary mb-3">
                <div class="card-header create-extra-header">
                    <div class="row">
                        <div class="col-md-8 col-6">
                            <h3 class="card-title">Create Extra</h3>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="card-tools">
                                <a href="{{ route('admin.addon.index') }}" class="btn btn-sm btn-back">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-primary bg-white border rounded-lg-custom">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="needs-validation" novalidate action="{{ route('admin.addon.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter name" required value="{{ old('name') }}">
                        </div>
                        @error('name')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror

                        <div class="form-group row">
                            <label for="price" class="col-md-12">Price</label>
                            <div class="input-group mb-3 col-md-4">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" id="price" name="price"
                                placeholder="Enter price" required value="{{ old('price') }}">
                            </div>
                            <select class="form-control col-md-4" name="customer_choice" id="customer_choice">
                                <option value="">Customer's choice</option>
                                <option {{ old('customer_choice' ? 'selected' : '' ) }} value="FIXED">Per Order</option>
                                <option {{ old('customer_choice' ? 'selected' : '' ) }} value="QUANTITY">Per Quantity</option>
                            </select>
                        </div>
                        @error('price')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                        @error('customer_choice')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror

                        <div class="form-group">
                            <label for="meta_description">Description</label>
                            <textarea type="text" class="form-control" rows="3" id="description" name="description"
                                placeholder="Enter description" required>{{ old('description') }}</textarea>
                        </div>
                        @error('description')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror

                        
                        <div class="form-group row">
                            <label for="canonical_url" class="col-md-12">Availibility</label>
                            <button type="button" class="btn btn-sm btn-white flex col-md-4 text-left" id="get_url">
                                <label><input onclick="visible_limit()" {{ old('is_availibility' ? 'checked' : '' ) }} type="checkbox" id="addon_is_availibility" name="is_availibility" value="1" /> This extra has limited availability</label>
                            </button>
                            <div class="input-group mb-3 col-md-6 {{ old('availibility') ? '' : 'd-none' }}" id="visible_limit">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Limit</span>
                                </div>
                                <input type="text" class="form-control col-md-6" id="availibility" name="availibility"
                                placeholder="Enter limit" value="{{  old('availibility') }}">
                            </div>                                
                        </div>
                        

                        <!-- <div class="form-group">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*"
                                >
                            @error('image')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div> -->
                        <div class="form-group" style="max-width:400px">
                            <label class="form-label">{{translate('Image')}}</label>
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
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right"><i class="fas fa-save"></i> Save</button>
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
