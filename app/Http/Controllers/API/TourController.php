<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderTour;
use App\Models\ScheduleDeleteSlot;
use App\Models\Tour;
use App\Models\TourReview;
use App\Models\TourSchedule;
use App\Models\TourScheduleRepeats;
use App\Models\TourSpecialDeposit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Dompdf\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tour::select([
                'id', 'title', 'slug', 'unique_code', 'price',
                'coupon_type', 'coupon_value', 'offer_ends_in'
            ])
            ->with([
                'galleries:id,file_name,medium_name,thumb_name',
                'mainImage:id,file_name,medium_name,thumb_name',
                'schedule:id,tour_id,estimated_duration_num,estimated_duration_unit',
                'categories:id',
                'location:id,city_id,state_id,country_id',
            ])
            ->where('status', 1)
            ->whereHas('schedules', function ($sq) {
                $sq->whereDate('until_date', '>=', now()->toDateString());
            })
            ->whereNull('deleted_at');
        // Filters
        $query->when($request->title, fn($q, $title) => $q->where('title', 'like', "%$title%"))
            ->when($request->q, fn($q, $qstr) => $q->where('title', 'like', "%$qstr%"));
            // ->when($request->slug, fn($q, $slug) => $q->where('slug', 'like', "%$slug%"));

        if ($request->categories) {
            $categories = explode(',', $request->categories);
            $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categories));
        }

        if ($request->city_id) {
            $type = $request->input('type');
            if($type == 'c3') {
                $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->city_id));
            }
            else {
                $query->whereHas('location', function ($q) use ($request, $type) {
                    match($type) {
                        'c1' => $q->where('city_id', $request->city_id),
                        's1' => $q->where('state_id', $request->city_id),
                        'c2' => $q->where('country_id', $request->city_id),
                        default => null
                    };
                });
            }
        }

        if ($request->min_price && $request->max_price) {
            $query->whereBetween('price', [(float)$request->min_price, (float)$request->max_price]);
        } elseif ($request->min_price) {
            $query->where('price', '>=', (float)$request->min_price);
        } elseif ($request->max_price) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        // Sorting
        // match($request->input('order_by')) {
        //     'lowtohigh' => $query->orderBy('price', 'ASC'),
        //     'hightolow' => $query->orderBy('price', 'DESC'),
        //     default     => $query->orderBy('sort_order', 'ASC'),
        // };
        // match ($request->input('order_by')) {
        //     'lowtohigh' => $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END, sort_order ASC')
        //                         ->orderBy('price', 'ASC'),

        //     'hightolow' => $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END, sort_order ASC')
        //                         ->orderBy('price', 'DESC'),

        //     default     => $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END, sort_order ASC'),
        // };


        $orderBy = strtolower($request->input('order_by', ''));

        if ($request->input('order_by') === 'lowtohigh') {
            // $query->orderByRaw('(CASE WHEN sort_order > 0 THEN 0 ELSE 1 END) ASC')
            //       ->orderBy('price', 'ASC');
            $query->orderBy('price', 'ASC');
        } elseif ($request->input('order_by') === 'hightolow') {
            // $query->orderByRaw('(CASE WHEN sort_order > 0 THEN 0 ELSE 1 END) ASC')
            //       ->orderBy('price', 'DESC');
            $query->orderBy('price', 'DESC');
        } else {
            $query->orderByRaw('(CASE WHEN sort_order > 0 THEN 0 ELSE 1 END) ASC')
                  ->orderBy('sort_order', 'ASC'); // Only sort_order for default
        }


        // Cache paginated
        $page = $request->get('page', 1);
        $cacheKey = 'tour_list_' . md5(json_encode($request->all()) . '_page_' . $page);

        // dd(getFullSql($query));

        $paginated = Cache::tags(['tours'])->remember($cacheKey, 86400, fn() => $query->paginate(12));
        // $paginated = Cache::remember($cacheKey, 86400, function () use ($query) {
        //     return $query->paginate(12);
        // });

        // Transform response
        $items = $paginated->map(fn($d) => [
            'id'              => $d->id,
            'title'           => $d->title,
            'slug'            => $d->slug,
            'unique_code'     => $d->unique_code,
            'all_images'      => $d->formatted_images,
            'price'           => price_format($d->price),
            'original_price'  => $d->discounted_data['original_price'],
            'discount'        => $d->discounted_data['discount'],
            'discount_type'   => $d->discounted_data['discount_type'],
            'discounted_price'=> $d->discounted_data['discounted_price'],
            'duration'        => $d->duration,
            'rating'          => randomFloat(4, 5),
            'comment'         => rand(50, 100),
            'offer_ends_in'   => $d->offer_ends_in,
            // 'meta_title'      => $paginated->total().' Things To Do In ' .ucfirst( $d->title ).' | ' .env('APP_NAME') ,
            // 'meta_description'=> 'Discover tour in '.ucfirst( $d->title ).'. Enjoy unforgettable experiences, attractions, and adventures with TourBeez.',
            
        ]);

        return response()->json([
            'status'         => true,
            'data'           => $items,
            'requested'      => $request->all(),
            'current_page'   => $paginated->currentPage(),
            'last_page'      => $paginated->lastPage(),
            'per_page'       => $paginated->perPage(),
            'total'          => $paginated->total(),
            'next_page_url'  => $paginated->nextPageUrl(),
            'prev_page_url'  => $paginated->previousPageUrl(),
        ]);
    }
    
    /**
     * Fetch a single tour.
     */
    public function fetch_one(Request $request, $slug)
    {
        //$slug = $request->input('slug');
        $cacheKey = 'tour_detail_' . $slug;

        $tour = Cache::remember($cacheKey, 86400, function () use ($slug) {
            return Tour::where('slug', $slug)
                ->where('status', 1)
                ->whereNull('deleted_at')
                //->with('main_image') // eager load image if needed
                ->with([
                    'galleries',
                    'meta',
                    'categories',
                    'tourtypes',
                    'addons',
                    'pickups',
                    'itineraries',
                    'itinerariesAll',
                    'faqs',
                    'inclusions',
                    'optionals', 
                    'exclusions',
                    'features',
                    'taxes_fees',
                    'detail',
                    'user',
                    'location',
                    'schedule',
                    // 'pricings',
                    'category',
                ])
                ->first();
        });

        if (!$tour) {
            return response()->json(['status' => false, 'message' => 'Tour not found'], 404);
        }

        $galleries = [];
        foreach ($tour->galleries as $item) {
            $image      = uploaded_asset($item->id);
            $medium_url = str_replace($item->file_name, $item->medium_name, $image);
            $thumb_url  = str_replace($item->file_name, $item->thumb_name, $image);

            $galleries[] = [
                'original_url'  => $image,
                'medium_url'    => $medium_url,
                'thumb_url'     => $thumb_url
            ];
        }

        $addons = [];
        foreach ($tour->addons as $addon) {
            $image      = uploaded_asset($addon->image);
            $medium_url = str_replace($item->file_name, $item->medium_name, $image);
            $thumb_url  = str_replace($item->file_name, $item->thumb_name, $image);

            $addons[] = [
                'id'            => $addon->id,
                'name'          => $addon->name,
                'description'   => $addon->description,
                'price'         => $addon->price,
                'original_url'  => $image,
                'medium_url'    => $medium_url,
                'thumb_url'     => $thumb_url,
            ];
        }

        $breadcrumbs[] = [
            'url' => '/',
            'label'=> 'Home'
        ];
        $breadcrumbs[] = [
            'url' => '/destinations',
            'label'=> 'Destinations'
        ];
        if($tour->location) {
            $location = $tour->location;
            if($location->country) {
                $breadcrumbs[] = ['url' => '/'.Str::slug($location->country->name).'/'.$location->country->id.'/c2', 'label' => 'Things To Do in '.$location->country->name];
            }
            if($location->state) {
                $breadcrumbs[] = ['url' => '/'.Str::slug($location->state->name).'/'.$location->state->id.'/s1', 'label' => 'Things To Do in '.$location->state->name];
            }
            if($location->city) {
                $breadcrumbs[] = ['url' => '/'.Str::slug($location->city->name).'/'.$location->city->id.'/c1', 'label' => 'Things To Do in '.$location->city->name];
            }
        }
        $breadcrumbs[] = [
            'url' => '',
            'label'=> $tour->title
        ];

        $pickups = [];

        // dd($tour->pickups);

        // return response()->json([
        //     'status' => true,
        //     'data'   =>$tour->pickups
        // ]);
        if(!empty($tour->pickups) && isset($tour->pickups[0]) && $tour->pickups[0]?->name === 'No Pickup') {
            $pickups[] = 'No Pickup';
        }
        else if(!empty($tour->pickups) && isset($tour->pickups[0]) && $tour->pickups[0]?->name === 'Pickup') {
            $pickups[0] = 'Pickup';
            
            $comment = \DB::table('pickup_tour')
                                            ->where('tour_id', $tour->id)
                                            ->where('pickup_id', $tour->pickups[0]?->id)  // a single pickup ID
                                            ->value('comment');


            $pickups[1] = $comment ?? "Enter the pickup location";
        }
        else if (!empty($tour->pickups) && isset($tour->pickups[0])) {
            $pickups = $tour->pickups[0]?->locations ?? [];
        }

        $original_price   = $tour->price;
        $discounted_price = $tour->price;

        if ($tour->coupon_value && $tour->coupon_value > 0) {
            if ($tour->coupon_type === 'fixed') {
                // Original price = price + coupon value
                $original_price   = $tour->price + $tour->coupon_value;
                $discounted_price = $tour->price;
            } elseif ($tour->coupon_type === 'percentage') {
                // Original price = inflated by coupon percentage
                // $original_price   = $tour->price * (1 + ($tour->coupon_value / 100));
                $original_price = $tour->price / (1 - ($tour->coupon_value / 100));
                $original_price = round($original_price);
                $discounted_price = $tour->price;
            }
        }

        if ($tour) {
            // 💡 You can now format or transform fields as needed
            // return $this->getNextAvailableDate($tour->id);
            // return $this->getDisabledTourDates($tour->id);
            // return $this->getDisabledTourDates($tour->id);
            $formattedTour = [
                'id'            => $tour->id,
                'title'         => $tour->title,
                'price'         => format_price($tour->price), // formatted price
                'original_price'=> $original_price, // without formatted price
                'price_type'    => $tour->price_type,
                'unique_code'   => $tour->unique_code,
                'slug'          => $tour->slug,
                'order_email'   => $tour->order_email,
                'features'      => $tour->features,
                'meta'          => $tour->meta,
                'pickups'       => $pickups,
                'categories'    => $tour->categories,
                'tourtypes'     => $tour->tourtypes,
                'itineraries'   => $tour->itineraries,
                'faqs'          => $tour->faqs,
                'inclusions'    => $tour->inclusions,
                'optionals'     => $tour->optionals,
                'exclusions'    => $tour->exclusions,
                'taxes_fees'    => $tour->taxes_fees,
                'detail'        => $tour->detail,
                'location'      => $tour->location,
                'breadcrumbs'   => $breadcrumbs,
                'category'      => $tour->category,
                'galleries'     => $galleries,
                'addons'        => $addons,
                'offer_ends_in' => $tour->offer_ends_in,
                'rating'          => randomFloat(4, 5),
                'comment'         => rand(50, 100),
                // 'pricings'      => $tour->pricings,
                // 'tour_special_deposits'        => $tour->specialDeposit,
                // 'itinerariesAll'=> $tour->itinerariesAll,
                // 'schedule'      => $tour->schedule,

                'discount'              =>  $tour->coupon_value,
                'discount_type'         =>  strtoupper($tour->coupon_type),
                'discounted_price'      => $discounted_price,
                'tour_start_date'       => [],
                'disabled_tour_dates'   => [],
                'review'                => $this->getReview($tour->id)
            ];           
        }

        return response()->json([
            'status' => true,
            'data'   => $formattedTour
        ]);
    }

    /**
     * Fetch booking related info for a tour.
     */
    public function fetch_booking(Request $request, $slug)
    {
        // Remove query logging to reduce overhead in production
        // \DB::enableQueryLog();

        $cacheKey = 'tour_booking_' . $slug;

        // Optimize query: Select only necessary fields and reuse eager-loaded schedules
        // $tour = Cache::remember($cacheKey, 86400, function () use ($slug) {
            $tour =  Tour::select([
                    'id', 'title', 'slug', 'price', 'price_type',
                    'coupon_value', 'coupon_type'
                ])
                ->where('slug', $slug)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->with([
                    'detail', // Select specific fields
                    'schedules' => function ($query) {
                        $query->select([
                            'id', 'tour_id', 'session_start_date', 'until_date',
                            'session_start_time', 'session_end_time', 'repeat_period',
                            'repeat_period_unit', 'minimum_notice_num', 'minimum_notice_unit',
                            'estimated_duration_num', 'estimated_duration_unit', 'sesion_all_day'
                        ])->orderBy('session_start_date');
                    },
                    'pricings' // Select specific fields
                ])
                ->first();
        // });

        if (!$tour) {
            return response()->json(['status' => false, 'message' => 'Tour not found'], 404);
        }
        // dd($tour, $tour->schedules);
        // Reuse eager-loaded schedules instead of querying again
        $schedules = $tour->schedules;

        // Cache next available date and disabled dates separately
        // $nextAvailableCacheKey = 'tour_next_available_' . $tour->id . '_' . Carbon::today()->toDateString();
        // $disabledDatesCacheKey = 'tour_disabled_dates_' . $tour->id . '_' . Carbon::today()->toDateString();

        // $tour_start_date = Cache::remember($nextAvailableCacheKey, 3600, function () use ($tour, $schedules) {
        //     return $this->getNextAvailableDate($tour->id, $schedules);
        // });

        // $disabled_dates = Cache::remember($disabledDatesCacheKey, 3600, function () use ($tour, $schedules) {
        //     return $this->getDisabledTourDates($tour->id, $schedules);
        // });

        $tour_start_date = $this->getNextAvailableDate($tour->id, $schedules);
        $disabled_dates =  $this->getDisabledTourDates($tour->id, $schedules);
        // $disabled_dates =  [];// $this->getDisabledTourDates_fromdb($tour->id);

        
        // Calculate discount pricing (unchanged)
        $original_price   = $tour->price;
        $discounted_price = $tour->price;

        if ($tour->coupon_value && $tour->coupon_value > 0) {
            if ($tour->coupon_type === 'fixed') {
                // Original price = price + coupon value
                $original_price   = $tour->price + $tour->coupon_value;
                $discounted_price = $tour->price;
            } elseif ($tour->coupon_type === 'percentage') {
                // Original price = inflated by coupon percentage
                // $original_price   = $tour->price * (1 + ($tour->coupon_value / 100));
                $original_price = $tour->price / (1 - ($tour->coupon_value / 100));
                $original_price = round($original_price);
                $discounted_price = $tour->price;
            }
        }

        // Prepare response data (unchanged)
        $data = [
            'id'                   => $tour->id,
            'title'                => $tour->title,
            'slug'                 => $tour->slug,
            'price_type'           => $tour->price_type,
            'pricings'             => $tour->pricings,
            'detail'               => $tour->detail,
            'original_price'       => $original_price,
            'discount'             => $tour->coupon_value,
            'discount_type'        => strtoupper($tour->coupon_type),
            'discounted_price'     => $discounted_price,
            'tour_start_date'      => $tour_start_date,
            'disabled_tour_dates'  => $disabled_dates,
            'have_sub_tour'        => $tour->subTours()->exists(),
        ];
        
        // $readmePath = base_path('WELCOME.md');

        // return view('welcome', [
        //     'readmeContent' => \Illuminate\Support\Str::markdown(file_get_contents($readmePath)),
        // ]);

        // Remove misplaced view return and return JSON
        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    private function getDisabledTourDates_fromdb(int $tourId): array
    {
        // ✅ Load the precomputed meta row for this tour
        $meta = \DB::table('tour_schedule_meta')
            ->where('tour_id', $tourId)
            ->first();

        if (!$meta) {
            return [
                'disabled_tour_dates' => [],
                'start_date' => null,
                'until_date' => null,
            ];
        }

        $globalStart   = Carbon::parse($meta->start_date);
        $globalEnd     = Carbon::parse($meta->until_date);
        $disabledDates = json_decode($meta->disabled_dates, true) ?? [];
        // dd($tourId, $disabledDates);
        // ✅ Apply delete slots
        $storeDeleteSlot = $this->fetchDeletedSlot($tourId);
        $deleteTypes = collect($storeDeleteSlot)->pluck('delete_type');

        if ($deleteTypes->contains('all')) {
            return [
                'disabled_tour_dates' => [], // fully blocked
                'start_date' => $globalStart->toDateString(),
                'until_date' => Carbon::yesterday()->toDateString(),
            ];
        }

        if ($storeDeleteSlot->where('delete_type', 'after')->isNotEmpty()) {
            $minAfterDate = $storeDeleteSlot
                ->where('delete_type', 'after')
                ->pluck('slot_date')
                ->min();

            $globalEnd = Carbon::parse($minAfterDate);
        }

        foreach ($storeDeleteSlot->where('delete_type', 'single_date') as $slot) {
            $disabledDates[] = Carbon::parse($slot->slot_date)->toDateString();
        }

        // ✅ Deduplicate & sort
        $disabledDates = array_values(array_unique($disabledDates));
        sort($disabledDates);

        return [
            'disabled_tour_dates' => $disabledDates,
            'start_date' => $globalStart->toDateString(),
            'until_date' => $globalEnd->toDateString(),
        ];
    }


    /**
     * Fetch booking related info for a tour.
     */
    public function fetch_sub_tours(Request $request, $id, $date)
    {
        $subTours = Tour::select([
                            "id",
                            "user_id",
                            "parent_id",
                            "title",
                            "slug",
                            "unique_code",
                            "price",
                            "price_type"
                        ])
                        ->where('parent_id', $id)
                        ->where('status', 1)
                        ->whereNull('deleted_at')
                        ->with(['detail:id,tour_id,description', 'pricings'])
                        ->get();

        if ($subTours->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No sub tours found'], 404);
        }

        // 👇 Reuse OrderController@getSessionTimes
        $orderController = app(\App\Http\Controllers\API\OrderController::class);

        $subTours->map(function ($tour) use ($date, $orderController) {

            $req = new \Illuminate\Http\Request([
                'tour_id' => $tour->id,
                'date'    => $date,
            ]);

            // Call existing method
            $response = $orderController->getSessionTimes($req);
            $sessions = $response->getData(true);

            $tour->sessions = $sessions;
            return $tour;
        });

        return response()->json([
            'status' => true,
            'data'   => $subTours
        ]);
    }


    /**
     * Fetch a deposit rule by tour id.
     */
    public function fetch_deposit_rule($id)
    {
        $cacheKey = 'deposit_rule_' . $id;

        $depositRule = Cache::remember($cacheKey, 86400, function () use ($id) {
            return TourSpecialDeposit::where('tour_id', $id)->first();
        });

        // If no rule found for specific tour, check global rule
        if (!$depositRule || ($depositRule && $depositRule->use_deposit == 0)) {
            $depositRule = Cache::remember('deposit_rule_global', 86400, function () {
                return TourSpecialDeposit::where('type', 'global')->first();
            });
        }

        // ✅ Booking fee data (always included)

        if($depositRule && $depositRule->price_booking_fee){
            $bookingFees = [
                'price_booking_fee'     => $depositRule->price_booking_fee,
                'tour_booking_fee'      => $depositRule->tour_booking_fee,
                'tour_booking_fee_type' => $depositRule->tour_booking_fee_type,
            ];
        } else{
            $bookingFees = [
                'price_booking_fee'     => get_setting('price_booking_fee'),
                'tour_booking_fee'      => get_setting('tour_booking_fee'),
                'tour_booking_fee_type' => get_setting('tour_booking_fee_type'),
            ];
        }
        

        if (!$depositRule) {
            return response()->json([
                'status' => false,
                'message' => 'Tour deposit rule not found (including global rule)',
                'data' => [
                    'deposit_rule' => null,
                    'booking_fees' => $bookingFees
                ]
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'deposit_rule' => $depositRule,
                'booking_fees' => $bookingFees
            ]
        ]);
    }



    /** 
     * Search home page tour  
     */
    public function search(Request $request) 
    {
        
        $search = $request->input('q', '');
        $date = $request->input('date', '');

        // Build cache key
        $cacheKey = 'search_tours_' . md5($search . '_' . $date);

        // $cities = City::where('status', 'active')
        //     ->when($search, function ($query, $search) {
        //         $query->where(function ($q) use ($search) {
        //             $q->where('name', 'LIKE', '' . $search . '%');
        //         });
        //     })
        //     ->orderBy('name', 'asc')
        //     ->limit(2)
        //     ->get();

        $cities = DB::table('tour_locations as tl')
                    ->join('tours as t', 't.id', '=', 'tl.tour_id')
                    ->join('cities as c', 'c.id', '=', 'tl.city_id')
                    ->join('states as s', 's.id', '=', 'c.state_id')
                    ->join('countries as cc', 'cc.id', '=', 's.country_id')
                    ->join('uploads as u', 'u.id', '=', 'c.upload_id')
                    ->select('c.id', 'c.name', 's.name as state_name', 'cc.name as country_name', 'u.file_name as image')
                    ->groupBy('c.id', 'c.name')
                    ->orderBy('name', 'asc')
                    ->where('c.upload_id', '>=', 1)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('tour_schedules as ts')
                            ->whereColumn('ts.tour_id', 't.id')
                            ->where('ts.until_date', '>=', DB::raw('CURDATE()'));
                    })
                    ->when($search, function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('c.name', 'LIKE', '' . $search . '%');
                        });
                    })
                    ->limit(2)
                    ->get();
            
        $categories = Category::orderBy('name', 'asc')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '' . $search . '%');
                });
            })
        ->limit(3)
        ->get();    
        
        $total_cities       = $cities->count();
        $total_categories   = $categories->count();
        $total_tours        = 8 - ($total_cities + $total_categories);

        // $total_tours        = 8 - ($total_cities);
        $tours = Cache::remember($cacheKey, now()->addMinutes(20), function () use ($search, $total_tours) {
            //return 
            return Tour::with(['location' => function ($query) {
                    $query->select('id', 'tour_id', 'address');
                }])
                ->select('id', 'title', 'slug', 'unique_code', 'price')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'LIKE', '%' . $search . '%');
                    });
                })
                ->where('status', 1)
                ->orderBy('title', 'asc')
                ->limit($total_tours)
                ->get();

        });

        $data = [];
        if($total_cities>0) {
            foreach($cities as $city) {
                //$data[] = ['icon'=>'city', 'title' => $this->highlightMatch($city->name, $search), 'slug' => '/'.Str::slug($city->name).'/'.$city->id.'/c1', 'address' => ucfirst($city->state?->name).', '.ucfirst($city->state?->country?->name)];
                $data[] = ['icon'=>'city', 'title' => $this->highlightMatch($city->name, $search), 'slug' => '/'.Str::slug($city->name).'/'.$city->id.'/c1', 'address' => ucfirst($city->state_name).', '.ucfirst($city->country_name)];
            }
        }
        if($total_categories>0) {
            foreach($categories as $category) {
                $data[] = ['icon'=>'category', 'title' => $this->highlightMatch($category->name, $search), 'slug' => '/'.$category->slug.'/'.$category->id.'/c3', 'address' => ''];
            }
        }
        if($tours->count()>0) {
            foreach($tours as $tour) {
                $image_id = $tour->main_image->id ?? 0;
                $image  = uploaded_asset($image_id, 'thumb');
                $data[] = ['icon'=>$image, 'title' => $this->highlightMatch($tour->title, $search), 'slug' => '/tour/'.$tour->slug, 'address' => $tour->location?->address];
            }
        }
        
        if (!$data) {
            return response()->json(['status' => false, 'data' => [], 'message' => 'No records found!']);
        }

        // Return the transformed data along with pagination info
        return response()->json([
            'status'  => true,
            'data'    => $data,
        ]);
    }

    public function highlightMatch($string, $keyword) {
        $string = ucfirst($string);
        return preg_replace("/(" . preg_quote($keyword, '/') . ")/i", '<mark>$1</mark>', $string);
    }
    
    /**
     * Fetch tours by category id.
     */
    public function toursByCategory(Request $request)
    {
        $category_id = $request->input('category_id');

        if (!$category_id) {
            return response()->json([
                'status' => false,
                'message' => 'category_id is required'
            ], 400);
        }

        $page = $request->get('page', 1);
        $order_by = $request->input('order_by');

        $cacheKey = 'category_tours_' . $category_id . '_order_' . $order_by . '_page_' . $page;

        $paginated = Cache::remember($cacheKey, 86400, function () use ($category_id, $order_by) {
            $query = Tour::where('status', 1)
                ->whereNull('deleted_at')
                ->whereHas('categories', function ($q) use ($category_id) {
                    $q->where('categories.id', $category_id);
                });

            if ($order_by === 'lowtohigh') {
                $query->orderBy('price', 'ASC');
            } elseif ($order_by === 'hightolow') {
                $query->orderBy('price', 'DESC');
            } else {
                $query->orderBy('id', 'DESC');
            }

            return $query->paginate(12);
        });

        // Format paginated result
        $items = [];
        foreach ($paginated->items() as $d) {
            $galleries = [];

            if (count($d->galleries) > 0) {
                foreach ($d->galleries as $g) {
                    $image      = uploaded_asset($g->id);
                    $medium_url = str_replace($g->file_name, $g->medium_name, $image);
                    $thumb_url  = str_replace($g->file_name, $g->thumb_name, $image);
                    $galleries[] = [
                        'original_image' => $image,
                        'medium_image'   => $medium_url,
                        'thumb_image'    => $thumb_url,
                    ];
                }
            } else {
                $image      = uploaded_asset($d->main_image->id);
                $medium_url = str_replace($d->main_image->file_name, $d->main_image->medium_name, $image);
                $thumb_url  = str_replace($d->main_image->file_name, $d->main_image->thumb_name, $image);
                $galleries[] = [
                    'original_image' => $image,
                    'medium_image'   => $medium_url,
                    'thumb_image'    => $thumb_url,
                ];
            }

            $duration = $d->schedule?->estimated_duration_num . ' ' ?? '';
            $duration .= ucfirst($d->schedule?->estimated_duration_unit ?? '');

            $items[] = [
                'id'             => $d->id,
                'title'          => $d->title,
                'slug'           => $d->slug,
                'unique_code'    => $d->unique_code,
                'all_images'     => $galleries,
                'price'          => price_format($d->price),
                'original_price' => $d->price,
                'duration'       => strtolower($duration),
                'rating'         => randomFloat(4, 5),
                'comment'        => rand(50, 100),
            ];
        }

        return response()->json([
            'status'         => true,
            'data'           => $items,
            'requested'      => $request->all(),
            'current_page'   => $paginated->currentPage(),
            'last_page'      => $paginated->lastPage(),
            'per_page'       => $paginated->perPage(),
            'total'          => $paginated->total(),
            'next_page_url'  => $paginated->nextPageUrl(),
            'prev_page_url'  => $paginated->previousPageUrl(),
        ]);
    }

    private function getNextAvailableDate($tourId, $schedules = null)
    {


        $today = Carbon::today();

        if ($schedules === null) {
            $schedules = TourSchedule::where('tour_id', $tourId)
                ->where(function ($query) use ($today) {
                    $query->orWhere(function ($q) use ($today) {
                        $q->whereDate('session_start_date', '<=', $today)
                        ->whereDate('until_date', '>=', $today);
                    })
                    ->orWhereDate('session_start_date', '>=', $today);
                })
                ->get();
        } else {
            // ✅ If schedules already passed in, filter in-memory
            $schedules = $schedules->filter(function ($s) use ($today) {
                return (
                    ($s->session_start_date <= $today && $s->until_date >= $today) ||
                    ($s->session_start_date >= $today)
                );
            });
        }

        $nextDates = [];

        $allRepeats = TourScheduleRepeats::whereIn('tour_schedule_id', $schedules->pluck('id')->toArray())
        ->get()
        ->groupBy('tour_schedule_id')
        ->map(function ($items) {
            return $items->groupBy('day'); // group inside each schedule by day
        });
        
        foreach ($schedules as $schedule) {
            // dd($schedule->repeat_period);
            if($schedule->repeat_period == 'NONE'){
                if($today->lte(Carbon::parse($schedule->session_start_date))){
                    return ['date' => Carbon::parse($schedule->session_start_date)->toDateString()];
                } 
                return ['date' => ""];            
            }   

            $nextDate = $this->calculateNextDate($schedule, $today, $allRepeats);
            
            if ($nextDate) {
                $nextDates[] = $nextDate;
            }
        }   


        if (!empty($nextDates)) {
            usort($nextDates, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
            return $nextDates[0];
        }

        return null;
    }


    private function calculateNextDate($schedule, Carbon $today, $allRepeats = [])
    {
        $interval   = $schedule->repeat_period_unit ?? 1;
        $repeatType = $schedule->repeat_period;

        if ($repeatType === 'NONE') {
            return null;
        }

        $scheduleStartDate = Carbon::parse($schedule->session_start_date);
        $scheduleEndDate   = Carbon::parse($schedule->until_date);

        // Start from today or schedule start (whichever is later)
        $next = $today->lt($scheduleStartDate) ? $scheduleStartDate->copy() : $today->copy();

        // Prefetched repeats for this schedule (grouped by day)
        $repeats = $allRepeats[$schedule->id] ?? collect();
        
        while ($next->lte($scheduleEndDate)) {

            $dayName = $next->format('l');
            $allowedDay = true;

            // Restrict to allowed days if WEEKLY/HOURLY/MINUTELY
            if (in_array($repeatType, ['WEEKLY', 'HOURLY', 'MINUTELY'])) {
                $allowedDay = $repeats->has($dayName);

                if ($repeatType === 'WEEKLY' && $allowedDay) {
                    $weekDiff = $scheduleStartDate->diffInWeeks($next);
                    if ($weekDiff % $interval !== 0) {
                        $allowedDay = false;
                    }
                }
            }

            // If day is allowed → check valid slot
            if ($allowedDay && $this->hasValidSlot($schedule, $next, $repeats)) {
                return ['date' => $next->toDateString()];
            }

            // Step forward depending on repeat type
            switch ($repeatType) {
                case 'DAILY':    $next->addDays($interval); break;
                case 'WEEKLY':   $next->addDay(); break;
                case 'MONTHLY':  $next->addMonths($interval); break;
                case 'YEARLY':   $next->addYears($interval); break;
                case 'HOURLY':   $next->addHours($interval); break;
                case 'MINUTELY': $next->addMinutes($interval); break;
                default: return null;
            }
        }

        return null;
    }


    private function hasValidSlot($schedule, Carbon $date, $repeats = [], $durationMinutes = 30)
    {
        $repeatType = $schedule->repeat_period;
        $dayName    = $date->format('l');

        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_start_time);
        $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_end_time);

        // override times if repeat slots exist (for hourly/minutely)
        if (($repeatType === 'HOURLY' || $repeatType === 'MINUTELY') && $repeats->has($dayName)) {
            $slot = $repeats[$dayName]->first();
            $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $slot->start_time);
            $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $slot->end_time);
        }

        // apply minimum notice
        $minutes = $schedule->minimum_notice_unit === "HOURS"
            ? $schedule->minimum_notice_num * 60
            : $schedule->minimum_notice_num;

        $earliestAllowed = now()->addMinutes($minutes);

        if ($endTime->lt($earliestAllowed)) {
            return false;
        }

        if (in_array($repeatType, ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'])) {
            return $startTime->gte($earliestAllowed);
        }

        while ($startTime->lte($endTime)) {
            if ($startTime->gte($earliestAllowed)) {
                return true;
            }
            $startTime->addMinutes($durationMinutes);
        }

        return false;
    }



    private function getSlotsForDate($schedule, $date, $durationMinutes = 30, $minimumNoticePeriod = 0)
    {
        $slots = [];

        // Parse start and end times for the given date
        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_start_time);
        $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_end_time);

        // Calculate the earliest slot allowed
        $earliestAllowed = now()->addMinutes($minimumNoticePeriod);

        // Generate slots at given intervals
        while ($startTime->lte($endTime)) {
            if ($startTime->gte($earliestAllowed)) {
                $slots[] = $startTime->format('g:i A'); // Keep same format as generateSlots()
            }
            $startTime->addMinutes($durationMinutes);
        }

        return $slots;
    }


    private function minutesFromUnit(?int $num, ?string $unit): int
    {
        $num  = (int)($num ?? 0);
        $unit = strtolower((string)$unit);

        return match ($unit) {
            'minute', 'minutes' => $num,
            'hour', 'hours'     => $num * 60,
            'day', 'days',
            'daily'             => $num * 60 * 24,
            'month', 'months',
            'monthly'           => $num * 60 * 24 * 30,   // same as your code
            'year', 'years',
            'yearly'            => $num * 60 * 24 * 365,  // same spirit as your code
            default             => 0
        };
    }

    /**
     * Fast boolean: does *any* valid slot exist on $date?
     * Mirrors your getSessionTimes logic but avoids generating slots.
     *
     * @param  $schedule  TourSchedule
     * @param  $date      Carbon (Y-m-d for the “selected” day)
     * @param  $repeatsByDay  array<string, Collection<TourScheduleRepeats>>  // optional prefetch
     */


    private function getDisabledTourDates(int $tourId, $schedules = null): array
    {
        if ($schedules === null) {
            $schedules = TourSchedule::where('tour_id', $tourId)
                ->orderBy('session_start_date')
                ->get();
        }

        if ($schedules->isEmpty()) {
            return [
                'disabled_tour_dates' => [],
                'per_schedule' => [],
            ];
        }

        $globalStart = Carbon::parse($schedules->min('session_start_date'));
        $globalEnd   = Carbon::parse($schedules->max('until_date'));

        // ✅ Prefetch deleted slots once
        $storeDeleteSlot = $this->fetchDeletedSlot($tourId);
        $deleteTypes = collect($storeDeleteSlot)->pluck('delete_type');

        if ($deleteTypes->contains('all')) {
            $globalEnd = Carbon::yesterday();
            return [
                'disabled_tour_dates' => [], // same as original
                'per_schedule' => [],
                'start_date' => $globalStart->toDateString(),
                'until_date' => $globalEnd->toDateString(),
            ];
        } elseif ($storeDeleteSlot->where('delete_type', 'after')->isNotEmpty()) {
            $minAfterDate = $storeDeleteSlot
                ->where('delete_type', 'after')
                ->pluck('slot_date')
                ->min();

            $globalEnd = Carbon::parse($minAfterDate);
        }

        // ✅ Prefetch all repeats for all schedules in one query
        $scheduleIds = $schedules->pluck('id')->toArray();
        $allRepeats = TourScheduleRepeats::whereIn('tour_schedule_id', $scheduleIds)
            ->get()
            ->groupBy('tour_schedule_id');

        $perSchedule = [];
        $scheduleMeta = [];
        $today = Carbon::today();

        // Collect per schedule availability
        foreach ($schedules as $schedule) {
            $start = Carbon::parse($schedule->session_start_date);
            $end   = Carbon::parse($schedule->until_date);

            // ✅ Pass in preloaded repeats + deleted slots
            $scheduleRepeats = $allRepeats->get($schedule->id, collect())->groupBy('day')->all();
            $customDisabled  = $this->calculateDisabledDates($schedule, $today, $scheduleRepeats, $storeDeleteSlot);

            $perSchedule[$schedule->id] = [
                'start_date' => $start->toDateString(),
                'until_date' => $end->toDateString(),
                'disabled'   => $customDisabled,
            ];

            $scheduleMeta[$schedule->id] = [
                'start' => $start,
                'end' => $end,
                'disabled' => $customDisabled,
            ];
        }

        // ✅ Compute overall disabled dates (same as original)
        $overallDisabled = [];
        $cursor = $globalStart->copy();
        while ($cursor->lte($globalEnd)) {
            $date = $cursor->toDateString();

            $openSomewhere = false;
            foreach ($scheduleMeta as $meta) {
                if ($cursor->between($meta['start'], $meta['end'])) {
                    // If inside this schedule range, but NOT in its disabled list → it's open
                    if (!in_array($date, $meta['disabled'])) {
                        $openSomewhere = true;
                        break;
                    }
                }
            }

            if (!$openSomewhere) {
                $overallDisabled[] = $date;
            }

            $cursor->addDay();
        }

        return [
            'disabled_tour_dates' => $overallDisabled,
            'per_schedule' => collect($perSchedule)->map(function ($data) {
                $data['disabled'] = array_slice($data['disabled'], 0, 90);
                return $data;
            })->toArray(),
            'start_date' => $globalStart->toDateString(),
            'until_date' => $globalEnd->toDateString(),
        ];
    }


    private function calculateDisabledDates($schedule, Carbon $today, $repeats, $storeDeletedSlots): array
    {
        $start = Carbon::parse($schedule->session_start_date)->max($today);
        $end   = Carbon::parse($schedule->until_date)->endOfDay();

        if ($start->gt($end)) {
            return [];
        }

        $disabled = [];
        $period = CarbonPeriod::create($start->toDateString(), '1 day', $end->toDateString());

        foreach ($period as $date) {

            if (!$this->isDateAvailable($schedule, $date, $repeats, $storeDeletedSlots)) {
                $disabled[] = $date->toDateString();
            }
        }
       
        return $disabled;
    }


    private function isDateAvailable($schedule, $date, array $repeatsByDay = [], $storeDeletedSlots = []): bool
    {
        // Hard bounds
        $startDate = Carbon::parse($schedule->session_start_date)->startOfDay();
        $endDate   = Carbon::parse($schedule->until_date)->endOfDay();
        if (!$date->between($startDate, $endDate)) {
            return false;
        }

        // Config
        $repeatType  = strtoupper((string)$schedule->repeat_period); 
        $repeatUnit  = max(1, (int)($schedule->repeat_period_unit ?? 1));
        $durationMin = $this->minutesFromUnit($schedule->estimated_duration_num, $schedule->estimated_duration_unit);
        $noticeMin   = $this->minutesFromUnit($schedule->minimum_notice_num, $schedule->minimum_notice_unit);

        $earliestAllow = now()->copy()->addMinutes($noticeMin);
        $allDay = (bool)($schedule->sesion_all_day ?? false);
        $dayStr = $date->toDateString();

        $at = fn(string $time) => Carbon::parse("{$dayStr} {$time}");

        $windowOk = function (Carbon $slotStart, Carbon $slotEnd) use ($earliestAllow): bool {
            if ($slotEnd->lt($slotStart)) return false;
            return $slotEnd->gte($earliestAllow);
        };

        $available = false;
        $slots = [];

        if ($repeatType === 'NONE') {
            if (!$date->isSameDay($startDate)) return false;
            $slotStart = $allDay
                ? $date->copy()->startOfDay()
                : $at($schedule->session_start_time ?? '00:00');
            $slotEnd = $allDay
                ? $date->copy()->endOfDay()
                : ($schedule->session_start_time
                    ? $at($schedule->session_start_time)
                    : $slotStart->copy()->addMinutes(0));

            $available = $windowOk($slotStart, $slotEnd);

        } elseif ($repeatType === 'DAILY') {
            $daysSinceStart = $startDate->diffInDays($date);
            if ($daysSinceStart % $repeatUnit === 0) {
                $slotStart = $at($schedule->session_start_time ?? '00:00');
                $slotEnd   = $slotStart->copy()->addMinutes(0);
                $available = $allDay
                    ? $windowOk($date->copy()->startOfDay(), $date->copy()->endOfDay())
                    : $windowOk($slotStart, $slotEnd);
            }

        } elseif ($repeatType === 'WEEKLY') {
            $weeksSinceStart = $startDate->diffInWeeks($date);
            if ($weeksSinceStart % $repeatUnit === 0) {
                $dayName = $date->format('l');
                $entries = $repeatsByDay[$dayName] ?? [];

                foreach ($entries as $rep) {
                    $slotStart = $at($rep->start_time ?? ($schedule->session_start_time ?? '00:00'));
                    $slotEnd   = $at($rep->end_time   ?? ($schedule->session_end_time   ?? '23:59'));
                    if ($windowOk($slotStart, $slotEnd)) {
                        $available = true;
                        break;
                    }
                }
            }

        } elseif ($repeatType === 'MONTHLY') {
            $monthsSinceStart = $startDate->diffInMonths($date);
            if ($monthsSinceStart % $repeatUnit === 0 && $date->day === $startDate->day) {
                $slotStart = $at($schedule->session_start_time ?? '00:00');
                $slotEnd   = $at($schedule->session_end_time   ?? '23:59');
                $available = $windowOk($slotStart, $slotEnd);
            }

        } elseif ($repeatType === 'YEARLY') {
            $yearsSinceStart = $startDate->diffInYears($date);
            if ($yearsSinceStart % $repeatUnit === 0 &&
                $date->day === $startDate->day &&
                $date->month === $startDate->month) {
                $slotStart = $at($schedule->session_start_time ?? '00:00');
                $slotEnd   = $slotStart->copy()->addMinutes(max(1, $durationMin));
                $available = $windowOk($slotStart, $slotEnd);
            }

        } elseif (in_array($repeatType, ['HOURLY', 'MINUTELY'])) {
            $dayName = $date->format('l');
            $entries = $repeatsByDay[$dayName] ?? [];

            if($repeatType == 'HOURLY'){
                $repeatUnit = $repeatUnit * 60;
            }

            foreach ($entries as $rep) {
                $slotStart0 = $at($rep->start_time);
                $slotEnd    = $at($rep->end_time);
                if (!$slotEnd->gt($slotStart0)) continue;

                $m0 = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false)));
                $m  = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));

                $candidate = $slotStart0->copy()->addMinutes($m);
                while ($candidate->lte($slotEnd)) {
                    $slots[] = $candidate->copy();
                    $candidate->addMinutes($repeatUnit);
                }
            }
            $available = count($slots) > 0;
        }

        // === Apply deletions ===
        if ($available) {
            $response = [
                'data' => [
                    $dayStr => !empty($slots) ? $slots : [$at($schedule->session_start_time ?? '00:00')]
                ]
            ];

            // $deleted = $this->fetchDeletedSlot($schedule->tour_id);
            $response = $this->applySlotDeletions($response, $storeDeletedSlots);

            return !empty($response['data'][$dayStr]);
        }

        return false;
    }




    public function getSubTour($parentId, $date)
    {

        if (!$parentId) {
            return response()->json([
                'status'  => false,
                'message' => 'Parent ID'
            ], 400);
        }

        // Fetch sub tours under the parent
        $subTours = Tour::where('parent_id', $parentId)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->with(['galleries', 'categories', 'location'])
            ->get();

        if ($subTours->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No sub tours found for given parent'
            ], 404);
        }

        // Filter subTours based on next available date
        $filtered = $subTours->filter(function ($tour) use ($date) {
            $nextAvailable = $this->getNextAvailableDate($tour->id);

            return $nextAvailable && isset($nextAvailable['date']) && $nextAvailable['date'] === $date;
        });

        if ($filtered->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No sub tours available on the given date'
            ], 404);
        }

        // Format response
        $formatted = $filtered->map(function ($tour) use ($date) {
            $galleries = $tour->galleries->map(function ($g) {
                $image      = uploaded_asset($g->id);
                $medium_url = str_replace($g->file_name, $g->medium_name, $image);
                $thumb_url  = str_replace($g->file_name, $g->thumb_name, $image);
                return [
                    'original_url' => $image,
                    'medium_url'   => $medium_url,
                    'thumb_url'    => $thumb_url
                ];
            });

            return [
                'id'         => $tour->id,
                'title'      => $tour->title,
                'slug'       => $tour->slug,
                'price'      => format_price($tour->price),
                'next_date'  => $date, // the requested available date
                'galleries'  => $galleries,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $formatted->values() // reindex collection
        ]);
    }

    public function fetchDeletedSlot($id)
    {

        return ScheduleDeleteSlot::where('tour_id', $id)->get();

        return response()->json(['success' => true, 'message' => 'Slot saved successfully']);
    }

    public function applySlotDeletions(array $response, $storeDeleteSlot): array
    {
        $clearAll = false;
        
        foreach ($storeDeleteSlot as $deleteSlot) {
            $date      = $deleteSlot->slot_date;
            $startTime = $this->normalizeTime($deleteSlot->slot_start_time);
            $endTime   = $deleteSlot->slot_end_time ? $this->normalizeTime($deleteSlot->slot_end_time) : null;
            $type      = $deleteSlot->delete_type;

            if ($type === 'all') {
                $clearAll = true;
                break;
            }

            if (!isset($response['data'][$date])) {
                continue;
            }

            if ($type === 'single') {
                // remove only this slot range
                $response['data'][$date] = array_filter(
                    $response['data'][$date],
                    function ($slot) use ($startTime, $endTime) {
                        $slot24 = $this->normalizeTime($slot);
                        return !($slot24 >= $startTime && $endTime && $slot24 < $endTime);
                    }
                );

            } elseif ($type === 'after') {

                // remove slots for this date and all future dates
                foreach ($response['data'] as $d => $slots) {

                    // dd($d, $date);
                    if ($d < $date) continue;

                    $response['data'][$d] = array_filter(
                        $slots,
                        function ($slot) use ($startTime, $d, $date) {
                            $slot24 = $this->normalizeTime($slot);
                            if ($d == $date) {
                                return $slot24 < $startTime;
                            }
                            return false; // future dates → clear all
                        }
                    );
                }
            }
        }

        // if 'all' deletion was found → clear everything
        if ($clearAll) {
            foreach ($response['data'] as $d => $slots) {
                $response['data'][$d] = [];
            }
        }

        // ensure sorted order for each date
        foreach ($response['data'] as $d => $slots) {
            $response['data'][$d] = $this->sortSlots($slots);
        }

        return $response;
    }

    public function normalizeTime(string $time): string
    {
        return date("H:i", strtotime($time));
    }

    /**
     * Sort slots chronologically (keeps AM/PM format)
     */
    public function sortSlots(array $slots): array
    {
        usort($slots, function ($a, $b) {
            return strtotime($a) <=> strtotime($b);
        });
        return $slots;
    }

    public function getReview($tourId)
    {
        $review = TourReview::where('tour_id', $tourId)->first();

        if (!$review) {
            return response()->json(['message' => 'No review found'], 404);
        }

        // Decode JSON fields safely
        $recommended = $review->use_recommended ? json_decode($review->recommended, true) ?? [] : [];
        $badges      = $review->use_badge ? json_decode($review->badges, true) ?? [] : [];
        $banners     = $review->use_banner ? json_decode($review->banners, true) ?? [] : [];

        // Build response respecting the flags
        $response = [
            'tour_id' => $review->tour_id,

            // Review section
            'review_heading' => $review->use_review ? $review->review_heading : null,
            'review_text'    => $review->use_review ? $review->review_text : null,
            'review_rating'  => $review->use_review ? $review->review_rating : 0,
            'review_count'   => $review->use_review ? $review->review_count : 0,

            // Multiple Recommended items (array)
            'recommended' => $recommended,

            // Multiple Badge items (array)
            'badges' => $badges,

            // Multiple Banner items (array)
            'banners' => $banners,
        ];

        return response()->json($response);
    }


    public function single32(Request $request)
    {

        $data = Tour::with(['pickups', 'pickups.locations', 'pricings', 'addons', 'taxes_fees'])
                    ->find($request->id);

        

        if (!$data) return '';

        $_tourId = $data->id;
        $subtotal = 0;

        // ----------------------------------------------------------
        // ★ ADDED: GET START DATE + DISABLED DATES FROM API LOGIC
        // ----------------------------------------------------------
        $schedules = $data->schedules ?? collect();

        $tour_start_date = $this->getNextAvailableDate($data->id, $schedules);
        $disabled_dates  = $this->getDisabledTourDates($data->id, $schedules);

        // fallback if null
        if (!$tour_start_date) {
            $tour_start_date = now()->format('Y-m-d');
        }

        // ----------------------------------------------------------
        // BUILD PICKUP HTML (unchanged)
        // ----------------------------------------------------------
        $pickupHtml = '<div class="p-3" style="background:#f7f7f7; border:1px solid #ddd; margin-bottom:10px">
            <h4 style="font-size:16px; font-weight:600">Pickup Options</h4>';

        if (!empty($data->pickups) && $data->pickups[0]?->name === 'No Pickup') {
            $pickupHtml .= '
                <p>No Pickup Available</p>
                <input type="hidden" name="pickup_id" value="0">
                <input type="hidden" name="pickup_name" value="">
            ';
        }
        elseif (!empty($data->pickups) && $data->pickups[0]?->name === 'Pickup') {

            $comment = \DB::table('pickup_tour')
                            ->where('tour_id', $data->id)
                            ->where('pickup_id', $data->pickups[0]?->id)
                            ->value('comment');

            $pickupHtml .= '
                <label>Pickup Location</label>
                <input type="text" name="pickup_name" class="form-control" placeholder="Enter pickup location">
                <small style="color:#777; display:block; margin-top:5px;">'.($comment ?? "Enter the pickup location").'</small>
                <input type="hidden" name="pickup_id" value="0">
            ';
        }
        else {
            $locations = $data->pickups[0]?->locations ?? [];

            $pickupHtml .= '
                <label>Select Pickup Point</label>
                <select name="pickup_id" class="form-control pickup-dropdown" data-target="pickup-other-box">
                    <option value="">Select Pickup Point</option>';

            foreach ($locations as $loc) {
                $pickupHtml .= '<option value="'.$loc->id.'">'.$loc->location.'</option>';
            }

            $pickupHtml .= '
                    <option value="other">Other</option>
                </select>

                <div id="pickup-other-box" style="display:none; margin-top:10px">
                    <label>Enter Pickup Location</label>
                    <input type="text" name="pickup_name" class="form-control" placeholder="Enter location manually" value=" ">
                </div>';
        }

        $pickupHtml .= '</div>';

        // ----------------------------------------------------------
        // MAIN HTML OUTPUT
        // ----------------------------------------------------------
        $row_id = 'row_'.$request->tourCount;

        $str = '<div id="'.$row_id.'" style="border:1px solid #e1a604; margin-bottom:10px">
                    <input type="hidden" name="tour_id[]" value="'.$data->id.'" />  

                    <table class="table">
                        <tr>
                            <td width="600"><h3 class="text-lg">'.$data->title.'</h3></td>

                            <!-- ★ ADDED tour_start_date -->
                            <td class="text-right" width="200">
                                <div class="input-group">
                                    <input 
                                        type="text" 
                                        class="aiz-date-range form-control tour-start-date" 
                                        id="tour_startdate_'.$request->tourCount.'" 
                                        name="tour_startdate[]" 
                                        placeholder="Select Date" 
                                        data-single="true" 
                                        data-show-dropdown="true"
                                        data-tour-id="'.$data->id.'" 
                                        data-count="'.$request->tourCount.'"
                                        data-disabled-dates="'.htmlspecialchars(json_encode($disabled_dates)).'"
                                        value="'.$tour_start_date.'">

                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                            </td>

                            <td class="text-right" width="200">
                                <div class="input-group">
                                    <input type="text" placeholder="Time" name="tour_starttime[]" id="tour_starttime_'.$request->tourCount.'" class="form-control aiz-time-picker">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    </div>                       
                                </div>
                            </td>

                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeTour(\''.$row_id.'\')">-</button>
                                <button type="button" onclick="addTour()" class="btn btn-sm btn-info">+</button>
                            </td>
                        </tr>
                    </table>

                    <table class="table">'.$pickupHtml.'</table>

                    <!-- Pricing + Addons + Tax (unchanged) -->
                    ... REMAINING SAME ...
                </div>';

        return $str;
    }

    public function single343(Request $request)
{
    $data = Tour::with(['detail', 'schedules', 'pricings', 'addons', 'taxes_fees', 'pickups.locations'])
                ->find($request->id);

    if (!$data) {
        return 'Tour not found';
    }

    $count = $request->tourCount;
    $_tourId = $data->id;

    // ---------------------------------------------------
    // GET NEXT AVAILABLE DATE + DISABLED DATES
    // ---------------------------------------------------
    $tour_start_date_arr = $this->getNextAvailableDate($data->id, $data->schedules);
    $disabled_dates = $this->getDisabledTourDates($data->id, $data->schedules);

    // Convert {date: "..."} into plain string
    $tour_start_date = is_array($tour_start_date_arr)
                        ? ($tour_start_date_arr['date'] ?? '')
                        : ($tour_start_date_arr->date ?? '');

    $disabledJson = json_encode($disabled_dates);


    // ---------------------------------------------------
    // PICKUP HTML
    // ---------------------------------------------------
    $pickupHtml = '<div class="p-3" style="background:#f7f7f7; border:1px solid #ddd; margin-bottom:10px">
        <h4 style="font-size:16px; font-weight:600">Pickup Options</h4>';

    if (!empty($data->pickups) && isset($data->pickups[0]) && $data->pickups[0]?->name === 'No Pickup') {

        $pickupHtml .= '
            <p>No Pickup Available</p>
            <input type="hidden" name="pickup_id" value="0">
            <input type="hidden" name="pickup_name" value="">
        ';
    }

    else if (!empty($data->pickups) && isset($data->pickups[0]) && $data->pickups[0]?->name === 'Pickup') {

        $comment = \DB::table('pickup_tour')
                        ->where('tour_id', $data->id)
                        ->where('pickup_id', $data->pickups[0]?->id)
                        ->value('comment');

        $commentText = $comment ?? "Enter the pickup location";

        $pickupHtml .= '
            <label>Pickup Location</label>
            <input type="text" name="pickup_name" class="form-control" placeholder="Enter pickup location">

            <small style="color:#777; display:block; margin-top:5px;">'.$commentText.'</small>
            <input type="hidden" name="pickup_id" value="0">
        ';
    }

    else if (!empty($data->pickups) && isset($data->pickups[0])) {

        $locations = $data->pickups[0]?->locations ?? [];

        $pickupHtml .= '
            <label>Select Pickup Point</label>
            <select name="pickup_id" class="form-control pickup-dropdown" data-target="pickup-other-box">
                <option value="">Select Pickup Point</option>';

                foreach ($locations as $loc) {
                    $pickupHtml .= '<option value="'.$loc->id.'">'.$loc->location.'</option>';
                }

                $pickupHtml .= '<option value="other">Other</option>';

        $pickupHtml .= '
            </select>

            <div id="pickup-other-box" style="display:none; margin-top:10px">
                <label>Enter Pickup Location</label>
                <input type="text" name="pickup_name" value=" " class="form-control" placeholder="Enter location manually">
            </div>
        ';
    }

    $pickupHtml .= '</div>';

    // ---------------------------------------------------
    // MAIN HTML BLOCK
    // ---------------------------------------------------
    $row_id = 'row_'.$count;
    $subtotal = 0;

    $str = '
    <div id="'.$row_id.'" style="border:1px solid #e1a604; margin-bottom:10px">

        <input type="hidden" name="tour_id[]" value="' .  $data->id . '" />

        <table class="table">
            <tr>
                <td width="600"><h3 class="text-lg">'.$data->title.'</h3></td>

                <td width="200" class="text-right">
                    <div class="input-group">
                        <input type="text"
                               class="aiz-date-range form-control tour-startdate"
                               data-count="'.$count.'"
                               id="tour_startdate_'.$count.'"
                               name="tour_startdate[]"
                               data-single="true"
                               data-show-dropdown="true"
                               data-disabled-dates=\''.$disabledJson.'\'
                               value="'.$tour_start_date.'">

                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                    </div>
                </td>

                <td width="200" class="text-right">
                    <div class="input-group">
                        <input type="text" placeholder="Time"
                               name="tour_starttime[]"
                               id="tour_starttime_'.$count.'"
                               class="form-control aiz-time-picker"
                               data-minute-step="1">

                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        </div>
                    </div>
                </td>

                <td class="text-right">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeTour(\''.$row_id.'\')">-</button>
                    <button type="button" onclick="addTour()" class="btn btn-sm btn-info">+</button>
                </td>
            </tr>
        </table>

        
    ';

    // ---------------------------------------------------
    // PRICING BLOCK (kept exactly as original)
    // ---------------------------------------------------
    $str .= '<table class="table" style="background:#ebebeb">
                <tr>
                    <td style="width:200px" width="200">
                        <table class="table">
                            <tr>
                                <td colspan="2">
                                    <h4 style="font-size:16px; font-weight:600">Quantities</h4>
                                </td>
                            </tr>';

    if ($data->pricings) {
        $i = 0;
        foreach ($data->pricings as $pricing) {
            $num = ($i++ == 0) ? 1 : 0;
            if ($num) $subtotal += ($num * $pricing->price);

            $str .= '
                <tr>
                    <td width="60">
                        <input type="hidden" name="tour_pricing_id_'.$_tourId.'[]" value="'.$pricing->id.'" />
                        <input type="number" name="tour_pricing_qty_'.$_tourId.'[]" value="'.$num.'" style="width:60px" class="form-contorl">
                        <input type="hidden" name="tour_pricing_price_'.$_tourId.'[]" value="'.$pricing->price.'" /> 
                    </td>
                    <td>'.$pricing->label.' ('. price_format($pricing->price) .')</td>
                </tr>';
        }
    }

    $str .= '</table>
            </td>
            <td style="width:200px">
                <table class="table">
                    <tr>
                        <td colspan="2">
                            <h4 style="font-size:16px; font-weight:600">Optional extras</h4>
                        </td>
                    </tr>';

    if ($data->addons) {
        foreach ($data->addons as $extra) {
            $price = $extra->price;

            $str .= '
                <tr>
                    <td width="60">
                        <input type="hidden" name="tour_extra_id_'.$_tourId.'[]" value="'.$extra->id.'" />  
                        <input type="number" name="tour_extra_qty_'.$_tourId.'[]" value="0" style="width:60px" min="0" class="form-contorl text-center">
                        <input type="hidden" name="tour_extra_price_'.$_tourId.'[]" value="'.$price.'" /> 
                    </td>
                    <td>'.$extra->name.' ('. price_format($extra->price) .')</td>
                </tr>';
        }
    }

    $str .= '</table>
            </td>
        </tr>
    </table>' ;

    $str .=$pickupHtml;

    // ---------------------------------------------------
    // TAXES & FEES
    // ---------------------------------------------------
    $str .= '<table class="table">';

    $str .= '

        <tr>
            <th>total</th>
            <th class="text-right withouttax-box">'. price_format($subtotal) .'</th>
            <th class="text-right">'. price_format($subtotal) .'</th>
        </tr>';
    if ($data->taxes_fees) {
        foreach ($data->taxes_fees as $item) {

            $price = get_tax($subtotal, $item->fee_type, $item->tax_fee_value);
            $tax = $price ?? 0;
            $subtotal += $tax;

            // $str .= '
            //     <tr>
            //         <td>'.$item->label.' ('. taxes_format($item->fee_type, $item->tax_fee_value) .')</td>
            //         <td class="text-right">'. price_format($tax) .'</td>
            //     </tr>';

                $str .= '<tr class="tax-row" 
                data-type="'.$item->fee_type.'" 
                data-value="'.$item->tax_fee_value.'">
                <td>'.$item->label.' ('. taxes_format($item->fee_type, $item->tax_fee_value) .')</td>
                <td class="text-right tax-amount">'. price_format($tax) .'</td>
            </tr>';
        }
    }

    $str .= '

        <tr>
            <th>Subtotal</th>
            <th class="text-right subtotal-box">'. price_format($subtotal) .'</th>
            <th class="text-right">'. price_format($subtotal) .'</th>
        </tr>
    </table>';

    $str .= '</div>'; // end main div

    return $str;
}


public function single(Request $request)
{
    $data  = Tour::find($request->id);
    $str = '';
    $subtotal = 0;

    if($data) {

        $_tourId = $data->id;

        // ================================
        // ADD YOUR NEW DATE LOGIC HERE
        // ================================
        $schedules = $data->schedules ?? [];

        $tour_start_date = $this->getNextAvailableDate($data->id, $schedules);
        $tour_start_date = is_array($tour_start_date) ? ($tour_start_date['date'] ?? '') : $tour_start_date;

        $disabled_dates  = $this->getDisabledTourDates($data->id, $schedules);
        $disabled_dates_json = json_encode($disabled_dates);


        // ================================
        // YOUR ORIGINAL PICKUP LOGIC
        // ================================
        $pickupHtml = '<div class="p-3" style="background:#f7f7f7; border:1px solid #ddd; margin-bottom:10px">
        <h4 style="font-size:16px; font-weight:600"></h4>';


        // CASE 1: NO PICKUP
        if(!empty($data->pickups) && isset($data->pickups[0]) && $data->pickups[0]?->name === 'No Pickup') {

            $pickupHtml .= '
                <p>No Pickup Available</p>

                <input type="hidden" name="pickup_id" value="0">
                <input type="hidden" name="pickup_name" value="">
            ';
        }



        // CASE 2: PICKUP (text input + comment)
        else if(!empty($data->pickups) && isset($data->pickups[0]) && $data->pickups[0]?->name === 'Pickup') {

            $comment = \DB::table('pickup_tour')
                            ->where('tour_id', $data->id)
                            ->where('pickup_id', $data->pickups[0]?->id)
                            ->value('comment');

            $commentText = $comment ?? "Enter the pickup location";

            $pickupHtml .= '
                <label>Pickup Location</label>
                <input required type="text" name="pickup_name" class="form-control" placeholder="Enter pickup location">

                <small style="color:#777; display:block; margin-top:5px;">'.$commentText.'</small>

                <input type="hidden" name="pickup_id" value="0">
            ';
        }



        // CASE 3: MULTIPLE LOCATIONS (dropdown + other option)
        else if (!empty($data->pickups) && isset($data->pickups[0])) {

            $locations = $data->pickups[0]?->locations ?? [];

            $pickupHtml .= '
                <label>Select Pickup Point</label>
                <select required name="pickup_id" class="form-control pickup-dropdown" data-target="pickup-other-box">
                    <option value="">Select Pickup Point</option>';

                    foreach($locations as $loc) {
                        $pickupHtml .= '<option value="'.$loc->id.'">'.$loc->location.'</option>';
                    }

                    $pickupHtml .= '<option value="other">Other</option>';

            $pickupHtml .= '
                </select>

                <div id="pickup-other-box" style="display:none; margin-top:10px">
                    <label>Enter Pickup Location</label>
                    <input required type="text" name="pickup_name" class="form-control" placeholder="Enter location manually" value=" ">
                </div>
            ';
        }

        $pickupHtml .= '</div>';

        // ================================
        // RENDER HTML START
        // ================================
        $row_id = 'row_'.$request->tourCount;

        $str = '<div id="'.$row_id.'" style="border:1px solid #e1a604; margin-bottom:10px">
                <input type="hidden" name="tour_id[]" value="'.$data->id.'" />  
                <input type="hidden" class="disabled-dates" value=\''.$disabled_dates_json.'\'>
                <table class="table">
                    <tr>
                        <td width="600"><h3 class="text-lg">' .  $data->title . '</h3></td>
                        <td class="text-right" width="200">
                            <div class="input-group">
                                <input type="text" 
                                    class="aiz-date-range form-control tour_startdate_field"
                                    id="tour_startdate"
                                    name="tour_startdate[]"
                                    placeholder="Select Date" 
                                    data-format="ddd MMM DD, YYYY"
                                    data-single="true" 
                                    data-show-dropdown="true" 
                                    value="'.date('D M d, Y',strtotime($tour_start_date)).'">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            <div>
                                <input type="text" class="tour_startdate_display border-0" readonly>
                            </div>
                        </td>

                        <td class="text-right" width="200">
                            <div class="input-group">
                                <input type="text" placeholder="Time" name="tour_starttime[]" id="tour_starttime" value="" class="form-control aiz-time-picker" data-minute-step="1"> 
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                </div>                       
                            </div>
                        </td>

                        <td class="text-right">
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
                                    <input type="hidden" name="tour_pricing_type" value="'.$data->price_type.'" /> 
                                </tr>';

                                if($data->pricings) {

                                    $maxQuantity = $data->detail->quantity_max;

                                    $i=0; $j=0;
                                    foreach($data->pricings as $pricing) {
                                        $num = ($i == 0) ? 1 : 0;
                                        if($i == 0) {
                                            $subtotal += ($num * $pricing->price);
                                        }

                                        $minQuantity = 0;
                                        if($j === 0) {
                                            $minQuantity = $pricing->quantity_used ?? $data->detail->quantity_min; 
                                            $j++;
                                        }
                                        $i++;

                                        $str .= '<tr>
                                            <td width="60">
                                                <input type="hidden" name="tour_pricing_id_'.$_tourId.'[]" value="'.$pricing->id.'" />
                                                <input type="number" name="tour_pricing_qty_'.$_tourId.'[]" value="'.$num.'" style="width:60px" class="form-contorl" min="'.$minQuantity.'" max="'.$maxQuantity.'" >
                                                <input type="hidden" name="tour_pricing_price_'.$_tourId.'[]" value="'.$pricing->price.'" /> 
                                                <input type="hidden" name="tour_pricing_type_'.$_tourId.'[]" value="'.$data->price_type.'" /> 
                                                <input type="hidden" name="tour_pricing_min_'.$_tourId.'[]" value="'.$pricing->quantity_used.'">
                                                
                                            </td>
                                            <td>'.$pricing->label.' ('. price_format($pricing->price) .')</td>
                                        </tr>';
                                    }
                                }

                            $str .= '</table>
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
                                                <input type="hidden" name="tour_extra_price_'.$_tourId.'[]" value="'.$price.'" /> 
                                            </td>
                                            <td>'.$extra->name.' ('.price_format($extra->price).')</td>
                                        </tr>';
                                    }
                                }
                                
                            $str .= '</table>
                        </td>
                    </tr>
                </table>
                
                <table class="table">';

                $str .= $pickupHtml;

                $str .= '

                <tr>
                    <th>Sub Total</th>
                    <th class="text-right withouttax-box">'. price_format($subtotal) .'</th>
                </tr>';

                if($data->taxes_fees) {
                    foreach ($data->taxes_fees as $item) {                    
                        $tax = get_tax($subtotal, $item->fee_type, $item->tax_fee_value) ?? 0;
                        $subtotal += $tax;

                        // $str .= '<tr>
                        //     <td>'.$item->label.' ('. taxes_format($item->fee_type, $item->tax_fee_value) .')</td>
                        //     <td class="text-right">'. price_format($tax) .'</td>
                        // </tr>';
                        $str .= '<tr class="tax-row" 
                data-type="'.$item->fee_type.'" 
                data-value="'.$item->tax_fee_value.'">
                <td>'.$item->label.' ('. taxes_format($item->fee_type, $item->tax_fee_value) .')</td>
                <td class="text-right tax-amount">'. price_format($tax) .'</td>
            </tr>';
                    }
                }

                $str .= '
                    <tr>
                        <th>Total</th>
                        
                        <th class="text-right subtotal-box">'. price_format($subtotal) .'</th>
                    </tr>
                </table>
                </div>';
    }

    return $str;
}


public function singleCalendar(Request $request)
{
    $tour = Tour::find($request->id);

    $orderTour = OrderTour::where('order_id', $request->order_id)->first();

    if (!$tour) {
        return response()->json(['error' => 'Not found'], 404);
    }

    $schedules = $tour->schedules ?? [];

    // Get next available date
    $tour_start_date = $this->getNextAvailableDate($tour->id, $schedules);
    $tour_start_date = is_array($tour_start_date) 
        ? ($tour_start_date['date'] ?? '') 
        : $tour_start_date;

    // Disabled dates
    $disabled_dates  = $this->getDisabledTourDates($tour->id, $schedules);

    return response()->json([
        'tour_date' => $orderTour->tour_date,
        'tour_time' => $orderTour->tour_time,
        'tour_id' => $tour->id,
        'start_date' => $tour_start_date,
        'disabled_dates' => $disabled_dates,
    ]);
}








}
