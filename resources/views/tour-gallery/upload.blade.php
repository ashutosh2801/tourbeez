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
            background: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
        }

        .text-left { text-align: left;}
        ul{padding: 0; margin: 0;}
        li{padding: 0; margin: 0 0 0 12px;}

        .upload-card {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        .upload-header {
            padding: 24px;
            background: #01228c;
            color: #ffffff;
            text-align: center;
        }

        .upload-header h1 {
            margin: 0 0 8px;
            font-size: 25px;
        }

        .upload-body {
            padding: 24px;
        }

        .booking-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #eef3ff;
            border-left: 5px solid #01228c;
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
</style>
</head>

<body>

<div class="upload-card">

    <div class="upload-header">
        <a
                            href="https://tourbeez.com"
                            target="_blank"
                            style="text-decoration:none;"
                        >
                            <img
                                src="https://tourbeez.com/public/admin/dist/img/logo.jpg"
                                alt="TourBeez"
                                width="200"
                                style="
                                    width:200px;
                                    max-width:100%;
                                    display:inline-block;
                                    border:0;
                                    margin-bottom:10px;
                                "
                            >
                        </a>
        <!-- <div>TourBeez</div> -->
    </div>

    <div class="upload-body">

        <h1 style="margin:0 0 18px; font-size: 25px;">Upload Tour Photos</h1>
        <div
            id="uploadMessage"
            class="message"
        ></div>

        <div class="booking-info">

            <p style="margin:0 0 8px;">
                <strong>Booking Number:</strong>
                #{{ $order->order_number }}
            </p>

            <p style="margin:0 0 8px;">
                <strong>Passenger:</strong>
                {{ $order->customer?->name ?? 'Guest' }}
            </p>

            <!-- <p style="margin:0;">
                <strong>Tour:</strong>
                {{ $tourId }}
            </p> -->

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