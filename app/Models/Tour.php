<?php

namespace App\Models;

use App\Upload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Requests;

class Tour extends Model
{
    use SoftDeletes, HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->useLogName('Tour')
        ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}")
        ->logOnly(['*'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

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

    public function addonsAll(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'addon_tour', 'tour_id', 'addon_id');
    }

    public function pickups(): BelongsToMany
    {
        return $this->belongsToMany(Pickup::class);
    }

    public function itineraries(): BelongsToMany
    {
        return $this->belongsToMany(Itinerary::class);
    }

    public function itinerariesAll()
    {
       return $this->hasMany(Itinerary::class, 'tour_id');
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

    public function inclusions(): BelongsToMany
    {
        return $this->belongsToMany(Inclusion::class);
    }

    public function exclusions(): BelongsToMany
    {
        return $this->belongsToMany(Exclusion::class);
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

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function orderTours()
    {
        return $this->hasMany(OrderTour::class);
    }
}
