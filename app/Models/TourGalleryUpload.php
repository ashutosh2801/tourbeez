<?php

namespace App\Models;

use App\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourGalleryUpload extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'upload_id',
        'order_id',
        'tour_id',
        'uploaded_by_type',
        'is_approved',
    ];

    protected $casts = [
        'upload_id'   => 'integer',
        'order_id'    => 'integer',
        'tour_id'     => 'integer',
        'is_approved' => 'boolean',
    ];

    public function upload()
    {
        return $this->belongsTo(
            Upload::class,
            'upload_id'
        );
    }

    public function order()
    {
        return $this->belongsTo(
            Order::class,
            'order_id'
        );
    }

    public function tour()
    {
        return $this->belongsTo(
            Tour::class, 
            'tour_id'
        );
    }
}