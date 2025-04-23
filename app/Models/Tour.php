<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use SoftDeletes, HasFactory;

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

    public function main_image()
    {
        return $this->hasOne(TourImage::class)->where('is_main', 1);
    }

    public function images()
    {
        return $this->hasMany(TourImage::class)->where('is_main', '<>', 1);
    }

    public function main_image_html($width='')
    {
        $data = $this->hasOne(TourImage::class)->where('is_main', 1);
        if(isset($data->image) && public_path('tour/' . $data->image) ) {
            $image_file = asset('tour/'.$data->image);
            return '<img src="'.$image_file.'" alt="'.$this->title.'" width="'.$width.'" />';
        }
        return 'NO IMAGE';
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
