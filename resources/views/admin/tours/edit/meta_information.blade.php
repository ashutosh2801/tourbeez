<div class="card">
    <div class="card-primary">
        <div class="card-header">
            <h3 class="card-title">SEO </h3>            
        </div>
        <form class="needs-validation" novalidate action="{{ route('admin.tour.seo_update', $data) }}"
            method="POST" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">

            <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" value="{{ $detail->meta_title }}"
                                        class="form-control" placeholder="Enter meta title">
                                    @error('meta_title')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <input type="text" name="meta_description" id="meta_description" value="{{ $detail->meta_description }}"
                                        class="form-control"  placeholder="Enter meta description">
                                    @error('meta_description')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" id="meta_keywords" value="{{ $detail->meta_keywords }}"
                                        class="form-control"  placeholder="Enter meta keywords">
                                    @error('meta_keywords')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="canonical_url" class="form-label">Canonical URL</label>
                                    <input type="text" name="canonical_url" id="canonical_url" value="{{ $detail->canonical_url }}"
                                        class="form-control"  placeholder="Enter canonical url">
                                    @error('canonical_url')
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