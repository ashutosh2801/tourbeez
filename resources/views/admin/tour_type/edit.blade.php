<x-admin>
    @section('title','Edit tour type')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card-primary mb-3">
                <div class="card-header edit-tourType-body">
                    <div class="row">
                        <div class="col-md-8 col-6">
                            <h3 class="card-title">Edit tour type</h3>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="card-tools">
                                <a href="{{ route('admin.tour_type.index') }}" class="btn btn-back btn-sm">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-primary bg-white border rounded-lg-custom edit-state-body">
                <form class="needs-validation" novalidate action="{{ route('admin.tour_type.update',$data) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter tour type name" required value="{{ $data->name }}">
                        </div>
                        <x-error>name</x-error>

                        <div class="form-group">
                            <label for="name">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                placeholder="Enter tour type slug" required value="{{ $data->slug }}">
                        </div>
                        <x-error>slug</x-error>

                        <div class="form-group">
                            <label for="meta_description">SEO Description</label>
                            <textarea type="text" class="form-control" id="description" name="description"
                                placeholder="Enter tour type Description">{{ $data->description }}</textarea>
                        </div>
                        <x-error>description</x-error>

                        
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right"><i class="fas fa-save"></i> Update tour type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin>
