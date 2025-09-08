<x-admin>
    @section('title')
        {{ 'Payment Settings' }}
    @endsection
    <div class="row">

        <div class="col-lg-6 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ __('Online Booking Fee') }}</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payment_method.update') }}" method="POST">
                        @csrf
                  
                        <div class="form-group row">
                            <div class="option">
                                <input type="hidden" name="types[]" value="price_booking_fee">
                                  <label class="mb-0">
                                    <input value="1" name="price_booking_fee" id="includes" type="radio" @if (get_setting('price_booking_fee') == 1)
                                    checked
                                    @endif>
                                </label>
                                <label for="includes">Price includes booking fee</label>
                            </div>
                        </div>
                        <div class="form-group row {{ get_setting('price_booking_fee') == 1 ?? 'hidden' }}" id="include_fee">
                            <input type="hidden" name="types[]" value="tour_booking_fee">
                            <input type="hidden" name="types[]" value="tour_booking_fee_type">

                            <div id="deposit_options" class="">
                                <label for="tour_booking_fee">Booking Fee Amount</label>
                            <div class="form-row">

                                <div class="col-md-6">
                                    <select class="form-control" name="tour_booking_fee_type" id="tour_booking_fee_type">   
                                        <option value="PERCENT" {{ old('tour_booking_fee_type', get_setting('tour_booking_fee_type')) == 'PERCENT' ? 'selected' : '' }}>PERCENT</option>
                                        <option value="FIXED" {{ old('tour_booking_fee_type', get_setting('tour_booking_fee_type')) == 'FIXED' ? 'selected' : '' }}>FIXED</option>
        
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <input type="text" 
                                        placeholder="Enter fee amount" 
                                        name="tour_booking_fee" 
                                        value="{{ get_setting('tour_booking_fee') }}" 
                                        class="form-control" id="tour_booking_fee" 
                                    />
                                </div>
                                <!-- <div class="col-md-1">
                                    <span id="deposit_unit">
                                        @php
                                            $unit = match($specialDeposit?->charge) {
                                                'DEPOSIT_PERCENT' => '%',
                                                'DEPOSIT_FIXED', 'DEPOSIT_FIXED_PER_ORDER' => '$',
                                                default => ''
                                            };
                                        @endphp
                                        {{ $unit }}
                                    </span>
                                </div> -->
                            </div>
                        </div>

                            
                        </div>
                        <div class="form-group row">
                              <div class="option">
                                <label class="mb-0">
                                    <input value="0" name="price_booking_fee" id="excludes" type="radio" @if (get_setting('price_booking_fee') == 0)
                                    checked
                                    @endif>
                                </label>
                                <label for="excludes">Price excludes booking fee</label>
                            </div>
                        </div>
                        
                       
                       
                        <div style="display:block; border-top:1px solid #ddd; margin-top:15px;padding-top:15px;">
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Special Deposit --}}
        <div class="col-lg-6 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ __('Deposit Rules') }}</h1>
                </div>
                <div class="card-body">
                    <form class="needs-validation" novalidate action="{{ route('admin.global.special-deposit') }}" method="POST" enctype="multipart/form-data">
                         
                        @method('PUT')
                        @csrf
                        
                        {{-- Use special deposit rules --}}
                        <div class="form-group form-check">
                            <input type="hidden" name="tour[use_deposit]" value="0">
                            <input type="checkbox" class="form-check-input" id="use_deposit"
                                name="tour[use_deposit]" value="1"
                                {{ old('tour.use_deposit', $specialDeposit?->use_deposit) ? 'checked' : '' }}>
                            <label class="form-check-label" for="use_deposit">
                                Use special deposit rules
                            </label>
                        </div>

                        {{-- Deposit Type --}}
                        <div id="deposit_options" class="{{ old('tour.use_deposit', $specialDeposit?->use_deposit) ? '' : 'd-none' }}">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <select class="form-control" name="tour[charge]" id="tour_charge">
                                        <option value="FULL" {{ old('tour.charge', $specialDeposit?->charge) == 'FULL' ? 'selected' : '' }}>Full amount</option>
                                        <option value="DEPOSIT_PERCENT" {{ old('tour.charge', $specialDeposit?->charge) == 'DEPOSIT_PERCENT' ? 'selected' : '' }}>Deposit (% of order total amount)</option>
                                        <option value="DEPOSIT_FIXED" {{ old('tour.charge', $specialDeposit?->charge) == 'DEPOSIT_FIXED' ? 'selected' : '' }}>Deposit (Fixed amount per person/quantity)</option>
                                        <option value="DEPOSIT_FIXED_PER_ORDER" {{ old('tour.charge', $specialDeposit?->charge) == 'DEPOSIT_FIXED_PER_ORDER' ? 'selected' : '' }}>Deposit (Fixed amount per order)</option>
                                        <option value="NONE" {{ old('tour.charge', $specialDeposit?->charge) == 'NONE' ? 'selected' : '' }}>No charge</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="tour[deposit_amount]" class="form-control"
                                        placeholder="Deposit"
                                        value="{{ old('tour.deposit_amount', $specialDeposit?->deposit_amount) }}">
                                </div>
                                <div class="col-md-1">
                                    <span id="deposit_unit">
                                        @php
                                            $unit = match($specialDeposit?->charge) {
                                                'DEPOSIT_PERCENT' => '%',
                                                'DEPOSIT_FIXED', 'DEPOSIT_FIXED_PER_ORDER' => '$',
                                                default => ''
                                            };
                                        @endphp
                                        {{ $unit }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Allow customers to pay full amount --}}
                        <div class="form-group form-check mt-3">
                            <input type="hidden" name="tour[allow_full_payment]" value="0">
                            <input type="checkbox" class="form-check-input" id="allow_full_payment"
                                    name="tour[allow_full_payment]" value="1"
                                    {{ old('tour.allow_full_payment', $specialDeposit?->allow_full_payment) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_full_payment">
                                Allow customers to pay full amount
                            </label>
                        </div>

                        {{-- Add minimum notice --}}
                        <div class="form-group form-check">
                            <input type="hidden" name="tour[use_minimum_notice]" value="0">
                            <input type="checkbox" class="form-check-input" id="use_minimum_notice"
                                    name="tour[use_minimum_notice]" value="1"
                                    {{ old('tour.use_minimum_notice', $specialDeposit?->use_minimum_notice) ? 'checked' : '' }}>
                            <label class="form-check-label" for="use_minimum_notice">
                                Add minimum notice
                            </label>
                        </div>

                        <div id="minimum_notice_days" class="{{ old('tour.use_minimum_notice', $specialDeposit?->use_minimum_notice) ? '' : 'd-none' }}">
                            <label for="notice_days">Charge full amount if booking</label>
                            <input type="number" name="tour[notice_days]" id="notice_days"
                                    class="form-control d-inline-block w-auto"
                                    value="{{ old('tour.notice_days', $specialDeposit?->notice_days) }}">
                            <span>days before tour date</span>
                        </div>
                            
                        <div style="display:block; border-top:1px solid #ddd; margin-top:15px;padding-top:15px;">
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- End Special Deposit --}}


        <!-- Paypal -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ translate('Paypal Credential') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payment_method.update') }}" method="POST">
                        <input type="hidden" name="payment_method" value="paypal">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="paypal_payment_activation" type="checkbox" @if (get_setting('paypal_payment_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYPAL_CLIENT_ID">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Paypal Client Id') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYPAL_CLIENT_ID"
                                    value="{{ env('PAYPAL_CLIENT_ID') }}"
                                    placeholder="{{ translate('Paypal Client ID') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYPAL_CLIENT_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Paypal Client Secret') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYPAL_CLIENT_SECRET"
                                    value="{{ env('PAYPAL_CLIENT_SECRET') }}"
                                    placeholder="{{ translate('Paypal Client Secret') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Paypal Sandbox Mode') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="paypal_sandbox" type="checkbox" @if (get_setting('paypal_sandbox') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-left">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stripe -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ translate('Stripe Credential') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="stripe">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="stripe_payment_activation" type="checkbox" @if (get_setting('stripe_payment_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="STRIPE_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Stripe Key') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="STRIPE_KEY"
                                    value="{{ env('STRIPE_KEY') }}" placeholder="{{ translate('STRIPE KEY') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="STRIPE_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Stripe Secret') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="STRIPE_SECRET"
                                    value="{{ env('STRIPE_SECRET') }}"
                                    placeholder="{{ translate('STRIPE SECRET') }}">
                            </div>
                        </div>
                        <div class="form-group mb-0 text-left">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @php
            /*
        <!-- Instamojo -->
        {{-- <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ translate('Instamojo Credential') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="instamojo">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="instamojo_payment_activation" type="checkbox" @if (get_setting('instamojo_payment_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="INSTAMOJO_API_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Instamojo API Key') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="INSTAMOJO_API_KEY"
                                    value="{{ env('INSTAMOJO_API_KEY') }}"
                                    placeholder="{{ translate('Instamojo API Key') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="INSTAMOJO_AUTH_TOKEN">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Instamojo Auth Token') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="INSTAMOJO_AUTH_TOKEN"
                                    value="{{ env('INSTAMOJO_AUTH_TOKEN') }}"
                                    placeholder="{{ translate('Instamojo Auth Token') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Instamojo Sandbox Mode') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="instamojo_sandbox" type="checkbox" @if (get_setting('instamojo_sandbox') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        <!-- Razorpay -->
        {{-- <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ translate('RazorPay Credential') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="razorpay">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="razorpay_payment_activation" type="checkbox" @if (get_setting('razorpay_payment_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="RAZORPAY_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Razorpay Key') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="RAZORPAY_KEY"
                                    value="{{ env('RAZORPAY_KEY') }}" placeholder="{{ translate('Razorpay Key') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="RAZORPAY_SECRET">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Razorpay Secret') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="RAZORPAY_SECRET"
                                    value="{{ env('RAZORPAY_SECRET') }}"
                                    placeholder="{{ translate('Razorpay Secret') }}">
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        {{-- Paytm --}}
        {{-- <div class="col-lg-6 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Paytm Credential') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('admin.payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="paytm">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="paytm_payment_activation" type="checkbox" @if (get_setting('paytm_payment_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYTM_ENVIRONMENT">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ translate('Paytm Environment') }}</label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control aiz-selectpicker" name="PAYTM_ENVIRONMENT" required>
                                    <option value="production" @if(env('PAYTM_ENVIRONMENT') == 'production') selected @endif>{{translate('Production')}}</option>
                                    <option value="local" @if(env('PAYTM_ENVIRONMENT') == 'local') selected @endif>{{translate('Local')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYTM_MERCHANT_ID">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ translate('Paytm Merchant ID') }}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="PAYTM_MERCHANT_ID"
                                    value="{{ env('PAYTM_MERCHANT_ID') }}" placeholder="PAYTM MERCHANT ID" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYTM_MERCHANT_KEY">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ translate('Paytm Merchant Key') }}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="PAYTM_MERCHANT_KEY"
                                    value="{{ env('PAYTM_MERCHANT_KEY') }}" placeholder="PAYTM MERCHANT KEY">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYTM_MERCHANT_WEBSITE">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ translate('Paytm Merchant Website') }}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="PAYTM_MERCHANT_WEBSITE"
                                    value="{{ env('PAYTM_MERCHANT_WEBSITE') }}" placeholder="PAYTM MERCHANT WEBSITE">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYTM_CHANNEL">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ translate('Paytm Channel') }}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="PAYTM_CHANNEL"
                                    value="{{ env('PAYTM_CHANNEL') }}" placeholder="PAYTM CHANNEL">
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYTM_INDUSTRY_TYPE">
                            <div class="col-lg-4">
                                <label class="col-from-label">{{ translate('PAYTM INDUSTRY TYPE') }}</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="PAYTM_INDUSTRY_TYPE"
                                    value="{{ env('PAYTM_INDUSTRY_TYPE') }}" placeholder="PAYTM INDUSTRY TYPE">
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        <!-- Paystack -->
        {{-- <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ translate('PayStack Credential') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('admin.payment_method.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" value="paystack">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="paystack_payment_activation" type="checkbox" @if (get_setting('paystack_payment_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYSTACK_PUBLIC_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('PUBLIC KEY') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYSTACK_PUBLIC_KEY"
                                    value="{{ env('PAYSTACK_PUBLIC_KEY') }}"
                                    placeholder="{{ translate('PUBLIC KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYSTACK_SECRET_KEY">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('SECRET KEY') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYSTACK_SECRET_KEY"
                                    value="{{ env('PAYSTACK_SECRET_KEY') }}"
                                    placeholder="{{ translate('SECRET KEY') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MERCHANT_EMAIL">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('MERCHANT EMAIL') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="MERCHANT_EMAIL"
                                    value="{{ env('MERCHANT_EMAIL') }}"
                                    placeholder="{{ translate('MERCHANT EMAIL') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="PAYSTACK_CURRENCY_CODE">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('PAYSTACK CURRENCY CODE') }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="PAYSTACK_CURRENCY_CODE"
                                    value="{{ env('PAYSTACK_CURRENCY_CODE') }}"
                                    placeholder="{{ translate('PAYSTACK CURRENCY CODE') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        <!-- Manual Payment Method 1 -->
        {{-- <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ translate('Manual Payment Method 1') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="hidden" name="types[]" value="manual_payment_1_activation">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="manual_payment_1_activation" type="checkbox" @if (get_setting('manual_payment_1_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ translate('Name') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="hidden" name="types[]" value="manual_payment_1_name">
                                <input type="text" class="form-control" name="manual_payment_1_name"
                                    value="{{ get_setting('manual_payment_1_name') }}"
                                    placeholder="{{ translate('Name') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ translate('Instruction') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="hidden" name="types[]" value="manual_payment_1_instruction">
                                <textarea class="aiz-text-editor form-control" name="manual_payment_1_instruction"
                                    data-buttons='[["font", ["bold", "underline", "italic"]],["para", ["ul", "ol"]],["view", ["undo","redo"]]]'
                                    placeholder="Type.."
                                    data-min-height="200">{{ get_setting('manual_payment_1_instruction') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ translate('Image') }}
                                <small>(Size)</small></label>
                            <div class="col-md-9">
                                <input type="hidden" name="types[]" value="manual_payment_1_image">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="manual_payment_1_image" class="selected-files"
                                        value="{{ get_setting('manual_payment_1_image') }}">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        <!-- Manual Payment Method 2 -->
        {{-- <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0 h6 ">{{ translate('Manual Payment Method 2') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ translate('Activation') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="hidden" name="types[]" value="manual_payment_2_activation">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="manual_payment_2_activation" type="checkbox" @if (get_setting('manual_payment_2_activation') == 1)
                                    checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ translate('Name') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="hidden" name="types[]" value="manual_payment_2_name">
                                <input type="text" class="form-control" name="manual_payment_2_name"
                                    value="{{ get_setting('manual_payment_2_name') }}"
                                    placeholder="{{ translate('Name') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label class="col-from-label">{{ translate('Instruction') }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="hidden" name="types[]" value="manual_payment_2_instruction">
                                <textarea class="aiz-text-editor form-control" name="manual_payment_2_instruction"
                                    data-buttons='[["font", ["bold", "underline", "italic"]],["para", ["ul", "ol"]],["view", ["undo","redo"]]]'
                                    placeholder="Type.."
                                    data-min-height="200">{{ get_setting('manual_payment_2_instruction') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ translate('Image') }}
                                <small>(Size)</small></label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="manual_payment_2_image">
                                    <input type="hidden" name="manual_payment_2_image" class="selected-files"
                                        value="{{ get_setting('manual_payment_2_image') }}">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        */
                @endphp


    </div>

@section('js')
@parent()
<script>
    $(function () {
        // toggle deposit section
        $('#use_deposit').on('change', function () {
        	console.log(23423);
            $('#deposit_options').toggleClass('d-none', !this.checked);
        });

        // toggle minimum notice section
        $('#use_minimum_notice').on('change', function () {
            $('#minimum_notice_days').toggleClass('d-none', !this.checked);
        });
    });
</script>
<script>
function toggleBookingFeeField() {
    if ($("#includes").is(":checked")) {
        $("#include_fee").removeClass('hidden');
    }
    else if($("#excludes").is(":checked")) {
        $("#include_fee").addClass('hidden');
    }
    else {
        $("#include_fee").addClass('hidden');
    }
}    
$(document).ready(function () {

    // Initial state
    toggleBookingFeeField();

    // On radio change
    $("input[name='price_booking_fee']").change(function () {
        toggleBookingFeeField();
    });
});
</script>
@endsection    
</x-admin>
