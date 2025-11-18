<x-admin>
    @section('title','Edit Tour')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header card-primary">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-0 h6">{{translate('Member Profile Update')}}</h5>
                        </div>
                        <div class="col-md-4">
                            <div class="card-tools">
                                <a class="btn" href="{{ route('admin.tour.show', $data->id) }}">{{translate('View Profile')}}</a>
                                <a href="{{ route('admin.tour.index') }}" class="btn btn-info btn-sm">Back</a>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link active" id="v-pills-tab-1" data-toggle="pill" href="#introduction" role="tab" aria-controls="v-pills-home" aria-selected="true">{{translate('Introduction')}}</a>
                                <a class="nav-link" id="v-pills-tab-2" data-toggle="pill" href="#basic_information" role="tab" aria-controls="v-pills-profile" aria-selected="false">{{translate('Basic Information')}}</a>
                                <a class="nav-link" id="v-pills-tab-3" data-toggle="pill" href="#present_address" role="tab" aria-controls="v-pills-messages" aria-selected="false">{{translate('Address')}}</a>
                                <a class="nav-link" id="v-pills-tab-4" data-toggle="pill" href="#education" role="tab" aria-controls="v-pills-settings" aria-selected="false">{{translate('Education')}}</a>
                                <a class="nav-link" id="v-pills-tab-5" data-toggle="pill" href="#career" role="tab" aria-controls="v-pills-settings" aria-selected="false">{{translate('Career')}}</a>
                                <a class="nav-link" id="v-pills-tab-6" data-toggle="pill" href="#physical_attributes" role="tab" aria-controls="v-pills-settings" aria-selected="false">{{translate('Physical Attributes')}}</a>
                                <a class="nav-link" id="v-pills-tab-7" data-toggle="pill" href="#language" role="tab" aria-controls="v-pills-settings" aria-selected="false">{{translate('Language')}}</a>
                                <a class="nav-link" id="v-pills-tab-8" data-toggle="pill" href="#hobbies_interest" role="tab" aria-controls="v-pills-settings" aria-selected="false">{{translate('Hobbies & Interest')}}</a>
                                <a class="nav-link" id="v-pills-tab-9" data-toggle="pill" href="#attitudes_behavior" role="tab" aria-controls="v-pills-settings" aria-selected="false">{{translate('Personal Attitude & Behavior')}}</a>
                                <a class="nav-link" id="v-pills-tab-10" data-toggle="pill" href="#residency_information" role="tab" aria-controls="v-pills-settings" aria-selected="false">{{translate('Residency Information')}}</a>
                               
                            </div>
                        </div>
                        <div class="col-10">
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="introduction" role="tabpanel" aria-labelledby="v-pills-tab-1">
                                    <div class="card">
                                        @include('admin.tours.edit.basic_information')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="basic_information" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                    <div class="card">
                                    basic_information here
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="present_address" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                    <div class="card">
                                    present_address here
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




        <div class="col-md-10">
            <div class="card">
                <div class="card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Edit Tour</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.tour.index') }}" class="btn btn-info btn-sm">Back</a>
                        </div>
                    </div>
                    <form class="needs-validation" novalidate action="{{ route('admin.tour.update', $data) }}"
                        method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="title" class="form-label">Tour Title</label>
                                        <input type="text" name="title" id="title" value="{{ $data->title }}"
                                            class="form-control" required>
                                           
                                        @error('title')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="slug" class="form-label">Tour Slug</label>
                                        <input type="text" name="slug" id="slug" value="{{ $data->slug }}"
                                            class="form-control" required>

                                        @error('slug')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="collection">Description</label>
                                        <textarea type="text" name="description" id="description" class="form-control" rows="5" >{{ $data->description }}</textarea>
                                        @error('description')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="category" class="form-label">Category</label>
                                        <select name="category" id="category" class="form-control">
                                            <option value="" selected disabled>select the category</option>
                                            @foreach ($category as $cat)
                                                <option {{ $data->category == $cat->id ? 'selected' : '' }}
                                                    value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="subcategory" class="form-label">Sub Category</label>
                                        <select name="subcategory" id="subcategory" class="form-control">
                                            <option value="" selected disabled>select the subcategory</option>
                                        </select>
                                        @error('subcategory')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="regular_price" class="form-label">Regular Price</label>
                                        <input type="text" name="regular_price" id="regular_price" value="{{ $data->price }}"
                                            class="form-control" >
                                        @error('regular_price')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="duration" class="form-label">Duration</label>
                                        <input type="text" name="duration" id="duration" value="{{ $data->duration }}"
                                            class="form-control" >
                                            @error('duration')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="image" class="form-label">Image</label>
                                        <input type="file" name="image" id="image" class="form-control" accept="image/*"
                                            >
                                            @error('image')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="slider-images" class="form-label">Tour Slider Images</label>
                                        <input type="file" name="slider_images[]" id="slider-images" accept="image/*"
                                            class="form-control" multiple>
                                            @error('slider_images')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="row">
                                        @foreach ($tourImages as $tourImage)
                                            <div class="col-lg-2">
                                                <a href="{{ route('admin.remove.image', $tourImage->id) }}"
                                                    onclick="return confirm('Are you sure want to remove image?')">
                                                    <img src="{{ asset('product-slider-images/' . $tourImage->image) }}"
                                                        alt="" class="slider-img">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="margin: 0px 20px 20px;">
                            <div class="card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Meta Information</h3>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="meta_title" class="form-label">Meta Title</label>
                                                <input type="text" name="meta_title" id="meta_title" value="{{ $data->meta_title }}"
                                                    class="form-control" >
                                                    @error('meta_title')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="meta_description" class="form-label">Meta Description</label>
                                                <input type="text" name="meta_description" id="meta_description" value="{{ $data->meta_description }}"
                                                    class="form-control" >
                                                    @error('meta_description')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                                <input type="text" name="meta_keywords" id="meta_keywords" value="{{ $data->meta_keywords }}"
                                                    class="form-control" >
                                                    @error('meta_keywords')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" id="submit" class="btn btn-primary float-right">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-default">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">View Image</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="{{ asset('product-image/' . $data->image) }}" alt="" class="w-full modal-img">
                    <span class="text-muted">If you want to change image just add new image otherwise leave it.</span>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    @section('css')
        <style>
            img.w-full.modal-img {
                width: 100%;
                height: auto;
                object-fit: cover;
            }
            img.slider-img {
                width: 100px;
                height: auto;
                object-fit: cover;
            }
        </style>
    @endsection
    @section('js')
        <script>
            $("#category").on('change', function() {
                let category = $("#category").val();
                $("#submit").attr('disabled', 'disabled');
                $("#submit").html('Please wait');
                $.ajax({
                    url: "{{ route('admin.getsubcategory') }}",
                    type: 'GET',
                    data: {
                        category: category,
                    },
                    success: function(data) {
                        if (data) {
                            $("#submit").removeAttr('disabled', 'disabled');
                            $("#submit").html('Save');
                            $("#subcategory").html(data);
                        }
                    }
                });
            });
        </script>
    @endsection
</x-admin>
