<x-admin>
    @section('title','Create tour type')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card-primary mb-3">
                <div class="card-header create-tourType-header">
                    <div class="row">
                        <div class="col-md-8 col-6">
                             <h3 class="card-title">Create tour type</h3>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="card-tools">
                                <a href="{{ route('admin.tour_type.index') }}" class="btn btn-back btn-sm">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-primary bg-white border rounded-lg-custom">
                <form class="needs-validation" novalidate action="{{ route('admin.tour_type.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter tour type name" required value="{{ old('name') }}">
                        </div>
                        <x-error>name</x-error>

                        <div class="form-group">
                            <label for="meta_description">SEO Description</label>
                            <textarea type="text" class="form-control" id="description" name="description"
                                placeholder="Enter tourtype Description">{{ old('description') }}</textarea>
                        </div>
                        <x-error>description</x-error>
                        
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right"><i class="fas fa-save"></i> Save tour type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin>
