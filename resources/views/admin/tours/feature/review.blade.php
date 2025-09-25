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
                                <a class="nav-link " href="{{ route('admin.tour.edit.special.deposit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate(' Special Deposit')}}</a> 
                                <a class="nav-link active" href="{{ route('admin.tour.edit.review', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Review')}}</a>                               
                            </div>
                        </div>
                        <div class="col-10">
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="taxes_nd_fees" role="tabpanel" aria-labelledby="v-pills-messages-tab-10">
                                	<form class="needs-validation" novalidate action="{{ route('admin.tour.review', $data->id) }}" method="POST" enctype="multipart/form-data">
                         
							            @method('PUT')
							            @csrf
	                                    <div class="card card-primary mt-4">
                                            <div class="card-header">
                                                <h3 class="card-title">Review & Highlights</h3>
                                            </div>
                                            <div class="card-body">

                                                {{-- Review Section --}}
                                                <div class="form-group form-check">
                                                    <input type="hidden" name="review[use_review]" value="0">
                                                    <input type="checkbox" class="form-check-input" id="use_review"
                                                           name="review[use_review]" value="1"
                                                           {{ old('review.use_review', $tourReview?->use_review) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="use_review">Enable Review</label>
                                                </div>
                                                <div id="review_options" class="{{ old('review.use_review', $tourReview?->use_review) ? '' : 'd-none' }}">
                                                    <input type="text" name="review[review_heading]" class="form-control mb-2"
                                                           placeholder="Review Heading"
                                                           value="{{ old('review.review_heading', $tourReview?->review_heading) }}">
                                                    <textarea name="review[review_text]" class="form-control mb-2"
                                                              placeholder="Review Text">{{ old('review.review_text', $tourReview?->review_text) }}</textarea>
                                                    <select name="review[review_rating]" class="form-control mb-2">
                                                        <option value="">Select Rating</option>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <option value="{{ $i }}" {{ old('review.review_rating', $tourReview?->review_rating) == $i ? 'selected' : '' }}>
                                                                {{ $i }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                    <input type="number" name="review[review_count]" class="form-control mb-2"
                                                           placeholder="Review Count"
                                                           value="{{ old('review.review_count', $tourReview?->review_count) }}">
                                                </div>

                                                {{-- Recommended --}}
                                                <div class="form-group form-check">
                                                    <input type="hidden" name="review[use_recommended]" value="0">
                                                    <input type="checkbox" class="form-check-input" id="use_recommended"
                                                           name="review[use_recommended]" value="1"
                                                           {{ old('review.use_recommended', $tourReview?->use_recommended) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="use_recommended">Enable Recommended</label>
                                                </div>
                                                <div id="recommended_options" class="{{ old('review.use_recommended', $tourReview?->use_recommended) ? '' : 'd-none' }}">
                                                    <input type="text" name="review[recommended_heading]" class="form-control mb-2"
                                                           placeholder="Recommended Heading"
                                                           value="{{ old('review.recommended_heading', $tourReview?->recommended_heading) }}">
                                                    <textarea name="review[recommended_text]" class="form-control mb-2"
                                                              placeholder="Recommended Text">{{ old('review.recommended_text', $tourReview?->recommended_text) }}</textarea>
                                                </div>

                                                {{-- Badge --}}
                                                <div class="form-group form-check">
                                                    <input type="hidden" name="review[use_badge]" value="0">
                                                    <input type="checkbox" class="form-check-input" id="use_badge"
                                                           name="review[use_badge]" value="1"
                                                           {{ old('review.use_badge', $tourReview?->use_badge) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="use_badge">Enable Badge</label>
                                                </div>
                                                <div id="badge_options" class="{{ old('review.use_badge', $tourReview?->use_badge) ? '' : 'd-none' }}">
                                                    <input type="text" name="review[badge_heading]" class="form-control mb-2"
                                                           placeholder="Badge Heading"
                                                           value="{{ old('review.badge_heading', $tourReview?->badge_heading) }}">
                                                    <textarea name="review[badge_text]" class="form-control mb-2"
                                                              placeholder="Badge Text">{{ old('review.badge_text', $tourReview?->badge_text) }}</textarea>
                                                </div>

                                                {{-- Banner --}}
                                                <div class="form-group form-check">
                                                    <input type="hidden" name="review[use_banner]" value="0">
                                                    <input type="checkbox" class="form-check-input" id="use_banner"
                                                           name="review[use_banner]" value="1"
                                                           {{ old('review.use_banner', $tourReview?->use_banner) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="use_banner">Enable Banner</label>
                                                </div>
                                                <div id="banner_options" class="{{ old('review.use_banner', $tourReview?->use_banner) ? '' : 'd-none' }}">
                                                    <input type="text" name="review[banner_heading]" class="form-control mb-2"
                                                           placeholder="Banner Heading"
                                                           value="{{ old('review.banner_heading', $tourReview?->banner_heading) }}">
                                                    <textarea name="review[banner_text]" class="form-control mb-2"
                                                              placeholder="Banner Text">{{ old('review.banner_text', $tourReview?->banner_text) }}</textarea>
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
            $('#deposit_options').toggleClass('d-none', !this.checked);
        });

        // toggle minimum notice section
        $('#use_minimum_notice').on('change', function () {
            $('#minimum_notice_days').toggleClass('d-none', !this.checked);
        });

        // update unit dynamically
        // $('#tour_charge').on('change', function () {
        //     let val = $(this).val();
        //     let unit = '';
        //     if (val === 'DEPOSIT_PERCENT') unit = '%';
        //     else if (val === 'DEPOSIT_FIXED' || val === 'DEPOSIT_FIXED_PER_ORDER') unit = '$';
        //     $('#deposit_unit').text(unit);
        // }).trigger('change');
    });
</script>
<script>
$(function () {
    function toggleSection(id, target) {
        $(id).on('change', function () {
            $(target).toggleClass('d-none', !this.checked);
        }).trigger('change');
    }

    toggleSection('#use_review', '#review_options');
    toggleSection('#use_recommended', '#recommended_options');
    toggleSection('#use_badge', '#badge_options');
    toggleSection('#use_banner', '#banner_options');
});
</script>




