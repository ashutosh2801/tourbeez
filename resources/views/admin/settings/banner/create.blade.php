<x-admin>
    @section('title', isset($banner) ? 'Edit Banner' : 'Create Banner')

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ isset($banner) ? 'Edit Banner' : 'Create Banner' }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($banner) ? route('admin.banners.update', $banner->id) : route('admin.banners.store') }}" 
                  method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($banner))
                    @method('POST')
                @endif

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location_id" class="form-control"
                               value="{{ old('location_id', $banner->location_id ?? '') }}">
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Heading</label>
                        <input type="text" name="heading" class="form-control"
                               value="{{ old('heading', $banner->heading ?? '') }}">
                    </div>

                    <div class="col-lg-12 mb-3">
                        <label class="form-label">Sub Heading</label>
                        <input type="text" name="sub_heading" class="form-control"
                               value="{{ old('sub_heading', $banner->sub_heading ?? '') }}">
                    </div>

                    {{-- Images --}}
                    

                    <div class="col-lg-12 mb-5">
                        <label class="form-label">Banner Images</label>
                        <div class="input-group input-group-sm" data-toggle="aizuploader" data-type="image" data-multiple="true">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose Photos') }}</div>
                            <input type="hidden" name="images[]" class="selected-files" 
                                value="{{ old('images', isset($banner->images) ? implode(',', json_decode($banner->images, true)) : '') }}">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>

                    

                    {{-- Videos --}}
                    <div class="col-lg-12">
                        <div class="form-group mb-5">
                            <label for="videos" class="form-label">Videos</label>
                            <div id="videosContainer">
                                @php
                                    $videos = old('videos', isset($banner->videos) ? json_decode($banner->videos, true) : []);
                                @endphp

                                @foreach($videos as $index => $video)
                                    <div class="input-group mb-3 video-input">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">https://www.youtube.com/watch?v=</span>
                                        </div>
                                        <input type="text" name="videos[]" class="form-control" value="{{ $video }}">
                                        <button type="button" class="btn btn-sm btn-primary mr-2" onclick="previewVideo('{{ $video }}')">Preview</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeVideo(this)"><i class="fa fa-minus"></i></button>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="addVideos()">
                                <i class="fa fa-plus"></i> Add Video
                            </button>
                        </div>
                    </div>

                    {{-- Modal for Video Preview --}}
                    <div class="modal fade" id="videoPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Video Preview</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="stopPreview()">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body text-center">
                            <iframe id="videoPreviewFrame" width="100%" height="400" frameborder="0" allowfullscreen></iframe>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($banner) ? 'Update' : 'Create' }}</button>
            </form>
        </div>
    </div>

    @section('js')
    <script>
        // Preview Images
        function previewImages(event) {
            let preview = document.getElementById('imagePreview');
            preview.innerHTML = "";
            Array.from(event.target.files).forEach(file => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let img = document.createElement("img");
                    img.src = e.target.result;
                    img.className = "rounded border mr-2 mb-2";
                    img.width = 100;
                    img.height = 100;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        // Add Video Input
        @section('js')
<script>
    // Add Video Input
    function addVideos() {
        let html = `
            <div class="input-group mb-3 video-input">
                <div class="input-group-prepend">
                    <span class="input-group-text">https://www.youtube.com/watch?v=</span>
                </div>
                <input type="text" name="videos[]" class="form-control">
                <button type="button" class="btn btn-sm btn-primary mr-2" onclick="previewVideo('')">Preview</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeVideo(this)"><i class="fa fa-minus"></i></button>
            </div>`;
        document.getElementById('videosContainer').insertAdjacentHTML('beforeend', html);
    }

    // Remove Video Input
    function removeVideo(btn) {
        btn.closest('.video-input').remove();
    }

    // Preview Video
    function previewVideo(videoId) {
        let frame = document.getElementById('videoPreviewFrame');
        if (!videoId) {
            // Get input value if empty in onclick
            let input = event.target.closest('.video-input').querySelector('input').value;
            videoId = input;
        }
        if (videoId) {
            frame.src = "https://www.youtube.com/embed/" + videoId;
            $('#videoPreviewModal').modal('show');
        } else {
            alert("Please enter a valid YouTube video ID");
        }
    }

    // Stop Preview when modal closes
    function stopPreview() {
        document.getElementById('videoPreviewFrame').src = "";
    }
</script>
@endsection

    </script>



    @endsection
</x-admin>
