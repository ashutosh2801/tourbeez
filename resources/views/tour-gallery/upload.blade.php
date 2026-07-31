<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >
    <link rel="apple-touch-icon" sizes="180x180" href="/admin/dist/img/fav.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/admin/dist/img/fav.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/admin/dist/img/fav.png">
    <link rel="manifest" href="/admin/favicon/site.webmanifest">

    <title>Upload Tour Photos || TourBeez</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(1, 34, 140, 0.10), transparent 34%),
                #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
        }

        .text-left { text-align: left;}
        ul{padding: 0; margin: 0;}
        li{padding: 0; margin: 0 0 0 12px;}

        .upload-card {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border: 1px solid rgba(1, 34, 140, 0.08);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 16px 45px rgba(18, 34, 76, 0.13);
        }

        .upload-header {
            padding: 22px 24px;
            background: linear-gradient(135deg, #01228c 0%, #0645c4 100%);
            color: #ffffff;
            text-align: center;
        }

        .upload-logo {
            display: block;
            width: 180px;
            max-width: 70%;
            height: auto;
            margin: 0 auto;
        }

        .upload-body {
            padding: 28px;
        }

        .page-title {
            margin: 0 0 6px;
            font-size: 26px;
            text-align: center;
        }

        .page-subtitle {
            margin: 0 0 22px;
            color: #667085;
            font-size: 14px;
            line-height: 1.5;
            text-align: center;
        }

        .booking-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 24px;
            padding: 14px;
            background: #f5f7ff;
            border: 1px solid #dce4ff;
            border-radius: 12px;
        }

        .booking-detail {
            display: flex;
            gap: 11px;
            align-items: center;
            min-width: 0;
            padding: 12px;
            background: #fff;
            border-radius: 9px;
        }

        .booking-detail-icon {
            display: grid;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 10px;
            background: #e9efff;
            color: #01228c;
        }

        .booking-detail-icon svg {
            width: 19px;
            height: 19px;
        }

        .booking-detail-content {
            min-width: 0;
        }

        .booking-detail-label {
            display: block;
            margin-bottom: 4px;
            color: #667085;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .booking-detail-value {
            display: block;
            overflow-wrap: anywhere;
            color: #172033;
            font-size: 15px;
            font-weight: 700;
        }

        .upload-area {
            padding: 25px;
            border: 2px dashed #b6c0d6;
            border-radius: 10px;
            text-align: center;
            background: #fafbff;
        }

        .upload-area input {
            width: 100%;
        }

        .preview-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 18px;
        }

        .preview-container img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border: 1px solid #dddddd;
            border-radius: 8px;
        }

        .upload-button {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            border: 0;
            border-radius: 7px;
            background: #01228c;
            color: #ffffff;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }

        .upload-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .message {
            display: none;
            margin-bottom: 18px;
            padding: 13px;
            border-radius: 7px;
        }

        .message-success {
            color: #146c43;
            background: #d1e7dd;
        }

        .message-error {
            color: #842029;
            background: #f8d7da;
        }

    .upload-area {
    padding: 35px 25px;
    border: 2px dashed #c9d7ff;
    border-radius: 14px;
    text-align: center;
    background: linear-gradient(
        180deg,
        #f8faff 0%,
        #eef4ff 100%
    );
    transition: all .3s ease;
}

.upload-area:hover {
    border-color: #01228c;
}

.upload-icon {
    font-size: 55px;
    color: #01228c;
    margin-bottom: 15px;
}

.upload-title {
    margin: 0;
    color: #01228c;
    font-size: 22px;
}

.upload-subtitle {
    margin: 12px 0 20px;
    color: #666;
    line-height: 1.6;
}

.custom-upload-btn {
    display: inline-block;
    padding: 12px 24px;
    background: #01228c;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: .3s;
}

.custom-upload-btn:hover {
    background: #001a6d;
}

.upload-note {
    margin-top: 15px;
    font-size: 13px;
    color: #777;
}

.preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(120px,1fr));
    gap: 10px;
    margin-top: 20px;
}

.preview-container img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #ddd;
}
.swal-deny-btn {
    background: #ffffff !important;
    color: #333333 !important;
    border: 0px solid #dcdcdc !important;
}

.swal-deny-btn:hover {
    background: #f5f5f5 !important;
}

@media (max-width: 560px) {
    body { padding: 12px; }
    .upload-card { margin: 12px auto; }
    .upload-body { padding: 22px 18px; }
    .booking-info { grid-template-columns: 1fr; }
}
</style>
</head>

<body>

<div class="upload-card">

    <div class="upload-header">
        <img
            class="upload-logo"
            src="{{ asset('admin/dist/img/tourbeez-logo-white.png') }}"
            alt="TourBeez"
        >
    </div>

    <div class="upload-body">

        <h1 class="page-title">Upload Tour Photos</h1>
        <p class="page-subtitle">Share your favorite moments from your TourBeez experience.</p>
        <div
            id="uploadMessage"
            class="message"
        ></div>

        <div class="booking-info">
            <div class="booking-detail">
                <span class="booking-detail-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 7h16v13H4z"></path>
                        <path d="M8 4v6M16 4v6M4 11h16"></path>
                    </svg>
                </span>
                <span class="booking-detail-content">
                    <span class="booking-detail-label">Booking Number</span>
                    <span class="booking-detail-value">#{{ $order->order_number }}</span>
                </span>
            </div>

            <div class="booking-detail">
                <span class="booking-detail-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 21a8 8 0 0 1 16 0"></path>
                    </svg>
                </span>
                <span class="booking-detail-content">
                    <span class="booking-detail-label">Passenger</span>
                    <span class="booking-detail-value">{{ $order->customer?->name ?? 'Guest' }}</span>
                </span>
            </div>
        </div>

        <form
            id="tourGalleryUploadForm"
            method="POST"
            action="{{ request()->fullUrl() }}"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="upload-area">

    <div class="upload-icon">
        <i class="fas fa-cloud-upload-alt"></i>
    </div>

    <h3 class="upload-title">
        Share Your Tour Memories
    </h3>

    <p class="upload-subtitle">
        Upload your favorite photos.
        You can select multiple photos at once.
    </p>

    <label for="galleryPhotos" class="custom-upload-btn">
        <i class="fas fa-camera"></i>
        Choose Photos
    </label>

    <input
        type="file"
        name="photos[]"
        id="galleryPhotos"
        accept="image/*"
        multiple
        required
        hidden
    >

    <div class="upload-note">
        JPG, PNG, WEBP • Maximum 10 photos • 10MB each
    </div>

</div>

<div
    class="preview-container"
    id="photoPreview"
></div>

            <button
                type="submit"
                class="upload-button"
                id="uploadButton"
                disabled
            >
                Upload Photos
            </button>
        </form>

    </div>
</div>

<script>
    const uploadForm = document.getElementById(
        'tourGalleryUploadForm'
    );

    const photoInput = document.getElementById(
        'galleryPhotos'
    );

    const previewContainer = document.getElementById(
        'photoPreview'
    );

    const uploadButton = document.getElementById(
        'uploadButton'
    );

    const messageBox = document.getElementById(
        'uploadMessage'
    );

    function showMessage(message, type) {
        messageBox.className =
            'message message-' + type;

        messageBox.innerHTML = message;
        messageBox.style.display = 'block';
    }

    photoInput.addEventListener('change', function () {
        previewContainer.innerHTML = '';
        uploadButton.disabled = false;

        const files = Array
            .from(this.files)
            .slice(0, 10);

        files.forEach(function (file) {
            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                const image = document.createElement('img');

                image.src = event.target.result;
                image.alt = file.name;

                previewContainer.appendChild(image);
            };

            reader.readAsDataURL(file);
        });
    });

    uploadForm.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            if (!photoInput.files.length) {
                showMessage(
                    'Please select at least one photo.',
                    'error'
                );

                return;
            }

            uploadButton.disabled = true;
            uploadButton.textContent = 'Uploading...';
            messageBox.style.display = 'none';

            try {
                const formData = new FormData(uploadForm);

                const response = await fetch(
                    uploadForm.action,
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: formData
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.status) {
                    let message =
                        result.message ||
                        'Photos could not be uploaded.';

                    if (result.errors) {
                        message = Object
                            .values(result.errors)
                            .flat()
                            .join('<br>');
                    }

                    throw new Error(message);
                }

                showMessage(
                    result.message,
                    'success'
                );

                uploadForm.reset();
                previewContainer.innerHTML = '';

                uploadButton.disabled = true;
            } catch (error) {
                showMessage(
                    error.message ||
                    'Photos could not be uploaded.',
                    'error'
                );
            } finally {
                    

                uploadButton.textContent = 'Upload Photos';
            }
        }
    );
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    Swal.fire({
        title: 'Photo Upload Consent',
        customClass: {
            title: 'text-left',
            htmlContainer: 'text-left',
            denyButton: 'swal-deny-btn'
        },
        width:600,
        html: `<div style="
                background:#e8f4fd;
                border-left:4px solid #01228c;
                padding:12px;
                margin-bottom:15px;
                border-radius:4px; line-height:1.6rem
            ">
                Thank you for traveling with TourBeez! We would love to feature your photos from this tour. Please review and accept the consent below before uploading.
            </div>
            <div style="text-align:left;">
                <p>
                    By uploading photos, you agree that:
                </p>

                <ul style="text-align:left;line-height:1.8;">
                    <li>You own the photos or have permission to share them.</li>
                    <li>TourBeez may use them for marketing and promotional purposes.</li>
                    <li>No compensation will be provided.</li>
                    <li>Photos may be reviewed before publication.</li>
                </ul>
            </div>
        `,
        // icon: 'info',
        confirmButtonText: 'Yes, I Understand',
        allowOutsideClick: false,
        allowEscapeKey: false,
        stopKeydownPropagation: false,

        showDenyButton: true,
        denyButtonText: 'I Do Not Agree',

    }).then((result) => {

      if (result.isConfirmed) {
          //document.getElementById('uploadCard').style.display = 'block';
      } else {
          window.location.href = 'https://tourbeez.com';
      }

  });

});
</script>
</body>
</html>
