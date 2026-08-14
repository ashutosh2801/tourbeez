<x-admin>

  @section('title', 'Tour Gallery')

  

  <div class="card card-primary">

    {{-- Filters --}}
    <div class="card-body customer-head mb-4">

        <form
            method="GET"
            action="{{ route('admin.tour-gallery.index') }}"
        >
            <div class="row">

                <div class="col-md-4 mb-3">
                    <!-- <label class="form-label">
                        Search Booking
                    </label> -->

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Booking number or order ID"
                    >
                </div>

                <!-- <div class="col-md-3 mb-3">
                    <label class="form-label">
                        Tour ID
                    </label>

                    <input
                        type="number"
                        name="tour_id"
                        class="form-control"
                        value="{{ request('tour_id') }}"
                        placeholder="Tour ID"
                    >
                </div> -->

                <div class="col-md-3 mb-3">
                    <!-- <label class="form-label">
                        Status
                    </label> -->

                    <select
                        name="status"
                        class="form-control"
                    >
                        <option value="">
                            All Photos
                        </option>

                        <option
                            value="pending"
                            @selected(request('status') === 'pending')
                        >
                            Pending
                        </option>

                        <option
                            value="approved"
                            @selected(request('status') === 'approved')
                        >
                            Approved
                        </option>
                    </select>
                </div>

                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Filter
                    </button>
                </div>

                <div class="col-md-3 mb-3 d-flex align-items-end">
                  @if(
                      request()->filled('search') ||
                      request()->filled('tour_id') ||
                      request()->filled('status')
                  )
                  <a
                      href="{{ route('admin.tour-gallery.index') }}"
                      class="btn btn-light"
                  >
                      Clear Filters
                  </a>
                  @endif
                </div>

            </div>

            

        </form>

    </div>

    <div class="card-body p-4">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- Gallery --}}
    @if($galleryUploads->count())

        <div class="row">

            @foreach($galleryUploads as $galleryPhoto)

                @php
                    $upload = $galleryPhoto->upload;
                    $isYoutube = $upload?->type === 'youtube';

                    if ($isYoutube) {
                        $imageUrl = $upload->medium_name
                            ?: $upload->thumb_name
                            ?: asset('admin/dist/img/no-image.png');
                        $mediaUrl = $upload->file_original_name
                            ?: 'https://www.youtube.com/watch?v=' . $upload->file_name;
                    } else {
                        $imagePath = $upload?->medium_name ?: $upload?->file_name;
                        if (
                            $imagePath && (
                                config('filesystems.default') === 's3' ||
                                env('FILESYSTEM_DRIVER') === 's3'
                            )
                        ) {
                            $imageUrl = Storage::disk('s3')->url(
                                $imagePath
                            );
                        } elseif ($imagePath) {
                            $imageUrl = asset($imagePath);
                        } else {
                            $imageUrl = asset('admin/dist/img/no-image.png');
                        }
                        $mediaUrl = $imageUrl;
                    }
                @endphp

                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">

                    <div class="card gallery-card h-100">

                        <div class="gallery-image-wrapper">

                            <a
                                href="{{ $mediaUrl }}"
                                target="_blank"
                            >
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $isYoutube ? 'Tour video' : ($upload?->file_original_name ?? 'Tour photo') }}"
                                    class="gallery-image"
                                >
                            </a>

                            <span class="
                                gallery-status
                                {{ $galleryPhoto->is_approved
                                    ? 'status-approved'
                                    : 'status-pending'
                                }}
                            ">
                                {{ $galleryPhoto->is_approved
                                    ? 'Approved'
                                    : 'Pending'
                                }}
                            </span>

                        </div>

                        <div class="card-body">

                            <h5 class="card-title1 mb-2">                                
                                <strong>Booking:</strong>

                                #{{ $galleryPhoto->order?->order_number
                                    ?? $galleryPhoto->order_id
                                }}
                            </h5>

                            <p class="mb-1">
                                <strong>Passenger:</strong>

                                {{ $galleryPhoto->order?->customer?->name
                                    ?? 'Guest'
                                }}
                            </p>

                            <!-- <p class="mb-1">
                                <strong>Tour ID:</strong>
                                {{ $galleryPhoto->tour_id }}
                            </p>

                            <p class="mb-1">
                                <strong>Order ID:</strong>
                                {{ $galleryPhoto->order_id }}
                            </p>

                            <p class="mb-1">
                                <strong>Uploaded by:</strong>
                                {{ ucfirst(
                                    $galleryPhoto->uploaded_by_type
                                ) }}
                            </p> -->

                            <p class="mb-0 text-muted small">
                                {{ optional(
                                    $galleryPhoto->created_at
                                )->format('M d, Y h:i A') }}
                            </p>

                        </div>

                        <div class="card-footer bg-white">

                            <div class="d-flex flex-wrap gap-2">

                                @if(!$galleryPhoto->is_approved)

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.tour-gallery.approve',
                                            $galleryPhoto
                                        ) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-success btn-sm"
                                        >
                                            Approve
                                        </button>
                                    </form>

                                @else

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.tour-gallery.reject',
                                            $galleryPhoto
                                        ) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-warning btn-sm"
                                        >
                                            Move to Pending
                                        </button>
                                    </form>

                                @endif

                                <a
                                    href="{{ $mediaUrl }}"
                                    target="_blank"
                                    class="btn btn-info btn-sm"
                                >
                                    {{ $isYoutube ? 'Watch Video' : 'View Photo' }}
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.tour-gallery.destroy',
                                        $galleryPhoto
                                    ) }}"
                                    class="delete-gallery-form"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                    >
                                        Delete
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        <div class="mt-3">
            {{ $galleryUploads->links() }}
        </div>

    @else

        <div class="card">
            <div class="card-body text-center py-5">

                <h4>No photos found</h4>

                <p class="text-muted mb-0">
                    Passenger-uploaded tour photos will appear here.
                </p>

            </div>
        </div>

    @endif

    </div>

  </div>


@section('css')
<style>
    .gallery-card {
        overflow: hidden;
        border: 1px solid #e3e6f0;
        border-radius: 10px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .gallery-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .1);
    }

    .gallery-image-wrapper {
        position: relative;
        height: 230px;
        overflow: hidden;
        background: #f4f6f9;
    }

    .gallery-image {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        transition: transform .25s ease;
    }

    .gallery-image-wrapper:hover .gallery-image {
        transform: scale(1.04);
    }

    .gallery-status {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 6px 10px;
        border-radius: 20px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
    }

    .status-approved {
        background: #198754;
    }

    .status-pending {
        background: #f0ad4e;
    }

    .gap-2 {
        gap: 8px;
    }
</style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.querySelectorAll(
        '.delete-gallery-form'
    ).forEach(function (form) {

        form.addEventListener(
            'submit',
            function (event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Delete this photo?',
                    text: 'The image and its resized versions will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc3545'
                }).then(function (result) {

                    if (result.isConfirmed) {
                        form.submit();
                    }

                });
            }
        );

    });
</script>
@endsection

</x-admin>
