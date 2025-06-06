<x-admin>
    @section('title','Preview Tour')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ $data->title }}</h5>
                    <div class="card-tools">
                        <a href="#" class="btn btn-primary btn-sm">{{translate('View Tour Online')}}</a>
                        <a href="{{ route('admin.tour.edit', encrypt($data->id)) }}" class="btn btn-info btn-sm">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                @include('admin.tours.preview.basic_detail')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.addon')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.schedule')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.location')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.pickup')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.itinerary')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.faqs')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.includes')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.excludes')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.taxes_nd_fees')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.gallery')
                            </div>
                            <div class="card">
                                @include('admin.tours.preview.message')
                            </div>
                            <div class="card">
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
