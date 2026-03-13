<x-admin>
    @section('title','Create Category')

    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card-primary mb-3">
                <div class="card-header create-category-header">
                    <div class="row">
                        <div class="col-md-8 col-8">
                            <h3 class="card-title">Create Category</h3>
                        </div>
                        <div class="col-md-4 col-4 text-right">
                            <a href="{{ route('admin.category.index') }}" 
                               class="btn btn-back btn-sm">Back</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-primary bg-white border rounded-lg-custom create-category-body">

                <form action="{{ route('admin.category.store') }}" method="POST">
                    @csrf

                    <div class="card-body">

                        {{-- Name --}}
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   class="form-control"
                                   placeholder="Enter category name"
                                   value="{{ old('name') }}"
                                   required>
                        </div>
                        <x-error>name</x-error>


                        {{-- Slug --}}
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text"
                                   name="slug"
                                   id="slug"
                                   class="form-control"
                                   placeholder="category-slug"
                                   value="{{ old('slug') }}"
                                   required>
                        </div>
                        <x-error>slug</x-error>
                        {{-- Description --}}
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      class="form-control aiz-text-editor">{{ old('description') }}</textarea>
                            <small class="form-text text-right">
                                Max 240 characters
                            </small>
                        </div>
                        <x-error>description</x-error>


                        

                        {{-- Meta Title --}}
                        <div class="form-group">
                            <label>Meta Title</label>
                            <input type="text"
                                   name="meta_title"
                                   class="form-control"
                                   placeholder="Enter meta title"
                                   value="{{ old('meta_title') }}">
                        </div>
                        <x-error>meta_title</x-error>


                        


                        {{-- Meta Description --}}
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description"
                                      class="form-control"
                                      required>{{ old('meta_description') }}</textarea>
                        </div>
                        <x-error>meta_description</x-error>

                        {{-- Meta Keywords --}}
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <input type="text"
                                   name="meta_keywords"
                                   class="form-control"
                                   placeholder="keyword1, keyword2, keyword3"
                                   value="{{ old('meta_keywords') }}">
                        </div>
                        <x-error>meta_keywords</x-error>

                        {{-- Canonical URL --}}
                        @php
                            $canonical_url = old('canonical_url') 
                                ? old('canonical_url') 
                                : env('APP_URL').'/category/';
                        @endphp

                        <div class="form-group">
                            <label>Canonical URL</label>
                            <input type="text"
                                   name="canonical_url"
                                   id="canonical_url"
                                   class="form-control"
                                   value="{{ $canonical_url }}"
                                   required>
                        </div>
                        <x-error>canonical_url</x-error>

                        

                    </div>

                    <div class="card-footer">
                        <button type="submit" 
                                class="btn btn-success float-right">
                            <i class="fas fa-save"></i> Save Category
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    @section('js')

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');
            const canonicalInput = document.getElementById('canonical_url');

            function generateSlug(text) {
                return text.toLowerCase()
                           .trim()
                           .replace(/[^a-z0-9\s-]/g, '')
                           .replace(/\s+/g, '-')
                           .replace(/-+/g, '-');
            }

            nameInput.addEventListener('keyup', function () {

                let slug = generateSlug(this.value);
                slugInput.value = slug;

                let baseUrl = "{{ env('APP_URL') }}/category/";
                canonicalInput.value = baseUrl + slug;
            });

        });
    </script>

    @endsection

</x-admin>