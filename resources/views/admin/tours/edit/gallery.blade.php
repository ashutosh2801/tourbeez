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
                     <div class="col-lg-3 gallery-item" data-id="{{ $image->id }}">
    <div class="form-group">

        <div class="d-flex justify-content-between align-items-center mb-1">
            
            <label class="form-label mb-0">
                {{ $i++ }} Image
            </label>

            <input type="radio" name="main_image"
                value="{{ $image->id }}"
                {{ $image->pivot->is_main ? 'checked' : '' }}>
            <!-- <small>Main</small> -->
        </div>

        <input type="hidden" name="order[]" value="{{ $image->id }}">

        <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image">

            @if($image->type == 'youtube')
                <img src="{{ $image->thumb_name }}" class="img-fluid mb-2">
                
                <button type="button"
                    class="btn btn-sm btn-primary mt-1"
                    onclick="previewVideo('{{ $image->file_name }}')">
                    Preview
                </button>

            @else
                <input type="hidden" name="gallery[]" class="selected-files"
                    value="{{ $image->id }}">
            @endif

        </div>

        <div class="file-preview box md"></div>

        <button type="button" class="btn btn-sm btn-danger mt-2 remove-item">
            Remove
        </button>

    </div>
</div>  

                    @endforeach
                    
                </div>

                <div class="text-left">
                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="addGallery()" style="padding: 9px 30px;"><i class="fa fa-plus"></i> Add Image</button>

                    <button type="button" class="btn btn-sm btn-primary mr-2" onclick="addVideo()" style="padding: 9px 30px;">
                    <i class="fa fa-plus"></i> Add Video
                </button>
                </div>

            </div>

            <div class="card-footer" style="display:block">
                <div class="row">
                    <div class="col-md-6">
                        <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                    </div>
                    <div class="col-md-6 align-buttons">
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.taxesfees', encrypt($data->id)) }}" class="btn btn-secondary"> <i class="fas fa-chevron-left"></i> Back</a>
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.message.notification', encrypt($data->id)) }}" class="btn btn-secondary">Next <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
            
        </form>
    </div>
</div>

@section('js')
@parent
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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

function addVideo() {
    const container = document.getElementById('GalleryContainer');

    const newRow = document.createElement('div');
    newRow.classList.add('col-lg-3');

    newRow.innerHTML = `
        <div class="form-group">
            <label class="form-label">Video</label>

            <input type="text" name="video_urls[]" 
                   class="form-control mb-2 video-input"
                   placeholder="Paste YouTube link">

            <img src="" class="img-fluid mb-2 video-thumb" style="display:none;">

            <button type="button" class="btn btn-sm btn-primary"
                onclick="previewVideoFromInput(this)">Preview</button>
        </div>
    `;

    container.appendChild(newRow);
}

function previewVideoFromInput(btn) {

    let parent = btn.closest('.form-group');
    let input = parent.querySelector('.video-input');
    let img = parent.querySelector('.video-thumb');

    let videoId = extractYoutubeId(input.value);

    if (!videoId) {
        alert('Invalid YouTube URL');
        return;
    }

    img.src = `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`;
    img.style.display = 'block';

    console.log(videoId);
}

// function extractYoutubeId(url) {

//     if (!url) return '';

//     // youtu.be links
//     let match = url.match(/youtu\.be\/([^?&]+)/);
//     if (match) {
//         return match[1];
//     }

//     // youtube.com/watch?v=
//     match = url.match(/[?&]v=([^?&]+)/);
//     if (match) {
//         return match[1];
//     }

//     // already a video id
//     return url;
// }

function extractYoutubeId(url) {
    if (!url) return '';

    let match = url.match(/(?:youtu\.be\/|youtube\.com\/watch\?v=)([^?&]+)/);
    return match ? match[1] : '';
}
const sortable = new Sortable(document.getElementById('GalleryContainer'), {
    animation: 150,
    ghostClass: 'bg-light',

    onEnd: function () {
        updateOrderInputs();
    }
});

function updateOrderInputs() {
    let container = document.getElementById('GalleryContainer');
    let items = container.querySelectorAll('.gallery-item');

    // remove old inputs
    document.querySelectorAll('input[name="order[]"]').forEach(e => e.remove());

    items.forEach(item => {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order[]';
        input.value = item.getAttribute('data-id');
        container.appendChild(input);
    });
}

document.addEventListener('click', function (e) {

    // REMOVE ITEM (IMAGE OR VIDEO)
    if (e.target.closest('.remove-item')) {

        const item = e.target.closest('.gallery-item') 
                  || e.target.closest('.col-lg-3');

        if (item) {
            item.remove();
            updateOrderInputs();
        }
    }
});
</script>
@endsection