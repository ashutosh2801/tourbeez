<style>
tr:hover{cursor: pointer;}    
tr.dragging {opacity: 1;}
tr.drag-over-top {border-top: 3px solid blue;}
tr.drag-over-bottom {border-bottom: 3px solid blue;}
</style>

<x-admin>
    @section('title','Edit Tour')

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
            <div class="card-primary bg-white border rounded-lg-custom">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-2 pr-0 desktop-menu">
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
                        <div class="col-md-10 col-12 pl-0">
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="taxes_nd_fees" role="tabpanel" aria-labelledby="v-pills-messages-tab-10">
                                <form class="needs-validation" novalidate action="{{ route('admin.tour.review', $data->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="review-body">
                                        <div class="card card-primary">
                                            <div class="card-header">
                                                <h3 class="card-title">Review & Highlights</h3>
                                            </div>

                                            <div class="card-body">

                                                {{-- ✅ Review Section --}}
                                                <div class="form-group form-check mb-3">
                                                    <input type="hidden" name="review[use_review]" value="0">
                                                    <input type="checkbox" class="form-check-input" id="use_review"
                                                        name="review[use_review]" value="1"
                                                        {{ old('review.use_review', $tourReview?->use_review) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="use_review">Enable Review</label>
                                                </div>

                                                <div id="review_options" class="{{ old('review.use_review', $tourReview?->use_review) ? '' : 'd-none' }}">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <input type="text" name="review[review_heading]" class="form-control"
                                                                placeholder="Review Heading"
                                                                value="{{ old('review.review_heading', $tourReview?->review_heading) }}">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <input type="text" name="review[review_text]" class="form-control"
                                                                placeholder="Review Text"
                                                                value="{{ old('review.review_text', $tourReview?->review_text) }}">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <select name="review[review_rating]" class="form-control">
                                                                <option value="">Select Rating</option>
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <option value="{{ $i }}" {{ old('review.review_rating', $tourReview?->review_rating) == $i ? 'selected' : '' }}>
                                                                        {{ $i }}
                                                                    </option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <input type="number" name="review[review_count]" class="form-control"
                                                                placeholder="Review Count"
                                                                value="{{ old('review.review_count', $tourReview?->review_count) }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                {{-- ✅ Recommended Section --}}
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5>Recommended Sections</h5>
                                                    <button type="button" id="add-recommended" class="btn btn-success btn-sm"> + Add Recommended</button>
                                                </div>
                                                <div id="recommended-sections" class="mt-3">
                                                    @php $recommended = old('review.recommended', $tourReview?->recommended ?? []); @endphp
                                                    @foreach($recommended as $i => $rec)
                                                        <div class="recommended-item border rounded p-3 mb-2">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="text" class="form-control"
                                                                        name="review[recommended][{{ $i }}][heading]"
                                                                        value="{{ $rec['heading'] ?? '' }}"
                                                                        placeholder="Recommended Heading">
                                                                </div>
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="text" class="form-control"
                                                                        name="review[recommended][{{ $i }}][text]"
                                                                        value="{{ $rec['text'] ?? '' }}"
                                                                        placeholder="Recommended Text">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-danger btn-sm remove-recommended">Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <hr>

                                                {{-- ✅ Badge Section --}}
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5>Badges</h5>
                                                    <button type="button" id="add-badges" class="btn btn-success btn-sm"> + Add Badge</button>
                                                </div>
                                                <div id="badges-sections" class="mt-3">
                                                    @php $badges = old('review.badges', $tourReview?->badges ?? []); @endphp
                                                    @foreach($badges as $i => $badge)
                                                        <div class="badges-item border rounded p-3 mb-2">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="text" class="form-control"
                                                                        name="review[badges][{{ $i }}][heading]"
                                                                        value="{{ $badge['heading'] ?? '' }}"
                                                                        placeholder="Badge Heading">
                                                                </div>
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="text" class="form-control"
                                                                        name="review[badges][{{ $i }}][text]"
                                                                        value="{{ $badge['text'] ?? '' }}"
                                                                        placeholder="Badge Text">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-danger btn-sm remove-badges">Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <hr>

                                                {{-- ✅ Banner Section --}}
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5>Banners</h5>
                                                    <button type="button" id="add-banners" class="btn btn-success btn-sm">+ Add Banner</button>
                                                </div>
                                                <div id="banners-sections" class="mt-3">
                                                    @php $banners = old('review.banners', $tourReview?->banners ?? []); @endphp
                                                    @foreach($banners as $i => $banner)
                                                        <div class="banners-item border rounded p-3 mb-2">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="text" class="form-control"
                                                                        name="review[banners][{{ $i }}][heading]"
                                                                        value="{{ $banner['heading'] ?? '' }}"
                                                                        placeholder="Banner Heading">
                                                                </div>
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="text" class="form-control"
                                                                        name="review[banners][{{ $i }}][text]"
                                                                        value="{{ $banner['text'] ?? '' }}"
                                                                        placeholder="Banner Text">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-danger btn-sm remove-banners">Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>

                                            </div>

                                            <div class="card-footer review-footer" style="display:block">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                                                    </div>
                                                    <div class="col-md-6 align-buttons">
                                                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}" class="btn btn-secondary"> <i class="fas fa-chevron-left"></i> Back</a>
                                                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}" class="btn btn-secondary">Next <i class="fas fa-chevron-right"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle Review Section visibility
    const useReview = document.getElementById('use_review');
    if (useReview) {
        useReview.addEventListener('change', function () {
            document.getElementById('review_options').classList.toggle('d-none', !this.checked);
        });
    }

    // Initialize indexes based on existing count
    let recIndex = {{ count($recommended ?? []) }};
    let badgeIndex = {{ count($badges ?? []) }};
    let bannerIndex = {{ count($banners ?? []) }};

    // Add Recommended
    document.getElementById('add-recommended').addEventListener('click', function () {
        const container = document.getElementById('recommended-sections');
        const html = `
            <div class="recommended-item border rounded p-3 mb-2">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="review[recommended][${recIndex}][heading]" placeholder="Recommended Heading">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="review[recommended][${recIndex}][text]" placeholder="Recommended Text">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-recommended">Remove</button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        recIndex++;
    });

    // Add Badges
    document.getElementById('add-badges').addEventListener('click', function () {
        const container = document.getElementById('badges-sections');
        const html = `
            <div class="badges-item border rounded p-3 mb-2">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="review[badges][${badgeIndex}][heading]" placeholder="Badge Heading">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="review[badges][${badgeIndex}][text]" placeholder="Badge Text">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-badges">Remove</button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        badgeIndex++;
    });

    // Add Banners
    document.getElementById('add-banners').addEventListener('click', function () {
        const container = document.getElementById('banners-sections');
        const html = `
            <div class="banners-item border rounded p-3 mb-2">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="review[banners][${bannerIndex}][heading]" placeholder="Banner Heading">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" name="review[banners][${bannerIndex}][text]" placeholder="Banner Text">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-banners">Remove</button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        bannerIndex++;
    });

    // Universal Remove (delegated)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-recommended')) {
            e.target.closest('.recommended-item').remove();
        }
        if (e.target.classList.contains('remove-badges')) {
            e.target.closest('.badges-item').remove();
        }
        if (e.target.classList.contains('remove-banners')) {
            e.target.closest('.banners-item').remove();
        }
    });
});
</script>





