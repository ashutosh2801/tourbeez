<x-admin>
    @section('title','Preview Tour')
    
    <div class="row">
        <div class="col-lg-12 tour-edit-body">
            <div class="card-primary mb-3">
                <div class="card-header tour-edit-head">
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h5 class="card-title">{{ $data->title }}</h5>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="card-tools">
                                <a href="https://tourbeez.com/tour/{{ $data->slug }}" class="btn btn-view-tour" target="_blank">{{translate('View Tour Online')}}</a>
                                <a href="{{ route('admin.tour.index') }}" class="btn btn-back">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="preview-tour-body">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-12">
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.basic_detail')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.addon')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.schedule')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.location')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.pickup')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.itinerary')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.faqs')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.includes')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.excludes')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.taxes_nd_fees')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.gallery')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.message')
                            </div>
                            <div class="card-primary bg-white border rounded-lg-custom mb-3">
                                @include('admin.tours.preview.meta_information')
                            </div>                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
@parent
<script>
$(document).ready(function() {
    $('input, textarea, select').attr('disabled', true);
});  

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
