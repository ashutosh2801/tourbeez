<x-admin>
@section('title','Edit Partner')

<div class="row">
    <div class="col-lg-12">
        <div class="card-primary mb-3">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="card-title">Edit Partner</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <a class="btn btn-sm btn-back"
                           href="{{ route('admin.partners.index') }}">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-primary bg-white border rounded-lg-custom">
            <div class="card-body">
                <form action="{{ route('admin.partners.update',$partner->id) }}"
                      method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               id="name" 
                               value="{{ $partner->name }}"
                               class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               value="{{ $partner->slug }}"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Logo Upload</label>
                        <div class="input-group input-group-sm"
                             data-toggle="aizuploader"
                             data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Browse</div>
                            </div>
                            <div class="form-control file-amount">Choose Logo</div>
                            <input type="hidden"
                                   name="upload_id"
                                   value="{{ $partner->upload_id }}"
                                   class="selected-files">
                        </div>
                        <div class="file-preview box"></div>
                    </div>

                    <div class="form-group">
                        <label>OR Logo URL</label>
                        <input type="text"
                               name="logo_url"
                               value="{{ $partner->logo_url }}"
                               class="form-control">
                    </div>

                    <div class="text-right">
                        <button type="submit"
                                class="btn btn-success">
                            Update
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@section('js')
<script>
    $('#name').on('keyup', function () {
        let slug = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');

        $('#slug').val(slug);
    });
</script>
@endsection
</x-admin>
