<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Feature;
use App\Models\Itinerary;
use App\Models\Pickup;
use App\Models\TaxesFee;
use App\Models\TourSchedule;
use App\Models\TourUpload;
use Illuminate\Support\Facades\Redirect;
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

        $pickups = Pickup::orderBy('sort_order','ASC')->get();
        view()->share('pickups', $pickups);

        $taxesfees = TaxesFee::orderBy('sort_order','ASC')->get();
        view()->share('taxesfees', $taxesfees);
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
            'image'                 => 'required|intger',
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
            'image.required' => 'Please select at featured image',
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
        if( $request->has('image') ) { 
            // Unset is_main for all current pivot rows
            $tour->galleries()->updateExistingPivot($tour->galleries->pluck('id'), ['is_main' => 0]);

            // Set is_main = 1 for the selected image
            $tour->galleries()->updateExistingPivot($request->image, ['is_main' => 1]);
            // $tour_image = new TourUpload();
            // $tour_image->tour_id    = $tourId;
            // $tour_image->upload_id  = $request->image;
            // $tour_image->is_main    = 1;
            // $tour_image->save();
        }

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
        $data       = Tour::where('id', decrypt($id))->first();
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $schedule   = $data->schedule ? $data->schedule :  new TourSchedule();
        $metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();

        return view('admin.tours.edit.index', compact( 'data', 'detail', 'schedule', 'metaData'));
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
            // 'country'               => 'required',
            // 'state'                 => 'required',
            // 'city'                  => 'required',

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
        // $tour->country    = $request->country;
        // $tour->state      = $request->state;
        // $tour->city       = $request->city;

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

            $tourId = $tour->id;
            if( $request->has('image') ) { 

                // Check if the requested image is already attached to the tour
                if ($tour->galleries->contains($request->image)) {
                    // Just update pivot
                    $tour->galleries()->updateExistingPivot($request->image, ['is_main' => 1]);
                } else {
                    // Attach and set is_main = 1
                    $tour->galleries()->attach($request->image, ['is_main' => 1]);
                }

                // Unset is_main for all current pivot rows
                // $tour->galleries()->updateExistingPivot($tour->galleries->pluck('id'), ['is_main' => 0]);

                // Set is_main = 1 for the selected image
                // $tour->galleries()->updateExistingPivot($request->image, ['is_main' => 1]);

                // $tour_image = TourUpload::where('tour_id', $tourId)->where('is_main', 1)->first();
                // if( !$tour_image ) {
                //     $tour_image = new TourUpload();
                // }
                // $tour_image->tour_id    = $tourId;
                // $tour_image->upload_id  = $request->image;
                // $tour_image->is_main    = 1;
                // $tour_image->save();
            }
        
            /*if( $request->hasFile('image') ) { 
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
            }*/
        }

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

    public function pickup_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);
        // Save tour types
        if ($request->has('pickups') && is_array($request->pickups)) {
            $tour->pickups()->sync($request->pickups);

            return Redirect::route('admin.tour.edit', encrypt($id) )->with('success','Pickup location saved successfully.'); 
        }

        return back()->withInput()->with('success','Addon saved successfully.');
    }

    public function seo_update(Request $request, $id)
    {

        //dd($request->all(), $request->file('image'));
        $request->validate([
            'meta_title'         => 'required|max:255',
            'meta_description'   => 'required',
            //'meta_keywords'      => 'required',
            'canonical_url'      => 'required',
        ],
        [
            'meta_title.required'       => 'Please enter a meta title',
            'meta_description.required' => 'Please enter a meta description',
            //'meta_keywords.required'    => 'Please enter a long description',
            'canonical_url.required'    => 'Please enter a canonical url',
        ]);
        
        // Update tour instance
        $tour  = Tour::findOrFail($id);
        if($tour) {            
            $tour_detail = TourDetail::where('tour_id', $tour->id)->first();
            $tour_detail->meta_title       = $request->meta_title;
            $tour_detail->meta_description = $request->meta_description;
            $tour_detail->meta_keywords    = $request->meta_keywords;
            $tour_detail->canonical_url    = $request->canonical_url;
            if ($tour_detail->save() ) {
                return Redirect::route('admin.tour.edit', encrypt($id) )->with('success','Tour SEO data saved successfully.'); 
            }
        }

        return redirect()->back()->with('error', 'Something went wrong!');
    }
    public function schedule_update(Request $request, $id) {
        //echo $request->session_start_time;   exit;
        $request->validate([
            'minimum_notice_num'    => 'required|integer|min:0',
            'minimum_notice_unit'   => 'required',
            'session_start_date'    => 'required|date_format:Y-m-d',
            'session_start_time'    => 'required',
            'session_end_date'      => 'required|date_format:Y-m-d',
            'session_end_time'      => 'required',

            'repeat_period'         => 'required|string|in:NONE,MINUTELY,HOURLY,DAILY,WEEKLY,MONTHLY,YEARLY', 
            'repeat_period_unit'    => 'required_if:repeat_period,MINUTELY,HOURLY|integer',
            'until_date'            => 'required_if:repeat_period,MINUTELY,HOURLY|date',  
        ],
        [
            'minimum_notice_num.required'   => 'Please enter a minimum notice number',
            'minimum_notice_unit.required'  => 'Please select a minimum notice unit',
            'session_start_date.required'   => 'Please enter a start date',
            'session_start_time.required'   => 'Please enter a start time',
            'session_end_date.required'     => 'Please enter a to date',
            'session_end_time.required'     => 'Please enter a to time',
            'repeat_period.required'        => 'Please select a repeat',
        ]);

        // If repeat_period is MINUTELY or HOURLY, validate Repeat[]
        if (in_array($request->repeat_period, ['MINUTELY', 'HOURLY'])) {
            foreach ($request->input('Repeat', []) as $index => $repeat) {
                $request->validate([
                    'num'        => ['nullable'], // checkbox, might not be checked
                    'day'        => ['required_if:num,on', 'string'],
                    'start_time' => ['required_if:num,on'], // required only if num is checked
                    'end_time'   => ['required_if:num,on', 'after:start_time'],
                ], [
                    'start_time.required_if'=> "Start time is required for {$repeat['day']} when selected.",
                    'end_time.required_if'  => "End time is required for {$repeat['day']} when selected.",
                    'end_time.after'        => "End time must be after start time for {$repeat['day']}.",
                ]);
            }
        }

        // If repeat_period is MINUTELY or HOURLY, validate Repeat[]
        if (in_array($request->repeat_period, ['WEEKLY'])) {
            foreach ($request->input('Repeat', []) as $index => $repeat) {
                $request->validate([
                    'num'        => ['nullable'], // checkbox, might not be checked
                    'day'        => ['required_if:num,on', 'string'],
                ]);
            }
        }

        $tour  = Tour::findOrFail($id);

        $schedule = $tour->schedule;
        if( !$schedule ) {
            $schedule = new TourSchedule();
        }

        $schedule->tour_id             = $tour->id;
        $schedule->minimum_notice_num  = $request->minimum_notice_num;
        $schedule->minimum_notice_unit = $request->minimum_notice_unit;
        $schedule->session_start_date  = $request->session_start_date;
        $schedule->session_start_time  = $request->session_start_time;
        $schedule->session_end_date    = $request->session_end_date;
        $schedule->session_end_time    = $request->session_end_time;
        $schedule->sesion_all_day      = $request->sesion_all_day?1:0;
        $schedule->repeat_period       = $request->repeat_period;
        $schedule->repeat_period_unit  = $request->repeat_period_unit;
        $schedule->until_date          = $request->until_date;
        if ($schedule->save() ) {
            // First delete old repeats
            $schedule->repeats()->delete();

            if (in_array($request->repeat_period, ['MINUTELY', 'HOURLY'])) {
                foreach ($request->input('Repeat', []) as $repeat) {
                    if(isset($repeat['num']) && $repeat['num']=='on') {
                        $schedule->repeats()->create([
                            'tour_schedule_id'  => $schedule->id,
                            'day'               => $repeat['day'],
                            'start_time'        => $repeat['start_time'],
                            'end_time'          => $repeat['end_time'],
                        ]);
                    }
                }
            }

            if (in_array($request->repeat_period, ['WEEKLY'])) {
                foreach ($request->input('Repeat', []) as $repeat) {
                    if(isset($repeat['num']) && $repeat['num']=='on') {
                        $schedule->repeats()->create([
                            'tour_schedule_id'  => $schedule->id,
                            'day'               => $repeat['day'],
                        ]);
                    }
                }
            }
        }

        return back()->withInput()->with('success','Schedule saved successfully.');
    }

    public function itinerary_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);

        $request->validate([
            'ItineraryOptions'               => 'required|array',
            'ItineraryOptions.*.title'       => 'required|string|max:255',
            'ItineraryOptions.*.datetime'        => 'required|string|max:255',
            'ItineraryOptions.*.address'     => 'required|string|max:255',
            'ItineraryOptions.*.description' => 'required',
        ],
        [
            'ItineraryOptions.*.title.required'      => 'Itinerary title is required',
            'ItineraryOptions.*.datetime.required'   => 'Itinerary datetime is required',
            'ItineraryOptions.*.address.required'    => 'Itinerary address is required',
            'ItineraryOptions.*.description.required'=> 'Itinerary description is required',
        ]);

        //Save new itinerary
        $itineraryIds = [];
        foreach ($request->ItineraryOptions as $option) {
            $itinerary = Itinerary::where('title', $option['title'])->where('datetime', $option['datetime'])->where('address', $option['address'])->first();
            if (!$itinerary) {
                $itinerary = new Itinerary();
                $itinerary->user_id     = auth()->user()->id;
                $itinerary->title       = $option['title'] ?? null;
                $itinerary->datetime    = $option['datetime'] ?? null;
                $itinerary->address     = $option['address'] ?? null;
                $itinerary->description = $option['description'] ?? null;
                $itinerary->save();
            } 
            $itineraryIds[] = $itinerary->id;
        }

        // Sycc itineraries
        if ( !empty($itineraryIds) ) {
            //$tour->itineraries()->attach([1, 2, 3]);
            $tour->itineraries()->sync($itineraryIds);
        }

        return redirect()->back()->with('success','Itineraries saved successfully.');
    }

    public function faq_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);

        $request->validate([
            'FaqOptions'            => 'required|array',
            'FaqOptions.*.question' => 'required|string|max:255',
            'FaqOptions.*.answer'   => 'required',
        ],
        [
            'FaqOptions.*.question.required'=> 'Question is required',
            'FaqOptions.*.answer.required'  => 'Answer is required',
        ]);

        //Save new faqs
        $faqIds = [];
        foreach ($request->FaqOptions as $option) {
            $faq = Faq::where('question', $option['question'])->first();
            if (!$faq) {
                $faq = new Faq();
                $faq->user_id  = auth()->user()->id;
                $faq->question = $option['question'] ?? null;
                $faq->answer   = $option['answer'] ?? null;
                $faq->save();
            } 
            $faqIds[] = $faq->id;
        }

        // Sycc faqs
        if ( !empty($faqIds) ) {
            $tour->faqs()->sync($faqIds);
        }

        return redirect()->back()->with('success','FAQs saved successfully.');
    }

    public function inclusion_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);

        $request->validate([
            'InclusionOptions'        => 'required|array',
            'InclusionOptions.*.name' => 'required|string|max:255',
        ],
        [
            'InclusionOptions.*.name.required'=> 'Name is required',
        ]);

        //Save new Exclusion
        $featureIds = [];
        foreach ($request->InclusionOptions as $option) {
            $feature = Feature::where('name', $option['name'])->first();
            if (!$feature) {
                $feature = new Feature();
                $feature->user_id   = auth()->user()->id;
                $feature->name      = $option['name'] ?? null;
                $feature->type      = 'Inclusion';
                $feature->save();
            } 
            $featureIds[] = $feature->id;
        }

        // Sycc faqs
        if ( !empty($featureIds) ) {
            $tour->features()->sync($featureIds);
        }

        return redirect()->back()->with('success','Inclusions saved successfully.');
    }

    public function exclusion_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);

        $request->validate([
            'ExclusionOptions'        => 'required|array',
            'ExclusionOptions.*.name' => 'required|string|max:255',
        ],
        [
            'ExclusionOptions.*.name.required'=> 'Name is required',
        ]);

        //Save new Exclusion
        $featureIds = [];
        foreach ($request->ExclusionOptions as $option) {
            $feature = Feature::where('name', $option['name'])->first();
            if (!$feature) {
                $feature = new Feature();
                $feature->user_id   = auth()->user()->id;
                $feature->name      = $option['name'] ?? null;
                $feature->type      = 'Exclusion';
                $feature->save();
            } 
            $featureIds[] = $feature->id;
        }

        // Sycc faqs
        if ( !empty($featureIds) ) {
            $tour->features()->sync($featureIds);
        }

        return redirect()->back()->with('success','Exclusions saved successfully.');
    }

    public function taxfee_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);
        // Save tour types
        if ($request->has('taxes') && is_array($request->taxes)) {
            $tour->taxes_fees()->sync($request->taxes);

            return Redirect::route('admin.tour.edit', encrypt($id) )->with('success','Tax and fee saved successfully.'); 
        }

        return back()->withInput()->with('error','OOPs! something went wrong!');
    }

    public function gallery_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);
        // Save tour types
        if ($request->has('gallery') && is_array($request->gallery)) {
            //$tour->galleries()->sync($request->gallery);

            // Filter out empty/null/false/blank values
            $gallery = array_filter($request->gallery, function ($value) {
                return !empty($value);
            });

            // Only sync if we have valid values
            if (!empty($gallery)) {
                $tour->galleries()->sync($gallery);
            }

            return Redirect::route('admin.tour.edit', encrypt($id) )->with('success','Gallery saved successfully.'); 
        }

        return back()->withInput()->with('error','OOPs! something went wrong!');
    }

    public function notification_update(Request $request, $id) 
    {
        $tour = Tour::findOrFail($id);

        $allMetaKeys = ['email_info', 'email_info_text', 'email_attachment', 'email_attachment_file', 'email_notification', 'email_notification_emails', 'sms_send_me', 'sms_send_customer'];

        // Validate incoming request if needed
        $validated = $request->validate([
            'Meta' => 'array',
            'Meta.*' => 'nullable|string', // customize validation as needed
        ]);

        $submittedMeta = $request->input('Meta', []);
        foreach ($allMetaKeys as $key) {
            //if (array_key_exists($key, $submittedMeta)) {
                $value = @$submittedMeta[$key];
                
                // Normalize checkbox value to boolean or string
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }
        
                $tour->meta()->updateOrCreate(
                    ['tour_id' => $tour->id, 'meta_key' => $key],
                    ['meta_value' => $value]
                );
            //} else {
                // If key not in request, delete it (e.g., checkbox was unchecked)
                //$tour->meta()->where('meta_key', $key)->delete();
            //}
        }

        //print_r($submittedMeta); exit;

        // foreach ($submittedMeta as $key => $value) {
        //     $tour->meta()->updateOrCreate(
        //         ['tour_id' => $tour->id, 'meta_key' => $key],
        //         ['meta_value' => $value]
        //     );
        // }

        return redirect()->back()->with('success', 'Tour notification updated successfully!');
    }

    public function reminders_update(Request $request, $id) 
    {
        $tour = Tour::findOrFail($id);

        $allMetaKeys = [
            'email1_reminder', 'email1_reminder_delay', 'email1_reminder_delayUnit', 'email1_reminder_text',
            'email2_reminder', 'email2_reminder_delay', 'email2_reminder_delayUnit', 'email2_reminder_text',
            'email3_reminder', 'email3_reminder_delay', 'email3_reminder_delayUnit', 'email3_reminder_text',
            'sms_reminder_customer', 'sms_reminder_delay', 'sms_reminder_delayUnit'
        ];

        // Validate incoming request if needed
        $validated = $request->validate([
            'Meta' => 'array',
            'Meta.*' => 'nullable|string', // customize validation as needed
        ]);

        $submittedMeta = $request->input('Meta', []);
        foreach ($allMetaKeys as $key) {
            //if (array_key_exists($key, $submittedMeta)) {
                $value = @$submittedMeta[$key];
                
                // Normalize checkbox value to boolean or string
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }
        
                $tour->meta()->updateOrCreate(
                    ['tour_id' => $tour->id, 'meta_key' => $key],
                    ['meta_value' => $value]
                );
            //} else {
                // If key not in request, delete it (e.g., checkbox was unchecked)
                //$tour->meta()->where('meta_key', $key)->delete();
            //}
        }

        //print_r($submittedMeta); exit;

        // foreach ($submittedMeta as $key => $value) {
        //     $tour->meta()->updateOrCreate(
        //         ['tour_id' => $tour->id, 'meta_key' => $key],
        //         ['meta_value' => $value]
        //     );
        // }

        return redirect()->back()->with('success', 'Tour reminder updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tour = Tour::where('id', decrypt($id))->first();
        if ($tour->delete()) {
            flash(translate('Tour info has been deleted successfully'))->success();
            return redirect()->route('admin.tour.index');
        } else {
            flash(translate('Sorry! Something went wrong.'))->error();
            return back();
        }

        
        //return redirect()->route('admin.tour.index')->with('error', 'Tour deleted successfully.');
    }
}
