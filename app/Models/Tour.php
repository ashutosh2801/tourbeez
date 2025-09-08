<?php

namespace App\Models;

use App\Models\Optional;
use App\Models\TourSpecialDeposit;
use App\Upload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tour extends Model
{
    use SoftDeletes, HasFactory;
    use LogsActivity;

    protected $appends = [
        'formatted_images',
        'discounted_data',
        'duration',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Tour')
            ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}")
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ---------------- RELATIONSHIPS ----------------
    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(Upload::class)->withPivot('is_main');
    }

    public function getMainImageAttribute()
    {
        return $this->galleries()->wherePivot('is_main', 1)->first();
    }

    public function mainImage()
    {
        return $this->belongsToMany(Upload::class, 'tour_upload', 'tour_id', 'upload_id')
                    ->wherePivot('is_main', 1);
    }

    public function meta() { return $this->hasMany(TourMeta::class); }
    public function categories(): BelongsToMany { return $this->belongsToMany(Category::class); }
    public function tourtypes(): BelongsToMany { return $this->belongsToMany(Tourtype::class); }
    public function addons(): BelongsToMany { return $this->belongsToMany(Addon::class)->withPivot('sort_by')->orderBy('addon_tour.sort_by', 'ASC'); }
    public function addonsAll(): BelongsToMany { return $this->belongsToMany(Addon::class, 'addon_tour', 'tour_id', 'addon_id'); }
    public function pickups(): BelongsToMany { return $this->belongsToMany(Pickup::class); }
    public function itineraries(): BelongsToMany { return $this->belongsToMany(Itinerary::class)->withPivot('sort_by'); }
    public function itinerariesAll() { return $this->hasMany(Itinerary::class, 'tour_id'); }
    public function itineraryAll() { return Itinerary::all(); }
    public function faqs(): BelongsToMany { return $this->belongsToMany(Faq::class); }
    public function faqAll() { return Faq::all(); }
    public function inclusions(): BelongsToMany { return $this->belongsToMany(Inclusion::class); }
    public function optionals(): BelongsToMany { return $this->belongsToMany(Optional::class); }
    public function exclusions(): BelongsToMany { return $this->belongsToMany(Exclusion::class); }
    public function features(): BelongsToMany { return $this->belongsToMany(Feature::class); }
    public function taxes_fees(): BelongsToMany { return $this->belongsToMany(TaxesFee::class); }
    public function detail() { return $this->hasOne(TourDetail::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function location() { return $this->hasOne(TourLocation::class); }
    public function schedule() { return $this->hasOne(TourSchedule::class); }
    public function pricings() { return $this->hasMany(TourPricing::class); }
    public function category() { return $this->belongsTo(Category::class,'category_id'); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function orderTours() { return $this->hasMany(OrderTour::class); }
    public function specialDeposit() { return $this->hasOne(TourSpecialDeposit::class, 'tour_id'); }
    public function schedules() { return $this->hasMany(TourSchedule::class); }
    public function subTours() { return $this->hasMany(Tour::class, 'parent_id'); }

    // ---------------- ACCESSORS ----------------
    public function getCategoryNamesAttribute(): string
    {
        return $this->categories->pluck('name')->implode(', ');
    }

    public function getTourTypeNameAttribute(): string
    {
        return $this->tourtypes->pluck('name')->implode(', ');
    }

    /** 🔹 Images */
    public function getFormattedImagesAttribute()
    {
        $galleries = $this->galleries->map(function($g) {
            $image = uploaded_asset($g->id);
            return [
                'original_image' => $image,
                'medium_image'   => str_replace($g->file_name, $g->medium_name, $image),
                'thumb_image'    => str_replace($g->file_name, $g->thumb_name, $image),
            ];
        });

        if ($galleries->isEmpty() && $this->main_image) {
            $image = uploaded_asset($this->main_image->id);
            $galleries = collect([[
                'original_image' => $image,
                'medium_image'   => str_replace($this->main_image->file_name, $this->main_image->medium_name, $image),
                'thumb_image'    => str_replace($this->main_image->file_name, $this->main_image->thumb_name, $image),
            ]]);
        }

        return $galleries;
    }

    /** 🔹 Discount calculation */
    public function getDiscountedDataAttribute()
    {
        $discount         = $this->coupon_value;
        $original_price   = $this->price;
        $discounted_price = $this->price;

        if ($discount && $discount > 0) {
            if ($this->coupon_type === 'fixed') {
                $original_price = round($this->price + $discount);
            } elseif ($this->coupon_type === 'percentage') {
                $original_price = round($this->price / (1 - ($discount / 100)));
            }
        }

        return [
            'discount'          => $discount,
            'discount_type'     => strtoupper($this->coupon_type ?? ''),
            'original_price'    => $original_price,
            'discounted_price'  => $discounted_price,
        ];
    }

    /** 🔹 Duration */
    public function getDurationAttribute()
    {
        if (!$this->schedule) return null;
        return strtolower(trim(
            $this->schedule->estimated_duration_num . ' ' . ucfirst($this->schedule->estimated_duration_unit)
        ));
    }
}
