<x-admin>
    @section('title','Update Addon')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Update Addon</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.addon.index') }}" class="btn btn-info btn-sm">Back</a>
                        </div>
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
                    <form class="needs-validation" novalidate action="{{ route('admin.addon.update', $data->id) }}" 
                    method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Enter name" required value="{{ old('name', $data->name) }}">
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
                                    placeholder="Enter price" required value="{{ old('price', $data->price) }}">
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
                                    placeholder="Enter description" required>{{ old('description', $data->description) }}</textarea>
                            </div>
                            @error('description')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror

                            
                            <div class="form-group row">
                                <label for="canonical_url" class="col-md-12">Availibility</label>
                                <button type="button" class="btn btn-sm btn-white flex col-md-4" id="get_url">
                                    <label><input onclick="visible_limit()" {{ old('is_availibility' ? 'checked' : '' ) }} type="checkbox" id="addon_is_availibility" name="is_availibility" value="1" /> This extra has limited availability</label>
                                </button>
                                <div class="input-group mb-3 col-md-6 {{ old('availibility', $data->availability) ? '' : 'd-none' }}" id="visible_limit">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Limit</span>
                                    </div>
                                    <input type="text" class="form-control col-md-6" id="availibility" name="availibility"
                                    placeholder="Enter limit" value="{{  old('availibility', $data->availability) }}">
                                </div>                                
                            </div>
                            

                            <div class="form-group">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" name="image" id="image" class="form-control" accept="image/*"
                                    >
                                @error('image')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary float-right">Save addon</button>
                        </div>
                    </form>
                </div>
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
