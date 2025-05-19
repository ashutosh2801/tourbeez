<div class="card">
    <div class="card-info">
        <div class="card-header">
            <h3 class="card-title">Message Payment Request</h3>            
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="list-unstyled">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="needs-validation" novalidate action="{{ route('admin.tour.payment_request_update', $data->id) }}" 
        method="POST" enctype="multipart/form-data" autocomplete="off">
            @method('PUT')
            @csrf
            <div class="card-body">
                <!-- Emails -->
                <div class="row">                   
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <label class="form-label col-2">Emails</label>
                            <div class="col-lg-10">
                                
                                @php
                                    $payments = [1, 2, 3];
                                @endphp

                                @foreach ($payments as $i)
                                    @php
                                        $paymentKey = "email{$i}_payment";
                                        $showhideKey= "email{$i}paymentstype";
                                        $ptypeKey   = "email{$i}_payment_type";
                                        $percentKey = "email{$i}_payment_percent";
                                        $delayKey   = "email{$i}_payments_delay";
                                        $dateKey    = "email{$i}_payments_date";
                                        $unitKey    = "email{$i}_payment_typedate";
                                    @endphp

                                    <div class="form-group">
                                        <label style="font-weight:400">
                                            <input type="checkbox" name="Meta[{{ $paymentKey }}]" id="{{ $paymentKey }}"
                                                {{ old("Meta.$paymentKey", !empty($metaData[$paymentKey])) ? 'checked' : '' }}
                                            /> Automated Payment Request {{ $i }}
                                        </label>

                                        <div id="{{ $showhideKey }}" class="hidden"  style="max-width: 600px;">
                                            <div class="form-group">
                                                <select class="form-control" id="{{ $ptypeKey }}" name="Meta[{{ $ptypeKey }}]">
                                                    <option {{ (old("meta.$ptypeKey", $metaData[$ptypeKey] ?? '')=='PAYMENT_REQUEST_PERCENT') ? 'checked' : '' }} value="PAYMENT_REQUEST_PERCENT">Percentage of order total amount</option>
                                                    <option {{ (old("meta.$ptypeKey", $metaData[$ptypeKey] ?? '')=='PAYMENT_REQUEST_FIXED') ? 'checked' : '' }} value="PAYMENT_REQUEST_FIXED"> Fixed amount per order </option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Amount requested</span>
                                                    </div>
                                                    <input type="number" class="form-control" id="{{ $percentKey }}"
                                                        name="Meta[{{ $percentKey }}]" placeholder="Ex: 10"
                                                        value="{{ old("Meta.$percentKey", $metaData[$percentKey] ?? '') }}">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <input type="number" class="form-control" id="{{ $delayKey }}"
                                                        name="Meta[{{ $delayKey }}]" placeholder="Ex: 10" 
                                                        value="{{ old("Meta.$delayKey", $metaData[$delayKey] ?? '') }}">
                                                        <input type="text" class="form-control aiz-date-range hidden" id="{{ $dateKey }}"
                                                        name="Meta[{{ $dateKey }}]" placeholder="Ex: {{ date('Y-m-d') }}" data-single="true" data-show-dropdown="true"
                                                        value="{{ old("Meta.$dateKey", $metaData[$dateKey] ?? '') }}">                                                        
                                                    </div>                                                    
                                                    <select class="form-control" id="{{ $unitKey }}" name="Meta[{{ $unitKey }}]">
                                                        <option {{ (old("meta.$unitKey", $metaData[$unitKey] ?? '')=='TOUR_DATE') ? 'selected' : '' }} value="TOUR_DATE" selected="selected">Days before tour date</option>
                                                        <option {{ (old("meta.$unitKey", $metaData[$unitKey] ?? '')=='ORDER_DATE') ? 'selected' : '' }} value="ORDER_DATE">Days after order date</option>
                                                        <option {{ (old("meta.$unitKey", $metaData[$unitKey] ?? '')=='SPECIFIC_DATE') ? 'selected' : '' }} value="SPECIFIC_DATE">Specific date</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </div>

                                    @section('js')
                                    @parent
                                    <script>
                                    function {{ $paymentKey }}() {
                                        if ($('#{{ $paymentKey }}').is(':checked')) {
                                            $('#{{ $showhideKey }}').removeClass('hidden');
                                        } else {
                                            $('#{{ $showhideKey }}').addClass('hidden');
                                        }
                                    }
                                    {{ $paymentKey }}();
                                    $('#{{ $paymentKey }}').on('change', {{ $paymentKey }});

                                    function {{ $unitKey }}() {
                                        if ( $('#{{ $unitKey }}').val() == 'SPECIFIC_DATE' ) {
                                            $('#{{ $dateKey }}').removeClass('hidden');
                                            $('#{{ $delayKey }}').addClass('hidden');
                                            //TB.plugins.dateRange();
                                        } else {
                                            $('#{{ $dateKey }}').addClass('hidden');
                                            $('#{{ $delayKey }}').removeClass('hidden');
                                            TB.plugins.dateRange();
                                        }
                                    }
                                    {{ $unitKey }}();
                                    $('#{{ $unitKey }}').on('change', {{ $unitKey }});
                                    </script>
                                    @endsection

                                @endforeach


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" id="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.tour.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@section('js')
@parent
<script>
$('#pricing').on('change', function() {
    if ($(this).val() == 'PER_PERSON') {
        $('.quantity_used').removeClass('hidden');
    } else {
        $('.quantity_used').addClass('hidden');
        $('.priceOptionsWra').html('');
        priceOptionCount = 1;
    }
});

$('#IsTermsAndConditions').on('click', function() {
    if ($(this).is(':checked')) {
        $('#terms_and_conditions_wra').removeClass('hidden');
    } else {
        $('#terms_and_conditions_wra').addClass('hidden');
    }
});
$('#IsPurchasedAsAGift').on('click', function() {
    if ($(this).is(':checked')) {
        $('#IsPurchasedAsAGift_show').removeClass('hidden');
    } else {
        $('#IsPurchasedAsAGift_show').addClass('hidden');
    }
});
$('#IsExpiryDays').on('click', function() {
    if ($(this).is(':checked')) {
        $('#expiry_days_wra').removeClass('hidden');
    } else {
        $('#expiry_days_wra').addClass('hidden');
    }
});
$('#IsExpiryDate').on('click', function() {
    if ($(this).is(':checked')) {
        $('#expiry_date_wra').removeClass('hidden');
    } else {
        $('#expiry_date_wra').addClass('hidden');
    }
});
</script>
@endsection