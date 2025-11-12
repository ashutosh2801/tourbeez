<div class="card">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Gallery</h3>            
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
        <form class="needs-validation" novalidate action="{{ route('admin.tour.gallery_update', $data->id) }}" method="POST"
            enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="card-body">
                <div class="row" id="GalleryContainer">
                    @php $i = 1; @endphp
                    @foreach ($data->galleries as $image)
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label">{{ $i++ }} Image</label>
                            <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                                <!-- <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{translate('Choose Photo')}}</div> -->
                                <input type="hidden" name="gallery[]" class="selected-files" value="{{ $image->id }}">
                            </div>
                            <div class="file-preview box md"></div>
                        </div>
                    </div>    
                    @endforeach
                    
                </div>

                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="addGallery()"><i class="fa fa-plus"></i> Add</button>
                </div>

            </div>

            
            <div class="card-footer" style="display:block">
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.taxesfees', encrypt($data->id)) }}" class="btn btn-secondary">Back</a>
                <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success">Save</button>
                <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.message.notification', encrypt($data->id)) }}" class="btn btn-primary">Next</a>           
            </div>
            
        </form>
    </div>
</div>

@section('js')
@parent
<script>
let GalleryCount = {{ $i ? $i : 1 }};

function addGallery() {
    const container = document.getElementById('GalleryContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('col-lg-3');
    newRow.setAttribute('id', `GalleryRow_${GalleryCount}`);

    newRow.innerHTML = `
            <div class="form-group">
                <label class="form-label">${GalleryCount} Image</label>
                <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{translate('Browse')}}</div>
                    </div>
                    <div class="form-control file-amount">{{translate('Choose Photo')}}</div>
                    <input type="hidden" name="gallery[]" class="selected-files">
                </div>
                <div class="file-preview box lg"></div>
            </div>`;

    container.appendChild(newRow);
    GalleryCount++;
}

function removePriceOption(id) {
    const row = document.getElementById(`GalleryRow_${id}`);
    if (row) {
        row.remove();
        GalleryCount--;
    }
}
</script>
@endsection