<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use Illuminate\Support\Facades\Validator;
use App\Models\Addon;
use App\Models\Tour;
use App\Models\Category;
use App\Models\TourLocation;
use App\Models\Tourtype;
use App\Models\TourDetail;
use App\Models\TourImage;
use App\Models\TourPricing;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Str;

class TourController extends Controller
{
    protected $imageService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;

        $data = Tour::orderBy('id', 'DESC')->get();
        view()->share('data', $data);

        $category = Category::get();
        view()->share('category', $category);

        $tour_type = Tourtype::get();
        view()->share('tour_type', $tour_type);

        $addons = Addon::orderBy('sort_order','ASC')->get();
        view()->share('addons', $addons);

        $pickups = Pickup::orderBy('id','ASC')->get();
        view()->share('pickups', $pickups);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.tours.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tours.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'                 => 'required|max:255',
            'description'           => 'required',
            'long_description'      => 'required',
            'price_type'            => 'required',
            'PriceOption'           => 'required|array',
            'PriceOption.*.label'   => 'required|string|max:255',
            'PriceOption.*.price'   => 'required|numeric|min:0',
            'PriceOption.*.qty_used'=> 'required|integer|min:0',
            'advertised_price'      => 'required',
            'category'              => 'required|array',
            'category.*'            => 'integer|exists:categories,id',
            'tour_type'             => 'required|array',
            'tour_type.*'           => 'integer|exists:tourtypes,id',
            'country'               => 'required',
            'state'                 => 'required',
            'city'                  => 'required',

            //'image' => 'required|image',
        ],
        [
            'title.required' => 'Please enter a title',
            'description.required' => 'Please enter a description',
            'long_description.required' => 'Please enter a long description',
            'price_type.required' => 'Please select a price type',
            'PriceOption.*.label.required' => 'Please enter a label for the price option',
            'PriceOption.*.price.required' => 'Please enter a price for the price option',
            'PriceOption.*.qty_used.required' => 'Please enter a quantity used for the price option',
            'advertised_price.required' => 'Please enter an advertised price',
            'category.required' => 'Please select at least one category',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($request->title);
        $uniqueSlug = $baseSlug;
        $counter = 1;
        while (Tour::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Create new product instance
        $tour = new Tour();
        $tour->title      = $request->title;
        $tour->slug       = $uniqueSlug;
        $tour->unique_code= $request->unique_code;
        $tour->price      = $request->advertised_price;
        $tour->price_type = $request->price_type;
        $tour->country    = $request->country;
        $tour->state      = $request->state;
        $tour->city       = $request->city;

        if($tour->save()) {

            // Save categories
            if ($request->has('category') && is_array($request->category)) {
                $tour->categories()->sync($request->category);
            }
            // Save tour types
            if ($request->has('tour_type') && is_array($request->tour_type)) {
                $tour->tour_types()->sync($request->tour_type);
            }

            if ($request->has('PriceOption') && is_array($request->PriceOption)) {
                foreach ($request->PriceOption as $option) {
                    $pricing = new TourPricing();
                    $pricing->tour_id       = $tour->id;
                    $pricing->label         = $option['label'] ?? null;
                    $pricing->price         = $option['price'] ?? null;
                    $pricing->quantity_used = $option['quantity_used'] ?? 0;
                    $pricing->save();
                }
            }
            
            $tour_detail = new TourDetail();
            $tour_detail->tour_id               = $tour->id;
            $tour_detail->description           = $request->description;
            $tour_detail->long_description      = $request->long_description;
            $tour_detail->quantity_min          = $request->quantity_min;
            $tour_detail->quantity_max          = $request->quantity_max;
            $tour_detail->IsPurchasedAsAGift    = $request->IsPurchasedAsAGift?1:0;
            $tour_detail->IsExpiryDays          = $request->IsExpiryDays?1:0;
            $tour_detail->expiry_days           = $request->expiry_days;
            $tour_detail->IsExpiryDate          = $request->IsExpiryDate?1:0;
            $tour_detail->expiry_date           = $request->expiry_date;
            $tour_detail->gift_tax_fees         = $request->gift_tax_fees?1:0;
            $tour_detail->IsTerms               = $request->IsTerms?1:0;
            $tour_detail->terms_and_conditions  = $request->terms_and_conditions;
            $tour_detail->save();

            $location = new TourLocation();
            $location->tour_id      = $tour->id;
            $location->country_id   = $request->country;
            $location->state_id     = $request->state;
            $location->city_id      = $request->city;            
            $location->save();
        }
        
        $tourId = $tour->id;
        if( $request->hasFile('image') ) { 
            $tour_image = new TourImage();
            $tour_image->name = $this->imageService->compressAndStoreImage($request->file('image'), $uniqueSlug, 'tour');
            $tour_image->tour_id = $tourId;
            $tour_image->type = 'Image';
            $tour_image->is_main = 1;
            $tour_image->save();
        }

        // if ($request->hasFile('slider_images')) {
        //     foreach ($request->file('slider_images') as $image) {
        //         $realImage = $request->slug . "-" . rand(1, 9999) . "-" . date('d-m-Y-h-s') . "." . $image->getClientOriginalExtension();
        //         $path = $image->move('tour-slider-images', $realImage);
        //         TourImage::create([
        //             'tour_id' => $tourId,
        //             'image' => $realImage,
        //         ]);
        //     }
        // }

        //$this->handleProductSliderImages($request->file('product_images'), $tourId);
        return redirect()->route('admin.tour.index')->with('success', 'Tour created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tour $tour)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Tour::where('id', decrypt($id))->first();
        //echo $data->id;
        //echo '<pre>'; print_r($data->tourtypes); exit;
        //$tourImages = TourImage::where('tour_id', $data->id)->get();
        return view('admin.tours.edit.index', compact( 'data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    }
    public function basic_detail_update(Request $request, $id)
    {

        //dd($request->all(), $request->file('image'));
        $request->validate([
            'title'                 => 'required|max:255',
            'description'           => 'required',
            'long_description'      => 'required',
            'price_type'            => 'required',
            'PriceOption'           => 'required|array',
            'PriceOption.*.label'   => 'required|string|max:255',
            'PriceOption.*.price'   => 'required|numeric|min:0',
            'PriceOption.*.qty_used'=> 'required|integer|min:0',
            'advertised_price'      => 'required',
            'category'              => 'required|array',
            'category.*'            => 'integer|exists:categories,id',
            'tour_type'             => 'required|array',
            'tour_type.*'           => 'integer|exists:tourtypes,id',
            'country'               => 'required',
            'state'                 => 'required',
            'city'                  => 'required',

            //'image' => 'required|image',
        ],
        [
            'title.required' => 'Please enter a title',
            'description.required' => 'Please enter a description',
            'long_description.required' => 'Please enter a long description',
            'price_type.required' => 'Please select a price type',
            'PriceOption.*.label.required' => 'Please enter a label for the price option',
            'PriceOption.*.price.required' => 'Please enter a price for the price option',
            'PriceOption.*.qty_used.required' => 'Please enter a quantity used for the price option',
            'advertised_price.required' => 'Please enter an advertised price',
            'category.required' => 'Please select at least one category',
        ]);

        $baseSlug = Str::slug($request->title);
        $uniqueSlug = $baseSlug;
        $counter = 1;
        while (Tour::where('slug', $uniqueSlug)->where('id', '!=', $request->id)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        // Update tour instance
        $tour             = Tour::findOrFail($id);
        $tour->title      = $request->title;
        $tour->slug       = $uniqueSlug;
        $tour->unique_code= $request->unique_code;
        $tour->price      = $request->advertised_price;
        $tour->price_type = $request->price_type;
        $tour->country    = $request->country;
        $tour->state      = $request->state;
        $tour->city       = $request->city;

        if($tour->save()) {

            // Save categories
            if ($request->has('category') && is_array($request->category)) {
                $tour->categories()->sync($request->category);
            }
            // Save tour types
            if ($request->has('tour_type') && is_array($request->tour_type)) {
                $tour->tourtypes()->sync($request->tour_type);
            }

            if ($request->has('PriceOption') && is_array($request->PriceOption)) {

                // Optional: delete old ones not in the list (if needed)
                $pricingIds = collect($request->PriceOption)->pluck('id')->filter()->toArray();
                if( !empty($pricingIds) ) { 
                    //print_r($request->PriceOption); print_r($pricingIds); exit;
                    $tour->pricings()->whereNotIn('id', $pricingIds)->delete();
                }

                foreach ($request->PriceOption as $option) {
                    if (!empty($option['id'])) {
                        $pricing = TourPricing::find($option['id']);
                        if ($pricing && $pricing->tour_id == $tour->id) {
                            $pricing->label = $option['label'] ?? null;
                            $pricing->price = $option['price'] ?? null;
                            $pricing->quantity_used = $option['quantity_used'] ?? 0;
                            $pricing->save();
                        }
                        // else {
                        //     $pricing = new TourPricing();
                        //     $pricing->tour_id       = $tour->id;
                        //     $pricing->label         = $option['label'] ?? null;
                        //     $pricing->price         = $option['price'] ?? null;
                        //     $pricing->quantity_used = $option['quantity_used'] ?? 0;
                        //     $pricing->save();
                        // }
                    } else {
                        $pricing = new TourPricing();
                        $pricing->tour_id       = $tour->id;
                        $pricing->label         = $option['label'] ?? null;
                        $pricing->price         = $option['price'] ?? null;
                        $pricing->quantity_used = $option['quantity_used'] ?? 0;
                        $pricing->save();
                    }
                }
            }
            
            $tour_detail = TourDetail::where('tour_id', $tour->id)->first();
            $tour_detail->tour_id               = $tour->id;
            $tour_detail->description           = $request->description;
            $tour_detail->long_description      = $request->long_description;
            $tour_detail->quantity_min          = $request->quantity_min;
            $tour_detail->quantity_max          = $request->quantity_max;
            $tour_detail->IsPurchasedAsAGift    = $request->IsPurchasedAsAGift?1:0;
            $tour_detail->IsExpiryDays          = $request->IsExpiryDays?1:0;
            $tour_detail->expiry_days           = $request->expiry_days;
            $tour_detail->IsExpiryDate          = $request->IsExpiryDate?1:0;
            $tour_detail->expiry_date           = $request->expiry_date;
            $tour_detail->gift_tax_fees         = $request->gift_tax_fees?1:0;
            $tour_detail->IsTerms               = $request->IsTerms?1:0;
            $tour_detail->terms_and_conditions  = $request->terms_and_conditions;
            $tour_detail->save();
        
            if( $request->hasFile('image') ) { 
                $tour_image = TourImage::where('tour_id', $tour->id)->where('is_main', 1)->first();
                if($tour_image) {
                    $image_path = public_path('tour-image/' . $tour_image->image);
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                else
                $tour_image = new TourImage();

                $filename = $this->imageService->compressAndStoreImage($request->file('image'), $uniqueSlug, 'tour');
                $tour_image->image      = $filename;
                $tour_image->tour_id    = $tour->id;
                $tour_image->type       = 'Image';
                $tour_image->is_main    = 1;
                $tour_image->save();
            }
        }

        // if ($request->hasFile('slider_images')) {
        //     foreach ($request->file('slider_images') as $image) {
        //         $realImage = $request->slug . "-" . rand(1, 9999) . "-" . date('d-m-Y-h-s') . "." . $image->getClientOriginalExtension();
        //         $path = $image->move('tour-slider-images', $realImage);
        //         TourImage::create([
        //             'tour_id' => $tourId,
        //             'image' => $realImage,
        //         ]);
        //     }
        // }

        return redirect()->back()->with('success', 'Tour  created successfully');
    }

    public function addon_update(Request $request, $id) {

        $tour  = Tour::findOrFail($id);
        // Save tour types
        if ($request->has('addons') && is_array($request->addons)) {
            $tour->addons()->sync($request->addons);
        }

        return back()->withInput()->with('success','Addon saved successfully.');
    }

    public function location_update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'address'       => 'required|max:255',
            'destination'   => 'required|max:255',
            'postal_code'   => 'required',
            'country'       => 'required',
            'state'         => 'required',
            'city'          => 'required'
        ]);
        
        if ($validator->fails()) {
            // Validation failed
            return back()->withInput()->withErrors($validator)->with('error','Something went wrong!');
        }

        $tour  = Tour::findOrFail($id);
        $location = $tour->location;
        if(!empty($location)) {
            $location = new TourLocation();        
            $location->tour_id      = $tour->id;
        }

        $location->country_id       = $request->country;
        $location->state_id         = $request->state;
        $location->city_id          = $request->city;
        $location->destination      = $request->destination;
        $location->address          = $request->address;
        $location->postal_code      = $request->postal_code;
        if($location->save() ) {
            return back()->withInput()->with('success','Location saved successfully.');
        }

        return back()->withInput()->withErrors($request->all())->with('error','Something went wrong!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tour = Tour::where('id', decrypt($id))->first();
        $tour->delete();
        // if ($tour) {
        //     $image_path = public_path('tour-image/' . $tour->image);
        //     if (file_exists($image_path)) {
        //         unlink($image_path);
        //         $tour->delete();
        //     }
        // }
        // $tourCollectionId = decrypt($id);
        // $imagesToDelete = T_BOOLEAN_ORourImage::where('tour_id', $tourCollectionId)->get();
        // foreach ($imagesToDelete as $image) {
        //     $imagePath = public_path('tour-slider-images/' . $image->image);
        //     // Delete the record from the database
        //     $image->delete();
        //     // Unlink (delete) the image from storage
        //     if (file_exists($imagePath)) {
        //         unlink($imagePath);
        //     }
        // }
        return redirect()->route('admin.tour.index')->with('error', 'Tour deleted successfully.');
    }
}
