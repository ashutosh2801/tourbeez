<?php

namespace App\Http\Controllers;

use App\Models\Exclusion;
use App\Models\Faq;
use App\Models\Feature;
use App\Models\Inclusion;
use App\Models\Itinerary;
use App\Models\Pickup;
use App\Models\TaxesFee;
use App\Models\TourSchedule;
use App\Models\Addon;
use App\Models\Tour;
use App\Models\Category;
use App\Models\TourLocation;
use App\Models\Tourtype;
use App\Models\TourDetail;
use App\Models\TourImage;
use App\Models\TourUpload;
use App\Models\TourPricing;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Redirect;
use Validator;
use Str;

class TourController extends Controller
{
    protected $imageService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(ImageService $imageService)
    {
        // $user = \App\Models\User::find(1); // replace with your user ID
        // $user->givePermissionTo('show_tours');
        // $dd = $user->getPermissionNames(); // See if 'show_tours' is listed

        // dd($dd );
        $this->middleware('auth');
        $this->middleware(['permission:show_tours'])->only('index');
        $this->middleware(['permission:add_tour'])->only('create', 'store');
        $this->middleware(['permission:clone_tour'])->only('clone');
        $this->middleware(['permission:edit_tour'])->only('edit', 'update');
        $this->middleware(['permission:delete_tour'])->only('destroy');
        
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
        $validator = Validator::make($request->all(), [
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
            'image'                 => 'required|integer',
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

        if ($validator->fails()) {
            // Validation failed
            return back()->withInput()->withErrors($validator)->with('error','Something went wrong!');
        }
        
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
        $tour->user_id    = auth()->id();
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
            $tour->galleries()->updateExistingPivot($tour->galleries->pluck('id'), ['is_main' => 0]);
            // Check if the requested image is already attached to the tour
            if ($tour->galleries->contains($request->image)) {
                // Just update pivot
                $tour->galleries()->detach($request->image);
            } 
            // Attach and set is_main = 1
            $tour->galleries()->attach($request->image, ['is_main' => 1]);
        }

        return redirect()->route('admin.tour.edit', encrypt($tour->id))->with('success', 'Tour created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function preview($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $schedule   = $data->schedule ? $data->schedule :  new TourSchedule();
        $metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();

        return view('admin.tours.preview.index', compact( 'data', 'detail', 'schedule', 'metaData'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        //$data       = Tour::findOrFail(decrypt($id));
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $schedule   = $data->schedule ? $data->schedule :  new TourSchedule();
        $metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();

        return view('admin.tours.edit.index', compact( 'data', 'detail', 'schedule', 'metaData'));
    }

    public function single(Request $request)
    {
        $data  = Tour::find($request->id);
        $str = '';
        $subtotal = 0;
        if($data) {
            $_tourId = $data->id;
            $row_id = 'row_'.$request->tourCount;
            $str = '<div id="'.$row_id.'" style="border:1px solid #e1a604; margin-bottom:10px">
                    <input type="hidden" name="tour_id[]" value="' .  $data->id . '" />  
                    <table class="table">
                        <tr>
                            <td width="600"><h3 class="text-lg">' .  $data->title . '</h3></td>
                            <td class="text-right" width="200">
                                <div class="input-group">
                                    <input type="text" class="aiz-date-range form-control" id="tour_startdate" name="tour_startdate[]" placeholder="Select Date" data-single="true" data-show-dropdown="true" value="2025-05-31">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right" width="200">
                                <div class="input-group">
                                    <input type="text" placeholder="Time" name="tour_starttime[]" id="tour_starttime" value="" class="form-control aiz-time-picker" data-minute-step="1"> 
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>                       
                                </div>
                            </td>';
                            /* <td class="text-right" width="200">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>                       
                                    <input type="text" placeholder="99.99" name="tour_price" id="tour_price" value="" class="form-control"> 
                                </div>
                            </td> */
                            $str.= '<td class="text-right">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeTour(\''.$row_id.'\')">-</button>
                                <button type="button" onClick="addTour()" class="btn btn-sm btn-info">+</button>
                            </td>
                        </tr>
                    </table>

                    <table class="table" style="background:#ebebeb">
                        <tr>
                            <td style="width:200px" width="200">
                                <table class="table">
                                    <tr>
                                        <td colspan="2">
                                            <h4 style="font-size:16px; font-weight:600">Quantities</h4>
                                        </td>
                                    </tr>';
                                    if($data->pricings) {
                                        $i=0;
                                        foreach($data->pricings as $pricing) {
                                            $num = 0;
                                            if($i++ == 0) {
                                                $num = 1;
                                                $subtotal = $subtotal + ($num * $pricing->price);
                                            }
                                            $str.= '<tr>
                                                <td width="60">
                                                    <input type="hidden" name="tour_pricing_id_'.$_tourId.'[]" value="'. $pricing->id .'" />
                                                    <input type="number" name="tour_pricing_qty_'.$_tourId.'[]" value="'. $num .'" style="width:60px" class="form-contorl">
                                                    <input type="hidden" name="tour_pricing_price_'.$_tourId.'[]" value="'. $pricing->price .'" /> 
                                                </td>
                                                <td>'. $pricing->label .' ('. price_format($pricing->price) .')</td>
                                            </tr>';
                                        }
                                    }
                                    
                                $str.= '</table>
                            </td>
                            <td style="width:200px">
                                <table class="table">
                                    <tr>
                                        <td colspan="2">
                                            <h4 style="font-size:16px; font-weight:600">Optional extras</h4>
                                        </td>
                                    </tr>';

                                    if ($data->addons) {
                                        foreach($data->addons as $extra) {
                                            $price = $extra->price;                                        
                                            $str.= '<tr>
                                                <td width="60">
                                                    <input type="hidden" name="tour_extra_id_'.$_tourId.'[]" value="'. $extra->id .'" />  
                                                    <input type="number" name="tour_extra_qty_'.$_tourId.'[]" value="0" style="width:60px" min="0" class="form-contorl text-center">
                                                    <input type="hidden" name="tour_extra_price_'.$_tourId.'[]" value="'. $price .'" /> 
                                                </td>
                                                <td>'. $extra->name .' ('. price_format($extra->price) .')</td>
                                            </tr>';
                                        }
                                    }
                                    
                                $str.= '</table>
                            </td>
                        </tr>
                    </table>
                    
                    <table class="table">';

                    $taxesfees = $data->taxes_fees;
                    if( $taxesfees ) {
                        foreach ($taxesfees as $key => $item) {                    
                            $price      = get_tax($subtotal, $item->fee_type, $item->tax_fee_value);
                            $tax        = $price ?? 0;
                            $subtotal   = $subtotal + $tax; 
                            
                            $str .= '<tr>
                                <td>'.$item->label.' ('. taxes_format($item->fee_type, $item->tax_fee_value) .')</td>
                                <td class="text-right">'. price_format($tax) .'</td>
                            </tr>';
                        }
                    }

                    $str .= '
                        <tr>
                            <th>Subtotal</th>
                            <th class="text-right">'. price_format($subtotal) .'</th>
                        </tr>
                    </table>
                    </div>';
        }
        return $str;
    }

    public function editAddon($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.addon', compact( 'data'));
    }

    public function editScheduling($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $schedule   = $data->schedule ? $data->schedule :  new TourSchedule();
        return view('admin.tours.feature.scheduling', compact( 'data', 'detail', 'schedule'));
    }

    public function editLocation($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.location', compact( 'data'));
    }

    public function editPickups($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.pickups', compact( 'data'));
    }

    public function editItinerary($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.itinerary', compact( 'data'));
    }

    public function editFaqs($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.faqs', compact( 'data'));
    }

    public function editInclusions($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.inclusions', compact( 'data'));
    }

    public function editExclusions($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.exclusions', compact( 'data'));
    }

    public function editTaxesfees($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.taxesfees', compact( 'data'));
    }

    public function editGallery($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        return view('admin.tours.feature.gallery', compact( 'data'));
    }

    public function editSeo($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        //$metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();
        $detail     = $data->detail ? $data->detail : new TourDetail();
        return view('admin.tours.feature.seo', compact( 'data', 'detail'));
    }

    public function editNotification($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();
        return view('admin.tours.feature.message.notification', compact( 'data', 'detail', 'metaData'));
    }

    public function editReminder($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();
        return view('admin.tours.feature.message.reminder', compact( 'data', 'detail', 'metaData'));
    }

    public function editFollowup($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();
        return view('admin.tours.feature.message.followup', compact( 'data', 'detail', 'metaData'));
    }

    public function editPaymentRequest($id)
    {
        $data       = Tour::findOrFail(decrypt($id));
        $detail     = $data->detail ? $data->detail : new TourDetail();
        $metaData   = $data->meta->pluck('meta_value', 'meta_key')->toArray();
        return view('admin.tours.feature.message.paymentrequest', compact( 'data', 'detail', 'metaData'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    }

    public function clone(Request $request, $id)
    {
        // Find the Tour
        $tour = Tour::with([
            'categories', 'galleries', 'tourtypes', 'addons', 'pickups',
            'itineraries', 'faqs', 'features', 'taxes_fees', 'detail',
            'location', 'schedule', 'pricings'
        ])->findOrFail(decrypt($id));

        // Duplicate the Main Tour Record
        $clonedTour = $tour->replicate(); // replicates attributes except primary key
        $clonedTour->title .= ' (Copy)'; // optionally append to differentiate
        $clonedTour->slug .= '-copy'; // optionally append to differentiate
        $clonedTour->unique_code = unique_code();
        $clonedTour->push(); // saves the tour and related hasOne/hasMany later

        // Attach BelongsToMany Relations
        $clonedTour->addons()->attach($tour->addons->pluck('id'));
        $clonedTour->categories()->attach($tour->categories->pluck('id'));
        $clonedTour->faqs()->attach($tour->faqs->pluck('id'));
        $clonedTour->galleries()->attach($tour->galleries->pluck('id'));
        $clonedTour->pickups()->attach($tour->pickups->pluck('id'));
        $clonedTour->itineraries()->attach($tour->itineraries->pluck('id'));
        $clonedTour->inclusions()->attach($tour->features->pluck('id'));
        $clonedTour->exclusions()->attach($tour->features->pluck('id'));
        $clonedTour->tourtypes()->attach($tour->tourtypes->pluck('id'));
        $clonedTour->taxes_fees()->attach($tour->taxes_fees->pluck('id'));

        // $pivotData = [];
        // foreach ($tour->galleries as $gallery) {
        //     $pivotData[$gallery->id] = [
        //         'is_main' => 1,
        //         // Add more fields if needed
        //     ];
        // }
        // $clonedTour->galleries()->attach($pivotData);

        // Duplicate HasOne Relations
        if ($tour->detail) {
            $newDetail = $tour->detail->replicate();
            $newDetail->tour_id = $clonedTour->id;
            $newDetail->save();
        }
        
        if ($tour->location) {
            $newLocation = $tour->location->replicate();
            $newLocation->tour_id = $clonedTour->id;
            $newLocation->save();
        }
        
        if ($tour->schedule) {
            $newSchedule = $tour->schedule->replicate();
            $newSchedule->tour_id = $clonedTour->id;
            $newSchedule->save();
        }

        // Duplicate HasMany Relations (e.g., pricings)
        foreach ($tour->pricings as $pricing) {
            $newPricing = $pricing->replicate();
            $newPricing->tour_id = $clonedTour->id;
            $newPricing->save();
        }

        return redirect()->route('admin.tour.index')->with('success', 'Tour has been cloned successfully');
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

                $tour->galleries()->updateExistingPivot($tour->galleries->pluck('id'), ['is_main' => 0]);
                // Check if the requested image is already attached to the tour
                if ($tour->galleries->contains($request->image)) {
                    // Just update pivot
                    //$tour->galleries()->updateExistingPivot($request->image, ['is_main' => 1]);
                    $tour->galleries()->detach($request->image);
                } 
                // Attach and set is_main = 1
                $tour->galleries()->attach($request->image, ['is_main' => 1]);
            }
            
        }

        return redirect()->back()->with([
            'success' => 'Tour created successfully',
            'active_tab' => '#basic_information'
        ]);
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
            'postal_code'   => 'required|max:7',
            'country'       => 'required|integer',
            'state'         => 'required|integer',
            'city'          => 'required|integer'
        ]);

        
        if ($validator->fails()) {
            // Validation failed
            return back()->withInput()->withErrors($validator)->with('error','Something went wrong!');
        }

        $tour  = Tour::findOrFail($id);
        $location = $tour->location;
        if(empty($location)) {
            $location = new TourLocation();        
            $location->tour_id      = $tour->id;
        }

        //echo $request->country; exit;
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

            return redirect()->back()->withInput()->with('success','Pickup location saved successfully.'); 
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
            //'canonical_url'      => 'required',
        ],
        [
            'meta_title.required'       => 'Please enter a meta title',
            'meta_description.required' => 'Please enter a meta description',
            //'meta_keywords.required'    => 'Please enter a long description',
            //'canonical_url.required'    => 'Please enter a canonical url',
        ]);
        
        // Update tour instance
        $tour  = Tour::findOrFail($id);
        $tour->status = 1;
        if($tour->save()) {            
            $tour_detail = TourDetail::where('tour_id', $tour->id)->first();
            $tour_detail->meta_title       = $request->meta_title;
            $tour_detail->meta_description = $request->meta_description;
            $tour_detail->meta_keywords    = $request->meta_keywords;
            //$tour_detail->canonical_url    = $request->canonical_url;
            if ($tour_detail->save() ) {
                return redirect()->back()->withInput()->with('success','Tour SEO data saved successfully.'); 
            }
        }

        return redirect()->back()->withInput()->with('error', 'Something went wrong!');
    }

    public function schedule_update(Request $request, $id) {
        //echo $request->session_start_time;   exit;
        $request->validate([
            'minimum_notice_num'        => 'required|integer|min:0',
            'minimum_notice_unit'       => 'required',
            'estimated_duration_num'    => 'required|integer|min:0',
            'estimated_duration_unit'   => 'required',
            'session_start_date'        => 'required|date_format:Y-m-d',
            'session_start_time'        => 'required',
            'session_end_date'          => 'required|date_format:Y-m-d',
            'session_end_time'          => 'required',

            'repeat_period'             => 'required|string|in:NONE,MINUTELY,HOURLY,DAILY,WEEKLY,MONTHLY,YEARLY', 
            'repeat_period_unit'        => 'required_if:repeat_period,MINUTELY,HOURLY|integer',
            'until_date'                => 'required_if:repeat_period,MINUTELY,HOURLY|date',  
        ],
        [
            'minimum_notice_num.required'       => 'Please enter a minimum notice number',
            'minimum_notice_unit.required'      => 'Please select a minimum notice unit',
            'estimated_duration_num.required'   => 'Please enter a minimum notice number',
            'estimated_duration_unit.required'  => 'Please select a minimum notice unit',
            'session_start_date.required'       => 'Please enter a start date',
            'session_start_time.required'       => 'Please enter a start time',
            'session_end_date.required'         => 'Please enter a to date',
            'session_end_time.required'         => 'Please enter a to time',
            'repeat_period.required'            => 'Please select a repeat',
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

        $schedule->tour_id                  = $tour->id;
        $schedule->minimum_notice_num       = $request->minimum_notice_num;
        $schedule->minimum_notice_unit      = $request->minimum_notice_unit;
        $schedule->estimated_duration_num   = $request->estimated_duration_num;
        $schedule->estimated_duration_unit  = $request->estimated_duration_unit;
        $schedule->session_start_date       = $request->session_start_date;
        $schedule->session_start_time       = $request->session_start_time;
        $schedule->session_end_date         = $request->session_end_date;
        $schedule->session_end_time         = $request->session_end_time;
        $schedule->sesion_all_day           = $request->sesion_all_day?1:0;
        $schedule->repeat_period            = $request->repeat_period;
        $schedule->repeat_period_unit       = $request->repeat_period_unit;
        $schedule->until_date               = $request->until_date;
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
            //'ItineraryOptions.*.datetime'        => 'required|string|max:255',
            'ItineraryOptions.*.address'     => 'required|string|max:255',
            'ItineraryOptions.*.description' => 'required',
        ],
        [
            'ItineraryOptions.*.title.required'      => 'Itinerary title is required',
            //'ItineraryOptions.*.datetime.required'   => 'Itinerary datetime is required',
            'ItineraryOptions.*.address.required'    => 'Itinerary address is required',
            'ItineraryOptions.*.description.required'=> 'Itinerary description is required',
        ]);

        //Save new itinerary
        $itineraryIds = [];
        foreach ($request->ItineraryOptions as $option) {
            // $itinerary = Itinerary::where('title', $option['title'])
            // ->where('datetime', $option['datetime'])
            // ->where('address', $option['address'])
            // ->first();

            $itinerary = Itinerary::find( $option['id'] ?? 0 );
            if (!$itinerary) {
                $itinerary = new Itinerary();
                //$itinerary->tour_id     = $tour->id;
                $itinerary->user_id     = auth()->user()->id;
            }
            $itinerary->title       = $option['title'] ?? null;
            $itinerary->datetime    = $option['datetime'] ?? null;
            $itinerary->address     = $option['address'] ?? null;
            $itinerary->description = $option['description'] ?? null;
            $itinerary->save();

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
            //$faq = Faq::where('question', $option['question'])->first();
            $faq = Faq::find( $option['id'] ?? 0 );
            if (!$faq) {
                $faq = new Faq();
                //$faq->tour_id     = $tour->id;
                $faq->user_id     = auth()->user()->id;
            }
            $faq->question = $option['question'] ?? null;
            $faq->answer   = $option['answer'] ?? null;
            $faq->save();

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
            //$feature = Inclusion::where('name', $option['name'])->first();
            $feature = Inclusion::find( $option['id'] ?? 0 );
            if (!$feature) {
                $feature = new Inclusion();
                //$feature->tour_id     = $tour->id;
                $feature->user_id     = auth()->user()->id;
            }
            $feature->name      = $option['name'] ?? null;
            
            if( $feature->save() ) {
                $featureIds[] = $feature->id;
            } 
            else {
                $featureIds[] = $feature->id;
            }
        }

        // Sycc faqs
        if ( !empty($featureIds) ) {
            $tour->inclusions()->sync($featureIds);
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
            //$feature = Exclusion::where('name', $option['name'])->first();

            $feature = Exclusion::find( $option['id'] ?? 0 );
            if (!$feature) {
                $feature = new Exclusion();
                $feature->user_id    = auth()->user()->id;
            }
            
            $feature->name      = $option['name'] ?? null;
            if( $feature->save() ) {
                $featureIds[] = $feature->id;
            } 
            else {
                $featureIds[] = $feature->id;
            }
        }

        // Sycc faqs
        if ( !empty($featureIds) ) {
            $tour->exclusions()->sync($featureIds);
        }

        return redirect()->back()->with('success','Exclusions saved successfully.');
    }

    public function taxfee_update(Request $request, $id) {
        $tour  = Tour::findOrFail($id);
        // Save tour types
        if ($request->has('taxes') && is_array($request->taxes)) {
            $tour->taxes_fees()->sync($request->taxes);

            return redirect()->back()->withInput()->with('success','Tax and fee saved successfully.'); 
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

            return redirect()->back()->withInput()->with('success','Gallery saved successfully.'); 
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

    public function followup_update(Request $request, $id) 
    {
        $tour = Tour::findOrFail($id);

        $allMetaKeys = [
            'email_review_followup', 'email_review_followup_delay', 'email_review_followup_delayUnit', 'email_review_followup_text',
            'email_recommend_followup', 'email_recommend_followup_delay', 'email_recommend_followup_delayUnit', 'email_recommend_followup_text',
            'email_coupon_followup', 'email_coupon_followup_delay', 'email_coupon_followup_delayUnit', 'email_coupon_followup_text',
            'sms_followup_customer', 'sms_followup_delay', 'sms_followup_delayUnit'
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


        return redirect()->back()->with('success', 'Tour followup updated successfully!');
    }

    public function payment_request_update(Request $request, $id) 
    {
        $tour = Tour::findOrFail($id);

        $allMetaKeys = [
            'email1_payment', 'email1_payment_type', 'email1_payment_percent', 'email1_payments_delay', 'email1_payments_date', 'email1_payment_typedate',
            'email2_payment', 'email2_payment_type', 'email2_payment_percent', 'email2_payments_delay', 'email2_payments_date', 'email2_payment_typedate',
            'email3_payment', 'email3_payment_type', 'email3_payment_percent', 'email3_payments_delay', 'email3_payments_date', 'email3_payment_typedate',
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


        return redirect()->back()->with('success', 'Tour payment request updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tour = Tour::where('id', decrypt($id))->first();
        $tour->title .= '-deleted';
        $tour->slug .= '-deleted';
        $tour->save();
        if ($tour->delete()) {
            return redirect()->route('admin.tour.index')->with('success', 'Tour info has been deleted successfull');
        } else {
            return back()->route('admin.tour.index')->with('error', 'Sorry! Something went wrong.');;
        }
    }

    /***
     Add focus keyword [SS]
    */
    public function add_focus_keyword(Request $request, $id)
    {
        
        // Update tour instance
        $tour  = Tour::findOrFail($id);
        $tour->status = 1;
        if($tour->save()) {            
            $tour_detail = TourDetail::where('tour_id', $tour->id)->first();
            $tour_detail->focus_keyword       = $request->focus_keyword;
            if ($tour_detail->save() ) {
                return redirect()->back()->withInput()->with('success','Focus key saved successfully.'); 
            }
        }
    }
}
