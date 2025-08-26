<style>
tr:hover{cursor: pointer;}    
tr.dragging {opacity: 1;}
tr.drag-over-top {border-top: 3px solid blue;}
tr.drag-over-bottom {border-bottom: 3px solid blue;}
</style>

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
                                <a class="nav-link" href="{{ route('admin.tour.edit.optionals', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Optional')}}</a>
                                <a class="nav-link " href="{{ route('admin.tour.edit.taxesfees', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Taxes & Fees')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.gallery', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Gallery')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.message.notification', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Message')}}</a>
                                <a class="nav-link" href="{{ route('admin.tour.edit.seo', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('SEO')}}</a> 
                                <a class="nav-link active" href="{{ route('admin.tour.edit.special.deposit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Speacial Deposit')}}</a>                               
                            </div>
                        </div>
                        <div class="col-10">
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="taxes_nd_fees" role="tabpanel" aria-labelledby="v-pills-messages-tab-10">
                                	<form class="needs-validation" novalidate action="{{ route('admin.tour.special-deposit', $data->id) }}" method="POST" enctype="multipart/form-data">
                         
							            @method('PUT')
							            @csrf
	                                    <div class="card">
										    <div class="card-header">
										        <h3 class="card-title card-primary">Deposit Rules</h3>
										    </div>
										    <div class="card-body">
										        {{-- Use special deposit rules --}}
										        <div class="form-group form-check">
										            <input type="hidden" name="tour[use_deposit]" value="0">
										            <input type="checkbox" class="form-check-input" id="use_deposit"
										                   name="tour[use_deposit]" value="1"
										                   {{ old('tour.use_deposit', $data->detail?->use_deposit) ? 'checked' : '' }}>
										            <label class="form-check-label" for="use_deposit">
										                Use special deposit rules
										            </label>
										        </div>

										        {{-- Deposit Type --}}
										        <div id="deposit_options" class="{{ old('tour.use_deposit', $data->detail?->use_deposit) ? '' : 'd-none' }}">
										            <div class="form-row">
										                <div class="col-md-6">
										                    <select class="form-control" name="tour[charge]" id="tour_charge">
										                        <option value="FULL" {{ old('tour.charge', $data->detail?->charge) == 'FULL' ? 'selected' : '' }}>Full amount</option>
										                        <option value="DEPOSIT_PERCENT" {{ old('tour.charge', $data->detail?->charge) == 'DEPOSIT_PERCENT' ? 'selected' : '' }}>Deposit (% of order total amount)</option>
										                        <option value="DEPOSIT_FIXED" {{ old('tour.charge', $data->detail?->charge) == 'DEPOSIT_FIXED' ? 'selected' : '' }}>Deposit (Fixed amount per person/quantity)</option>
										                        <option value="DEPOSIT_FIXED_PER_ORDER" {{ old('tour.charge', $data->detail?->charge) == 'DEPOSIT_FIXED_PER_ORDER' ? 'selected' : '' }}>Deposit (Fixed amount per order)</option>
										                        <option value="NONE" {{ old('tour.charge', $data->detail?->charge) == 'NONE' ? 'selected' : '' }}>No charge</option>
										                    </select>
										                </div>
										                <div class="col-md-3">
										                    <input type="number" name="tour[deposit_amount]" class="form-control"
										                           placeholder="Deposit"
										                           value="{{ old('tour.deposit_amount', $data->detail?->deposit_amount) }}">
										                </div>
										                <div class="col-md-1">
										                    <span id="deposit_unit">
										                        {{-- Dynamically updated (% or fixed symbol) --}}
										                        @php
										                            $unit = match($data->detail?->charge) {
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
										                   {{ old('tour.allow_full_payment', $data->detail?->allow_full_payment) ? 'checked' : '' }}>
										            <label class="form-check-label" for="allow_full_payment">
										                Allow customers to pay full amount
										            </label>
										        </div>

										        {{-- Add minimum notice --}}
										        <div class="form-group form-check">
										            <input type="hidden" name="tour[use_minimum_notice]" value="0">
										            <input type="checkbox" class="form-check-input" id="use_minimum_notice"
										                   name="tour[use_minimum_notice]" value="1"
										                   {{ old('tour.use_minimum_notice', $data->detail?->use_minimum_notice) ? 'checked' : '' }}>
										            <label class="form-check-label" for="use_minimum_notice">
										                Add minimum notice
										            </label>
										        </div>

										        <div id="minimum_notice_days" class="{{ old('tour.use_minimum_notice', $data->detail?->use_minimum_notice) ? '' : 'd-none' }}">
										            <label for="notice_days">Charge full amount if booking</label>
										            <input type="number" name="tour[notice_days]" id="notice_days"
										                   class="form-control d-inline-block w-auto"
										                   value="{{ old('tour.notice_days', $data->detail?->notice_days) }}">
										            <span>days before tour date</span>
										        </div>
										    </div>
										</div>
										<div class="card-footer" style="display:block">
							                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
							                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
							                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}" class="btn btn-primary">Next</a>
							            </div>
									</form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin>


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

        // update unit dynamically
        $('#tour_charge').on('change', function () {
            let val = $(this).val();
            let unit = '';
            if (val === 'DEPOSIT_PERCENT') unit = '%';
            else if (val === 'DEPOSIT_FIXED' || val === 'DEPOSIT_FIXED_PER_ORDER') unit = '$';
            $('#deposit_unit').text(unit);
        }).trigger('change');
    });
</script>



