<x-admin>
    @section('title','Edit Tour')
    @section('css')
    
    @endsection
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ $data->title }}</h5>
                    <div class="card-tools">
                        <a href="https://tourbeez.com/tour/{{ $data->slug }}" class="btn btn-primary btn-sm" target="_blank">{{translate('View Tour Onlinedds')}}</a>
                        <a href="{{ route('admin.tour.preview', encrypt($data->id)) }}" class="btn btn-success btn-sm">{{translate('Preview')}}</a>
                        <a href="{{ route('admin.tour.index') }}" class="btn btn-info btn-sm">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link active" href="{{ route('admin.tour.edit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Basic Details')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" ><i class="fas fa-caret-right"></i> {{translate('Extra')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Scheduling')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.location', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Location ')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Pickups')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.itinerary', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Itinerary')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.faqs', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('FAQs')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.inclusions', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Inclusions')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.exclusions', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Exclusions')}}</a>
                                 <a class="nav-link" href="{{ route('admin.tour.edit.optionals', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Optional')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.taxesfees', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Taxes & Fees')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.gallery', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Gallery')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.message.notification', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Message')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.booking', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Booking Info')}}</a>                               
                                <a class="nav-link" href="{{ route('admin.tour.edit.seo', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('SEO')}}</a> 
                                <a class="nav-link" href="{{ route('admin.tour.edit.special.deposit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Speacial Deposit')}}</a>  

                            </div>
                            <!-- <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link active" id="v-pills-tab-1" data-toggle="pill" href="#basic_information" role="tab" aria-controls="v-pills-profile" aria-selected="true"><i class="fas fa-caret-right"></i> {{translate('Basic Details')}}</a>
                                <a class="nav-link" id="v-pills-tab-2" data-toggle="pill" href="#addon" ><i class="fas fa-caret-right"></i> {{translate('Extra')}}</a>
                                <a class="nav-link" id="v-pills-tab-3" data-toggle="pill" href="#schduling" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Scheduling
')}}</a>
                                <a class="nav-link" id="v-pills-tab-4" data-toggle="pill" href="#location" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Location ')}}</a>
                                <a class="nav-link" id="v-pills-tab-5" data-toggle="pill" href="#pickup" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Pickups')}}</a>
                                <a class="nav-link" id="v-pills-tab-6" data-toggle="pill" href="#itinerary" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Itinerary')}}</a>
                                <a class="nav-link" id="v-pills-tab-7" data-toggle="pill" href="#faq" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('FAQs')}}</a>
                                <a class="nav-link" id="v-pills-tab-8" data-toggle="pill" href="#includes" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Inclusions')}}</a>
                                <a class="nav-link" id="v-pills-tab-9" data-toggle="pill" href="#excludes" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Exclusions')}}</a>
                                <a class="nav-link" id="v-pills-tab-10" data-toggle="pill" href="#taxes_nd_fees" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Taxes & Fees')}}</a>
                                <a class="nav-link" id="v-pills-tab-11" data-toggle="pill" href="#gallery" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Gallery')}}</a>
                                <a class="nav-link" id="v-pills-tab-12" data-toggle="pill" href="#message" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Message')}}</a>
                                <a class="nav-link" id="v-pills-tab-13" data-toggle="pill" href="#meta_information" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('SEO')}}</a>                               
                            </div> -->
                        </div>
                        <div class="col-10">
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="basic_information" role="tabpanel" aria-labelledby="v-pills-tab-1">
                                    <div class="card">
                                        @include('admin.tours.edit.basic_detail')
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
