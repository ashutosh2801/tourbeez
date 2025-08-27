<x-admin>
    @section('title','Edit Tour')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ $data->title }}</h5>
                    <div class="card-tools">
                        <a href="https://tourbeez.com/tour/{{ $data->slug }}" class="btn btn-primary btn-sm" target="_blank">{{translate('View Tour Online')}}</a>
                        <a href="{{ route('admin.tour.index') }}" class="btn btn-info btn-sm">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link" href="{{ route('admin.tour.edit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Basic Details')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" ><i class="fas fa-caret-right"></i> {{translate('Extra')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Scheduling')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.location', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Location ')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Pickups')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.itinerary', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Itinerary')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.faqs', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('FAQs')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.inclusions', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Inclusions')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.exclusions', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Exclusions')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.taxesfees', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Taxes & Fees')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.gallery', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Gallery')}}</a>
                                <a class="nav-link active" href="{{ route('admin.tour.edit.message.notification', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Message')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.seo', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('SEO')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.special.deposit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate(' Special Deposit')}}</a>                                 
                            </div>
                        </div>
                        <div class="col-10">
                            <div class="tab-content" id="v-pills-tabContent">                                
                                <div class="tab-pane fade show active" id="message" role="tabpanel" aria-labelledby="v-pills-messages-tab-12">
                                    <div class="card">
                                        <div class="nav nav-pills mb-3 justify-center align-center" id="v-pills-tab" role="tablist" aria-orientation="horizontal">
                                            <a class="nav-link" href="{{ route('admin.tour.edit.message.notification', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Notification')}}</a>
                                            <a class="nav-link" href="{{ route('admin.tour.edit.message.reminder', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Remiders')}}</a>
                                            <a class="nav-link active" href="{{ route('admin.tour.edit.message.followup', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Followups')}}</a>
                                            <a class="nav-link" href="{{ route('admin.tour.edit.message.paymentrequest', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Payment Request')}}</a>
                                        </div>

                                        <div class="tab-content" id="v-pills-tabContent">
                                            <div class="tab-pane fade show active" id="followups" role="tabpanel" aria-labelledby="v-pills-tab-100">
                                                @include('admin.tours.edit.followup')
                                            </div>
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>   
</x-admin>
