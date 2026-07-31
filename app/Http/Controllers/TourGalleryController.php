<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TourGalleryUpload;
use App\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class TourGalleryController extends Controller
{
    public function showOrderVerification(Request $request, Order $order)
    {
        abort_unless(
            $request->hasValidSignature(),
            403,
            'This verification link is invalid or has expired.'
        );

        return view('tour-gallery.required-order-id', [
            'order' => $order,
            'verificationUrl' => $request->fullUrl(),
        ]);
    }

    public function verifyOrderId(Request $request, Order $order)
    {
        abort_unless(
            $request->hasValidSignature(),
            403,
            'This verification link is invalid or has expired.'
        );

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'max:50'],
        ], [
            'order_id.required' => 'Please enter your Order ID.',
        ]);

        $verifiedOrder = Order::withoutGlobalScopes()
            ->where('order_number', trim($validated['order_id']))
            ->first();

        if (!$verifiedOrder) {
            return back()
                ->withInput()
                ->withErrors([
                    'order_id' => 'The Order ID is not valid.',
                ]);
        }

        return redirect()->to(URL::temporarySignedRoute(
            'tour-gallery.show',
            now()->addDays(30),
            ['order' => $verifiedOrder->id]
        ));
    }

    /**
     * Summary of index
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $galleryQuery = TourGalleryUpload::with([
            'upload',
            'order.customer',
        ]);

        /*
        * Booking number search.
        */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $galleryQuery->whereHas('order', function ($query) use ($search) {
                $query->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('id', $search);
            });
        }

        /*
        * Tour filter.
        */
        if ($request->filled('tour_id')) {
            $galleryQuery->where(
                'tour_id',
                $request->tour_id
            );
        }

        /*
        * Approval status filter.
        */
        if ($request->status === 'approved') {
            $galleryQuery->where('is_approved', true);
        }

        if ($request->status === 'pending') {
            $galleryQuery->where('is_approved', false);
        }

        $galleryUploads = $galleryQuery
            ->latest()
            ->paginate(24)
            ->appends($request->query());

        return view(
            'tour-gallery.index',
            compact('galleryUploads')
        );
    }

    public function approve(
        TourGalleryUpload $galleryUpload
    ) {
        $galleryUpload->update([
            'is_approved' => true,
        ]);

        return back()->with(
            'success',
            'Photo approved successfully.'
        );
    }
    public function reject(
        TourGalleryUpload $galleryUpload
    ) {
        $galleryUpload->update([
            'is_approved' => false,
        ]);

        return back()->with(
            'success',
            'Photo moved to pending status.'
        );
    }

    public function destroy(
    TourGalleryUpload $galleryUpload
) {
    try {
        $upload = $galleryUpload->upload;

        if ($upload) {
            $paths = array_filter([
                $upload->file_name,
                $upload->medium_name,
                $upload->thumb_name,
            ]);

            foreach ($paths as $path) {
                if ($this->isS3Enabled()) {
                    Storage::disk('s3')->delete($path);
                } else {
                    $localPath = public_path($path);

                    if (file_exists($localPath)) {
                        unlink($localPath);
                    }
                }
            }

            /*
             * Existing Upload model soft deletes use karta hai.
             */
            $upload->delete();
        }

        $galleryUpload->delete();

        return back()->with(
            'success',
            'Gallery photo deleted successfully.'
        );
    } catch (\Throwable $exception) {
        Log::error('Tour gallery delete failed', [
            'gallery_id' => $galleryUpload->id,
            'error'      => $exception->getMessage(),
        ]);

        return back()->with(
            'error',
            'Photo could not be deleted.'
        );
    }
}


    /**
     * Passenger ko gallery upload page dikhayega.
     */
    public function show(Request $request, Order $order)
    {
        /*
         * Signed URL validate karega.
         */
        abort_unless(
            $request->hasValidSignature(),
            403,
            'This upload link is invalid or has expired.'
        );

        $tourId = $this->getTourIdFromOrder($order);

        abort_if(
            !$tourId,
            404,
            'Tour information was not found for this booking.'
        );

        return view('tour-gallery.upload', [
            'order'  => $order,
            'tourId' => $tourId,
        ]);
    }

    /**
     * Passenger ki selected images upload karega.
     */
    public function store(Request $request, Order $order)
    {
        abort_unless(
            $request->hasValidSignature(),
            403,
            'This upload link is invalid or has expired.'
        );

        $tourId = $this->getTourIdFromOrder($order);

        abort_if(
            !$tourId,
            404,
            'Tour information was not found for this booking.'
        );

        $validated = $request->validate([
            'photos' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'photos.*' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ], [
            'photos.required' => 'Please select at least one photo.',
            'photos.max'      => 'You can upload a maximum of 10 photos.',
            'photos.*.image'  => 'Only image files are allowed.',
            'photos.*.mimes'  => 'Only JPG, JPEG, PNG and WebP files are allowed.',
            'photos.*.max'    => 'Each photo must be smaller than 10 MB.',
        ]);

        $uploadedFiles = [];

        try {
            foreach ($validated['photos'] as $file) {
                $uploadedFiles[] = $this->saveGalleryImage(
                    file: $file,
                    order: $order,
                    tourId: $tourId
                );
            }

            return response()->json([
                'status'  => true,
                'message' => count($uploadedFiles) .
                    ' photo(s) uploaded successfully.',
                'data' => $uploadedFiles,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Passenger tour gallery upload failed', [
                'order_id' => $order->id,
                'tour_id'  => $tourId,
                'error'    => $exception->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Photos could not be uploaded. Please try again.',
            ], 500);
        }
    }

    /**
     * Single image ko original, medium aur thumbnail
     * format me create karega.
     */
    private function saveGalleryImage(
        $file,
        Order $order,
        int $tourId
    ): array {
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $originalName = Str::slug(
            pathinfo(
                $file->getClientOriginalName(),
                PATHINFO_FILENAME
            )
        );

        if (blank($originalName)) {
            $originalName = 'tour-photo';
        }

        /*
         * Duplicate file name avoid karne ke liye UUID.
         */
        $uniqueName = $originalName . '-' . Str::uuid();

        /*
         * S3 folder:
         *
         * uploads/tour-gallery/{tour_id}/orders/{order_id}
         */
        $directory = sprintf(
            'uploads/tour-gallery/%s/orders/%s',
            $tourId,
            $order->id
        );

        $fileName = $uniqueName . '.' . $extension;

        $mediumFileName = $uniqueName .
            '-600x600.' .
            $extension;

        $thumbFileName = $uniqueName .
            '-200x200.' .
            $extension;

        $path = $directory . '/' . $fileName;

        $mediumPath = $directory . '/' . $mediumFileName;

        $thumbPath = $directory . '/' . $thumbFileName;

        /*
         * Local directory create karein.
         */
        if (!is_dir(public_path($directory))) {
            mkdir(
                public_path($directory),
                0755,
                true
            );
        }

        /*
         * Original image.
         */
        $image = Image::read($file);

        $image->save(
            public_path($path)
        );

        /*
         * Medium image.
         */
        $mediumImage = clone $image;

        $mediumImage
            ->scaleDown(
                width: 600,
                height: 600
            )
            ->save(
                public_path($mediumPath)
            );

        /*
         * Thumbnail.
         */
        $thumbImage = clone $image;

        $thumbImage
            ->scaleDown(
                width: 200,
                height: 200
            )
            ->save(
                public_path($thumbPath)
            );

        /*
         * S3 configured ho to teeno files S3 par upload karein.
         */
        if ($this->isS3Enabled()) {
            $this->uploadFileToS3($path);
            $this->uploadFileToS3($mediumPath);
            $this->uploadFileToS3($thumbPath);
        }

        /*
         * Existing uploads table me file record save.
         */
        $upload = new Upload();

        $upload->file_original_name = $originalName;
        $upload->file_name = $path;
        $upload->medium_name = $mediumPath;
        $upload->thumb_name = $thumbPath;
        $upload->extension = $extension;
        $upload->type = 'image';
        $upload->file_size = $file->getSize();

        /*
         * Passenger logged in nahi hoga.
         * Isliye order/supplier ka user_id use hoga.
         */
        $upload->user_id = $this->getUploadOwnerId($order);

        $upload->save();

        /*
         * New table me order/tour mapping.
         */
        $galleryUpload = TourGalleryUpload::create([
            'upload_id'        => $upload->id,
            'order_id'         => $order->id,
            'tour_id'          => $tourId,
            'uploaded_by_type' => 'passenger',
            'is_approved'      => false,
        ]);

        return [
            'gallery_id' => $galleryUpload->id,
            'upload_id'  => $upload->id,
            'file_name'  => $upload->file_name,
            'thumb_name' => $upload->thumb_name,
        ];
    }

    /**
     * Local image ko AWS S3 me upload karega.
     */
    private function uploadFileToS3(string $path): void
    {
        $localPath = public_path($path);

        if (!file_exists($localPath)) {
            throw new \RuntimeException(
                'Image file was not created: ' . $path
            );
        }

        Storage::disk('s3')->put(
            $path,
            file_get_contents($localPath),
            [
                'visibility'  => 'public',
                'ContentType' => mime_content_type($localPath),
            ]
        );
    }

    /**
     * Check karega ki S3 enabled hai.
     */
    private function isS3Enabled(): bool
    {
        return config('filesystems.default') === 's3' ||
            env('FILESYSTEM_DRIVER') === 's3';
    }

    /**
     * Order ke corresponding tour_id return karega.
     */
    private function getTourIdFromOrder(Order $order): ?int
    {
        /*
         * Order table me direct tour_id ho.
         */
        if (!empty($order->tour_id)) {
            return (int) $order->tour_id;
        }

        /*
         * Agar order items relation me tour_id ho.
         *
         * Relation ka naam apne project ke according
         * adjust karna ho sakta hai.
         */
        if (method_exists($order, 'items')) {
            $tourId = $order->items()
                ->whereNotNull('tour_id')
                ->value('tour_id');

            if ($tourId) {
                return (int) $tourId;
            }
        }

        return null;
    }

    /**
     * Upload model par SupplierScope laga hua hai.
     * Isliye valid supplier/user ID save karna zaroori hai.
     */
    private function getUploadOwnerId(Order $order): int
    {
        // $userId = $order->supplier_id
        //     ?? $order->user_id
        //     ?? $order->created_by
        //     ?? null;

        // if (!$userId) {
        //     throw new \RuntimeException(
        //         'Supplier/user ID was not found for this order.'
        //     );
        // }
        $userId = $order->created_by ?? 1; // Default user ID for uploads, adjust as needed

        return (int) $userId;
    }
}
