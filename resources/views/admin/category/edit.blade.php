<x-admin>
    @section('title','Edit Category')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card-primary mb-3">
                <div class="card-header edit-cat-header">
                    <div class="row">
                        <div class="col-md-8 col-6">
                            <h3 class="card-title">Edit Category</h3>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="card-tools">
                                <a href="{{ route('admin.category.index') }}" class="btn btn-sm btn-back">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-primary bg-white border rounded-lg-custom">
                <form class="needs-validation" novalidate action="{{ route('admin.category.update',$data) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">

                    <div class="card-body">

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter category name" required value="{{ $data->name }}">
                        </div>
                        <x-error>name</x-error>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                placeholder="Enter slug" required value="{{ $data->slug }}">
                        </div>
                        <x-error>slug</x-error>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control aiz-text-editor">{{ old('description') ?: $data->description }}</textarea>
                            <small class="form-text text-right">Max 240 characters</small>
                            @error('description')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="tour" class="form-label">Tours *</label>
                            <select name="tour[]" id="tour" class="form-control aiz-selectpicker"  data-live-search="true" multiple>
                                @foreach ($tours as $tour)
                                    <option value="{{ $tour->id }}"
                                    {{ in_array( $tour->id, $tour_category)? 'selected' : '' }}>{{ $tour->title }}</option>
                                @endforeach
                            </select>
                            @error('tour')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text"
                                   class="form-control"
                                   id="meta_title"
                                   name="meta_title"
                                   placeholder="Enter meta title"
                                   value="{{ old('meta_title') ?: $data->meta_title }}">
                        </div>
                        <x-error>meta_title</x-error>

                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description"
                                placeholder="Enter category Meta Description" required>{{ $data->meta_description }}</textarea>
                        </div>
                        <x-error>meta_description</x-error>

                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text"
                               class="form-control"
                               name="meta_keywords"
                               placeholder="keyword1, keyword2, keyword3"
                               value="{{ old('meta_keywords') ?? (is_array($data->meta_keywords ?? null) ? implode(', ', $data->meta_keywords) : ($data->meta_keywords ?? '')) }}">
                        </div>
                        <x-error>meta_keywords</x-error>

                        <div class="form-group">
                            <label for="canonical_url">Canonical URL</label>
                            <input type="text" class="form-control" id="canonical_url" name="canonical_url"
                                required value="{{ $data->canonical_url }}">
                        </div>
                        <x-error>canonical_url</x-error>

                        <hr>
                        <h4 class="mb-3">FAQs</h4>

                        <div id="faq-wrapper">

                            @foreach($data->faqs as $faq)
                                <div class="faq-item border p-3 mb-3">
                                    <input type="hidden" name="faq_ids[]" value="{{ $faq->id }}">

                                    <div class="form-group">
                                        <label>Question</label>
                                        <input type="text" class="form-control"
                                               name="questions[]" value="{{ $faq->question }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Answer</label>
                                        <textarea class="form-control"
                                                  name="answers[]" rows="3" required>{{ $faq->answer }}</textarea>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-danger remove-faq">
                                        Remove
                                    </button>
                                </div>
                            @endforeach

                        </div>
                        

                        <button type="button" class="btn btn-sm btn-primary" id="add-faq">
                            + Add FAQ
                        </button>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right">
                            <i class="fas fa-save"></i> Update category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('js')
    <script>
        function generateSlug(text) {
            return text
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        function updateCanonical() {
            let slug = document.getElementById('slug').value;
            let id = "{{ $data->id }}";
            let baseUrl = "{{ url('/') }}";

            let canonical = baseUrl + '/' + slug + '/' + id + '-c3';
            document.getElementById('canonical_url').value = canonical;
        }

        document.addEventListener('DOMContentLoaded', function () {

            let nameInput = document.getElementById('name');
            let slugInput = document.getElementById('slug');

            nameInput.addEventListener('keyup', function () {
                let slug = generateSlug(this.value);
                slugInput.value = slug;
                updateCanonical();
            });

            slugInput.addEventListener('keyup', function () {
                this.value = generateSlug(this.value);
                updateCanonical();
            });

        });
    </script>
    <script>
        document.getElementById('add-faq').addEventListener('click', function(){
            let html = `
                <div class="faq-item border p-3 mb-3">
                <div class="form-group">
                <label>Question</label>
                <input type="text" class="form-control" name="questions[]">
                </div>
                <div class="form-group">
                <label>Answer</label>
                <textarea class="form-control" name="answers[]" rows="3"></textarea>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-faq">
                Remove
                </button>
                </div>`;
        document.getElementById('faq-wrapper')
            .insertAdjacentHTML('beforeend', html);
        });

        document.addEventListener('click', function(e){
            if(e.target.classList.contains('remove-faq')){
                e.target.closest('.faq-item').remove();
            }
        });
    </script>
    @endsection

</x-admin>