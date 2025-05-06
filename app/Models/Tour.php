<?php

namespace App\Models;

use App\Upload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Requests;

class Tour extends Model
{
    use SoftDeletes, HasFactory;

    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(Upload::class);
    }

    public function getMainImageAttribute()
    {
        return $this->galleries()->wherePivot('is_main', 1)->first();
    }

    public function meta()
    {
        return $this->hasMany(TourMeta::class);
    }

    // public function main_image()
    // {
    //     return $this->hasOne(TourUpload::class)->where('is_main', 1);
    // }

    // public function images()
    // {
    //     return $this->hasMany(TourUpload::class)->where('is_main', '<>', 1);
    // }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function getCategoryNamesAttribute(): string
    {
        return $this->categories->pluck('name')->implode(', ');
    }

    public function tourtypes(): BelongsToMany
    {
        return $this->belongsToMany(Tourtype::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class);
    }

    public function pickups(): BelongsToMany
    {
        return $this->belongsToMany(Pickup::class);
    }

    public function itineraries(): BelongsToMany
    {
        return $this->belongsToMany(Itinerary::class);
    }

    public function itineraryAll()
    {
        return Itinerary::all();
    }
    
    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class);
    }

    public function faqAll()
    {
        return Faq::all();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    public function featureAll($type = '')
    {
        if($type != '') {
            return Feature::where('type', $type)->get();
        }
        return Faq::all();
    }

    public function taxes_fees(): BelongsToMany
    {
        return $this->belongsToMany(TaxesFee::class);
    }
    
    public function getTourTypeNameAttribute(): string
    {
        return $this->tour_types->pluck('name')->implode(', ');
    }

    public function detail()
    {
        return $this->hasOne(TourDetail::class);
    }

    public function location()
    {
        return $this->hasOne(TourLocation::class);
    }

    public function schedule()
    {
        return $this->hasOne(TourSchedule::class);
    }    

    public function pricings()
    {
        return $this->hasMany(TourPricing::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class,'sub_category_id');
    }

    public function collection()
    {
        return $this->belongsTo(SubCategory::class,'collection_id');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
