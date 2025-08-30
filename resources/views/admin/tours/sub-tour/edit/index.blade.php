<x-admin>
    @section('title','Edit Sub Tour ')
    @section('css')
    
    @endsection
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ $data->title }}</h5>
                    <div class="card-tools">
                        <a href="https://tourbeez.com/tour/{{ $data->slug }}" class="btn btn-primary btn-sm" target="_blank">{{translate('View Tour Online')}}</a>
                        <a href="{{ route('admin.tour.preview', encrypt($data->id)) }}" class="btn btn-success btn-sm">{{translate('Preview')}}</a>
                        <a href="{{ route('admin.tour.index') }}" class="btn btn-info btn-sm">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link active" href="{{ route('admin.tour.edit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Basic Details')}}</a>
                                
                                <a class="nav-link" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Scheduling')}}</a>

                                <a class="nav-link" href="{{ route('admin.tour.edit.gallery', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Gallery')}}</a>

                            </div>
                           
                        </div>
                        <div class="col-10">
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="basic_information" role="tabpanel" aria-labelledby="v-pills-tab-1">
                                    <div class="card">
                                        @include('admin.tours.sub-tour.edit.basic-details')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="addon" role="tabpanel" aria-labelledby="v-pills-profile-tab-2">
                                    <div class="card">
                                        @include('admin.tours.edit.addon')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="schduling" role="tabpanel" aria-labelledby="v-pills-messages-tab-3">
                                    <div class="card">
                                        @include('admin.tours.edit.schedule')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="location" role="tabpanel" aria-labelledby="v-pills-messages-tab-4">
                                    <div class="card">
                                        @include('admin.tours.edit.location')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pickup" role="tabpanel" aria-labelledby="v-pills-messages-tab-5">
                                    <div class="card">
                                        @include('admin.tours.edit.pickup')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="itinerary" role="tabpanel" aria-labelledby="v-pills-messages-tab-6">
                                    <div class="card">
                                        @include('admin.tours.edit.itinerary')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="faq" role="tabpanel" aria-labelledby="v-pills-messages-tab-7">
                                    <div class="card">
                                        @include('admin.tours.edit.faqs')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="includes" role="tabpanel" aria-labelledby="v-pills-messages-tab-8">
                                    <div class="card">
                                        @include('admin.tours.edit.includes')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="excludes" role="tabpanel" aria-labelledby="v-pills-messages-tab-9">
                                    <div class="card">
                                        @include('admin.tours.edit.excludes')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="taxes_nd_fees" role="tabpanel" aria-labelledby="v-pills-messages-tab-10">
                                    <div class="card">
                                        @include('admin.tours.edit.taxes_nd_fees')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="gallery" role="tabpanel" aria-labelledby="v-pills-messages-tab-11">
                                    <div class="card">
                                        @include('admin.tours.edit.gallery')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="message" role="tabpanel" aria-labelledby="v-pills-messages-tab-12">
                                    <div class="card">
                                        @include('admin.tours.edit.message')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="meta_information" role="tabpanel" aria-labelledby="v-pills-messages-tab-13">
                                    <div class="card">
                                        @include('admin.tours.edit.meta_information')
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="special_deposit" role="tabpanel" aria-labelledby="v-pills-messages-tab-13">
                                    <div class="card">
                                        @include('admin.tours.edit.special-deposit')
                                    </div>
                                </div>
                                
                                
                                
                                
                                
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
