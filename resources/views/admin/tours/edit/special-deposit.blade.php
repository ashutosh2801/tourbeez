<div class="card">
    <div class="card-primary">
        <div class="card-header">
            <h3 class="card-title">Special Deposit Rule</h3>            
        </div>
        <div class="card-body"> 
            <div class="nav nav-pills mb-3 justify-center align-center" id="v-pills-tab" role="tablist" aria-orientation="horizontal">
                <a class="nav-link  active" id="v-pills-tab-100" data-toggle="pill" href="#notification" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Notification')}}</a>
                <a class="nav-link" id="v-pills-tab-101" data-toggle="pill" href="#reminders" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Remiders')}}</a>
                <a class="nav-link" id="v-pills-tab-102" data-toggle="pill" href="#followups" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Followups')}}</a>
                <a class="nav-link " id="v-pills-tab-102" data-toggle="pill" href="#payment-request" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fas fa-caret-right"></i> {{translate('Payment Request')}}</a>
            </div>

            <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="notification" role="tabpanel" aria-labelledby="v-pills-tab-100">
                    <div class="card">
                        @include('admin.tours.edit.notification')
                    </div>
                </div>
                <div class="tab-pane fade" id="reminders" role="tabpanel" aria-labelledby="v-pills-profile-tab-101">
                    <div class="card">
                        @include('admin.tours.edit.reminder')
                    </div>
                </div>
                <div class="tab-pane fade" id="followups" role="tabpanel" aria-labelledby="v-pills-profile-tab-101">
                    <div class="card">
                        @include('admin.tours.edit.followup')
                    </div>
                </div>
                <div class="tab-pane fade" id="payment-request" role="tabpanel" aria-labelledby="v-pills-profile-tab-101">
                    <div class="card">
                        @include('admin.tours.edit.payment_request')
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>

@section('modal')

@endsection
@section('js')

@endsection