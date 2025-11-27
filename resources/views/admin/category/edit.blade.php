<x-admin>
    @section('title','Edit Category')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Edit Category</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.category.index') }}" class="btn btn-info btn-sm">Back</a>
                        </div>
                    </div>
                    <form class="needs-validation" novalidate action="{{ route('admin.category.update',$data) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Enter category name" required value="{{ $data->name }}">
                            </div>
                            <x-error>name</x-error>

                            <div class="form-group">
                                <label for="meta_description">SEO Description</label>
                                <textarea type="text" class="form-control" id="meta_description" name="meta_description"
                                    placeholder="Enter category SEO Description" required>{{ $data->meta_description }}</textarea>
                            </div>
                            <x-error>meta_description</x-error>

                            <div class="form-group">
                                <label for="canonical_url">Rel Canonical URL</label>
                                <input type="text" class="form-control" id="canonical_url" name="canonical_url"
                                    placeholder="Enter category Rel Canonical URL" required value="{{ $data->anonical_url }}">
                            </div>
                            <x-error>canonical_url</x-error>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary float-right">Update category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin>
