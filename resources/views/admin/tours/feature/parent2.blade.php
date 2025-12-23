<style>
tr:hover{cursor: pointer;}    
tr.dragging {opacity: 1;}
tr.drag-over-top {border-top: 3px solid blue;}
tr.drag-over-bottom {border-bottom: 3px solid blue;}
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
                            <a href="https://tourbeez.com/tour/{{ $data->slug }}" class="btn btn-view-tour" target="_blank">
                                {{ translate('View Tour Online') }}
                            </a>
                            <a href="{{ route('admin.tour.index') }}" class="btn btn-back">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-primary bg-white border rounded-lg-custom">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-md-10 col-12 pl-0">
                        <form id="parentTourForm"
                              class="needs-validation"
                              novalidate
                              action="{{ route('admin.tour.parent', $data->id) }}"
                              method="POST">
                            @csrf
                            @method('PUT')

                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Parent Tour</h3>
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label>
                                            Parent Tour <span class="required">*</span>
                                        </label>

                                        <select name="parent_id"
                                                class="form-control col-6 aiz-selectpicker border"
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

                                <div class="card-footer review-footer">
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
                                </div>
                            </div>

                            <input type="hidden" name="remove_parent" id="remove_parent" value="0">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LOADER --}}
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
