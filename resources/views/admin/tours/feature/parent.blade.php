<style>
tr:hover{cursor: pointer;}    
tr.dragging {opacity: 1;}
tr.drag-over-top {border-top: 3px solid blue;}
tr.drag-over-bottom {border-bottom: 3px solid blue;}

/* Wrap selected text */
.bootstrap-select .dropdown-toggle .filter-option-inner-inner {
    white-space: normal !important;
    line-height: 1.3;
}

/* Wrap dropdown options */
.bootstrap-select .dropdown-menu .dropdown-item,
.bootstrap-select .dropdown-menu li a {
    white-space: normal !important;
    word-break: break-word;
}

/* Optional: better spacing for long titles */
.bootstrap-select .dropdown-menu li a {
    padding-top: 8px;
    padding-bottom: 8px;
}
/* Force wrapping inside select */
.select option {
    white-space: normal;
}

/* For Bootstrap / aiz-selectpicker */
.bootstrap-select .dropdown-menu li a span.text {
    white-space: normal !important;
    word-wrap: break-word;
}
</style>

<x-admin>
    @section('title','Edit Parent')

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
                                    @if(!$data->parent_id)
                                        <a href="https://tourbeez.com/tour/{{ $data->slug }}" class="btn btn-view-tour" target="_blank">{{translate('View Tour Online')}}</a>
                                    @endif
                                    <a href="{{ route('admin.tour.index') }}" class="btn btn-back">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- mobile menu start -->
                <div class="dropdown tour-mb-dropdown">
                    <div class="form-control" data-toggle="dropdown" href="#" aria-expanded="false">
                        - Select Menu -
                    </div>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right profile-dropdown">
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
                        <a class="nav-link" href="{{ route('admin.tour.edit.partner', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Partner')}}</a>                               
                        <a class="nav-link" href="{{ route('admin.tour.edit.seo', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('SEO')}}</a> 
                        <a class="nav-link" href="{{ route('admin.tour.edit.special.deposit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate(' Special Deposit')}}</a>
                        <a class="nav-link" href="{{ route('admin.tour.edit.review', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Review')}}</a>
                        <a class="nav-link" href="{{ route('admin.tour.edit.parent', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Parent Tour')}}</a>
                    </div>
                </div>
                <!-- mobile menu end -->
                <div class="card-primary bg-white border rounded-lg-custom">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-xl-2 col-lg-3 pr-0 desktop-menu">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    @if($data->parent_id)
                                        <a class="nav-link" href="{{ route('admin.tour.sub-tour.edit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Basic Details')}}</a>

                                        <a class="nav-link" href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" ><i class="fas fa-caret-right"></i> {{translate('Extra')}}</a>
                                        
                                        <a class="nav-link" href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Scheduling')}}</a>
                                        <a class="nav-link active" href="{{ route('admin.tour.edit.parent', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Parent Tour')}}</a>
                                        <a class="nav-link" href="{{ route('admin.tour.edit.special.deposit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate(' Special Deposit')}}</a> 
                                    @else
                                        <a class="nav-link" href="{{ route('admin.tour.edit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Basic Details')}}</a>
                                        <a class="nav-link" href="{{ route('admin.tour.edit.addone', encrypt($data->id)) }}" ><i class="fas fa-caret-right"></i> {{translate('Extra')}}</a>
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
                                        <a class="nav-link" href="{{ route('admin.tour.edit.booking', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Booking Info')}}</a>
                                        <a class="nav-link" href="{{ route('admin.tour.edit.partner', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Partner')}}</a>
                                        <a class="nav-link" href="{{ route('admin.tour.edit.seo', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('SEO')}}</a> 
                                        <a class="nav-link " href="{{ route('admin.tour.edit.special.deposit', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate(' Special Deposit')}}</a> 
                                        <a class="nav-link " href="{{ route('admin.tour.edit.review', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Review')}}</a>
                                        <a class="nav-link active" href="{{ route('admin.tour.edit.parent', encrypt($data->id)) }}"><i class="fas fa-caret-right"></i> {{translate('Parent Tour')}}</a> 
                                    @endif                               
                                </div>
                            </div>
                            <div class="col-xl-10 col-lg-9 col-12 pl-0">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <div class="tab-pane fade show active" id="taxes_nd_fees" role="tabpanel" aria-labelledby="v-pills-messages-tab-10">
                                        <div class="card itinerary-body">
                                            <form id="parentTourForm" class="needs-validation" novalidate action="{{ route('admin.tour.parent', $data->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="card card-primary">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Parent Tour</h3>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <label>
                                                                        Parent Tour <span class="required">*</span>
                                                                    </label>
                                                                </div>
                                                                <div class="col-sm-12">
                                                                    <select name="parent_id"
                                                                            class="form-control aiz-selectpicker border"
                                                                            data-live-search="true">
                                                                        <option value="">Select tour...</option>

                                                                        @foreach($tours as $tour)
                                                                            <option value="{{ $tour->id }}"
                                                                                {{ $data->parent_id == $tour->id ? 'selected' : '' }}>
                                                                                {{ $tour->parent ? $tour->parent->title.' → ' : '' }}
                                                                                {{ $tour->title }} ({{ $tour->unique_code }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card-footer" style="display:block">
                                                        <div class="row">
                                                            <div class="col-md-6 order-2 order-md-1">
                                                                <!-- <a href="{{ route('admin.orders.index') }}" class="btn btn-cancel"> <i class="fas fa-times"></i> Cancel</a> -->
                                                                <!-- <a onclick="return confirm('Are you sure?')" href="javascript:void(0)" class="btn btn-danger confirm-delete"> <i class="fas fa-trash-alt"></i> Delete</a> -->

                                                                <!-- <a href="javascript:void(0)"
                                                                    data-url="#"
                                                                    class="btn btn-danger btn-delete-order">
                                                                    <i class="fas fa-trash-alt"></i> Delete
                                                                </a> -->

                                                                @if($data->parent_id)
                                                                    <a href="javascript:void(0)"
                                                                    data-url="#" type="button"
                                                                            id="removeParentBtn"
                                                                            class="btn btn-danger"
                                                                            style="padding:0.6rem 2rem">
                                                                        <i class="fas fa-unlink"></i> Remove Parent
                                                                    </a>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-6 align-buttons order-1 order-md-2">
                                                                <button type="button" id="saveParentBtn" class="btn btn-success" style="padding:0.6rem 2rem"><i class="fas fa-save"></i> Save </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- <div class="card-footer review-footer">
                                                        <div class="row">
                                                            <div class="col-md-6 d-flex gap-2">

                                                                <button type="button"
                                                                        id="saveParentBtn"
                                                                        class="btn btn-success"
                                                                        style="padding:0.6rem 2rem">
                                                                    <i class="fas fa-save"></i> Save
                                                                </button>

                                                                @if($data->parent_id)
                                                                    <button type="button"
                                                                            id="removeParentBtn"
                                                                            class="btn btn-danger"
                                                                            style="padding:0.6rem 2rem">
                                                                        <i class="fas fa-unlink"></i> Remove Parent
                                                                    </button>
                                                                @endif

                                                            </div>

                                                            <div class="col-md-6 text-end">
                                                                <a class="btn btn-secondary"
                                                                style="padding:0.6rem 2rem"
                                                                href="{{ route('admin.tour.edit.scheduling', encrypt($data->id)) }}">
                                                                    <i class="fas fa-chevron-left"></i> Back
                                                                </a>

                                                                <a class="btn btn-secondary"
                                                                style="padding:0.6rem 2rem"
                                                                href="{{ route('admin.tour.edit.pickups', encrypt($data->id)) }}">
                                                                    Next <i class="fas fa-chevron-right"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                </div>

                                                <input type="hidden" name="remove_parent" id="remove_parent" value="0">
                                            </form>
                                            <div id="formLoader" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:9999; align-items:center; justify-content:center;">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="sr-only">Loading...</span>
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
    

<div id="formLoader"
     style="display:none;
            position:fixed;
            inset:0;
            background:rgba(255,255,255,0.7);
            z-index:9999;
            align-items:center;
            justify-content:center;">
    <div class="spinner-border text-primary"></div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const form   = document.getElementById('parentTourForm');
    const loader = document.getElementById('formLoader');

    function submitForm() {
        loader.style.display = 'flex';
        HTMLFormElement.prototype.submit.call(form);
    }

    document.getElementById('saveParentBtn').addEventListener('click', function () {
        document.getElementById('remove_parent').value = 0;

        Swal.fire({
            title: 'Confirm Update',
            text: 'Do you want to update the parent tour?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save'
        }).then(res => {
            if (res.isConfirmed) submitForm();
        });
    });

    const removeBtn = document.getElementById('removeParentBtn');
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            document.getElementById('remove_parent').value = 1;

            Swal.fire({
                title: 'Remove Parent?',
                text: 'This will unlink the parent tour.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Remove',
                confirmButtonColor: '#d33'
            }).then(res => {
                if (res.isConfirmed) submitForm();
            });
        });
    }
</script>
@endsection
</x-admin>





