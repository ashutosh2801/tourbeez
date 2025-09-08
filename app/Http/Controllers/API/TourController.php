<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\TourScheduleRepeats;
use App\Models\TourSpecialDeposit;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Helpers;
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
        match ($request->input('order_by')) {
            'lowtohigh' => $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END, sort_order ASC')
                                ->orderBy('price', 'ASC'),

            'hightolow' => $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END, sort_order ASC')
                                ->orderBy('price', 'DESC'),

            default     => $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END, sort_order ASC'),
        };

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
                    'pricings',
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
            $pickups[] = 'Pickup';
        }
        else if (!empty($tour->pickups) && isset($tour->pickups[0])) {
            $pickups = $tour->pickups[0]?->locations ?? [];
        }
        $original_price   = $tour->price;
        $discounted_price = $tour->price;

        if ($tour->coupon_value && $tour->coupon_value > 0) {
            if ($tour->coupon_type == 'fixed') {
                // Original price = price + coupon value
                $original_price   = $tour->price + $tour->coupon_value;
                $discounted_price = $tour->price;
            } elseif ($tour->coupon_type == 'percentage') {
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
                //'itinerariesAll'=> $tour->itinerariesAll,
                //'schedule'      => $tour->schedule,
                'faqs'          => $tour->faqs,
                'inclusions'    => $tour->inclusions,
                'optionals'     => $tour->optionals,
                'exclusions'    => $tour->exclusions,
                'optionals'     => $tour->optionals,
                'taxes_fees'    => $tour->taxes_fees,
                'detail'        => $tour->detail,
                'location'      => $tour->location,
                'breadcrumbs'   => $breadcrumbs,
                'pricings'      => $tour->pricings,
                'category'      => $tour->category,
                'galleries'     => $galleries,
                'addons'        => $addons,
                'offer_ends_in' => $tour->offer_ends_in,
                //'tour_special_deposits'        => $tour->specialDeposit,

                'discount'              =>  $tour->coupon_value,
                'discount_type'         =>  strtoupper($tour->coupon_type),
                'discounted_price'      => $discounted_price,
                'tour_start_date'       => $this->getNextAvailableDate($tour->id),
                'disabled_tour_dates'   => $this->getDisabledTourDates($tour->id),
            ];

            //discounted_price
            //discount
            //discount_type
            // tour_start_date -> inhich solt available (same as the slots and avalable slots)
            // disabled_tour_dates -> [array of date in which slot is not availble,  // repeate _tour_type
           
        }

        return response()->json([
            'status' => true,
            'data'   => $formattedTour
        ]);
    }

    function highlightMatch($string, $keyword) {
        $string = ucfirst($string);
        return preg_replace("/(" . preg_quote($keyword, '/') . ")/i", '<mark>$1</mark>', $string);
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
        if (!$depositRule) {
            $depositRule = Cache::remember('deposit_rule_global', 86400, function () {
                return TourSpecialDeposit::where('type', 'global')->first();
            });
        }

        // ✅ Booking fee data (always included)
        $bookingFees = [
            'price_booking_fee'     => get_setting('price_booking_fee'),
            'tour_booking_fee'      => get_setting('tour_booking_fee'),
            'tour_booking_fee_type' => get_setting('tour_booking_fee_type'),
        ];

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

        $cities = City::where('status', 'active')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '' . $search . '%');
                });
            })
            ->orderBy('name', 'asc')
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
                $data[] = ['icon'=>'city', 'title' => $this->highlightMatch($city->name, $search), 'slug' => '/'.Str::slug($city->name).'/'.$city->id.'/c1', 'address' => ucfirst($city->state?->name).', '.ucfirst($city->state?->country?->name)];
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
                $data[] = ['icon'=>$image, 'title' => $this->highlightMatch($tour->title, $search), 'slug' => '/tour/'.$tour->slug, 'address' => $tour->location->address];
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

private function getNextAvailableDate($tourId)
{
    $today = Carbon::today();

    // Get schedules where today is within range or in the future
    $schedules = TourSchedule::where('tour_id', $tourId)
        ->where(function ($query) use ($today) {
            $query->orWhere(function ($q) use ($today) {
                $q->whereDate('session_start_date', '<=', $today)
                  ->whereDate('until_date', '>=', $today);
            })
            ->orWhereDate('session_start_date', '>=', $today);
        })
        ->get();

    $nextDates = [];

    foreach ($schedules as $schedule) {
        // dd($schedule->repeat_period);
        if($schedule->repeat_period == 'NONE'){
            if($today->lte(Carbon::parse($schedule->session_start_date))){
                return ['date' => Carbon::parse($schedule->session_start_date)->toDateString()];
            } 
            return ['date' => ""];
            
        }   

        $nextDate = $this->calculateNextDate($schedule, $today);

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

private function calculateNextDate($schedule, Carbon $today)
{
    $interval = $schedule->repeat_period_unit ?? 1;
    $repeatType = $schedule->repeat_period;
    // dd($repeatType);
    if($repeatType == 'none'){
        return false;
    }
    $scheduleStartDate = Carbon::parse($schedule->session_start_date);
    $scheduleEndDate   = Carbon::parse($schedule->until_date);

    // Start from today or schedule start (whichever is later)
    $next = $scheduleStartDate;

    while ($next->lte($scheduleEndDate)) {

        if ($repeatType === 'WEEKLY' || $repeatType === 'MINUTELY' || $repeatType === 'HOURLY') {
            // ✅ check if this weekday is allowed
            $dayName = $next->format('l');
            // dd($next, $dayName);
            $allowed = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                ->where('day', $dayName)
                ->exists();
                // dd($allowed);
            if (!$allowed) {
                $next->addDay(); // move to next day if not allowed
                continue;
            }
            if ($repeatType === 'WEEKLY'){
                $weekDiff = Carbon::parse($schedule->session_start_date)->diffInWeeks($next);

                if ($weekDiff % $interval != 0) {
                    $next->addDay(); // move to next day if not allowed
                    continue;// No slots this week
                } 
            }
              
        }

        if ($this->hasValidSlot($schedule, $next, )) {

            return ['date'  => $next->toDateString()];
        }

        switch ($repeatType) {
            case 'DAILY':   $next->addDays($interval); break;
            case 'WEEKLY':  $next->addDays(1); break;
            case 'MONTHLY': $next->addMonths($interval); break;
            case 'YEARLY':  $next->addYears($interval); break;
            case 'HOURLY':  $next->addHours($interval); break;
            case 'MINUTELY':$next->addMinutes($interval); break;
            default: return null;
        }
    }

    return null;
}

private function hasValidSlot($schedule, Carbon $date, $durationMinutes = 30, $minimumNoticePeriod = 0)
{

    $repeatType = $schedule->repeat_period;

    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_start_time);
    $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_end_time);

    if ($repeatType === 'MINUTELY' || $repeatType === 'HOURLY') {
            // ✅ check if this weekday is allowed
            $dayName = $date->format('l');
            // dd($next, $dayName);
            $tourScheduleRepeats = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                ->where('day', $dayName)
                ->first();

            $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $tourScheduleRepeats->start_time);
            $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $tourScheduleRepeats->end_time);
              
    }



    $minimumNoticePeriod = $schedule->minimum_notice_unit == "HOURS" ? $schedule->minimum_notice_num * 60 : $schedule->minimum_notice_num ;
    $earliestAllowed = now()->addMinutes($minimumNoticePeriod);
    // dd($earliestAllowed, $endTime->lt($earliestAllowed), $endTime);
    // If the entire session is already in the past, skip
    if ($endTime->lt($earliestAllowed)) {
        return false;
    }

    // Daily/weekly/monthly/yearly → just check if at least one valid slot exists
    if (in_array($schedule->repeat_period, ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'])) {
        return $startTime->gte($earliestAllowed);
    }

    // Hourly/minutely schedules → iterate slots
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
    private function isDateAvailable($schedule, $date, array $repeatsByDay = []): bool
    {
        // Hard bounds
        $startDate = Carbon::parse($schedule->session_start_date)->startOfDay();
        $endDate   = Carbon::parse($schedule->until_date)->endOfDay();
        if (!$date->between($startDate, $endDate)) {
            return false;
        }

        // Config
        $repeatType    = strtoupper((string)$schedule->repeat_period); // NONE, DAILY, WEEKLY...
        $repeatUnit    = max(1, (int)($schedule->repeat_period_unit ?? 1)); // interval
        $durationMin   = $this->minutesFromUnit($schedule->estimated_duration_num, $schedule->estimated_duration_unit);
        $noticeMin     = $this->minutesFromUnit($schedule->minimum_notice_num, $schedule->minimum_notice_unit);


        $earliestAllow = now()->copy()->addMinutes($noticeMin);
        // dd($earliestAllow);
        // Times for this date
        $allDay = (bool)($schedule->sesion_all_day ?? false);
        $dayStr = $date->toDateString();

        // Helper: window available?
        // dd($earliestAllow);
        $windowOk = function (Carbon $slotStart, Carbon $slotEnd) use ($earliestAllow): bool {
            if ($slotEnd->lt($slotStart)) return false;
            // A slot exists if some timepoint within [slotStart, slotEnd] is >= earliestAllow
            return $slotEnd->gte($earliestAllow);
        };

        // Helper: parse HH:MM onto $date
        $at = fn(string $time) => Carbon::parse("{$dayStr} {$time}");

        // NONE: one-off date
        if ($repeatType === 'NONE') {
            // dd(23423);
            // dd($date->isSameDay($startDate));
            if (!$date->isSameDay($startDate)) return false;
            $slotStart = $allDay
                ? $date->copy()->startOfDay()
                : $at($schedule->session_start_time ?? '00:00');
            $slotEnd = $allDay
                ? $date->copy()->endOfDay()
                : (isset($schedule->session_start_time)
                    ? $at($schedule->session_start_time)
                    : $slotStart->copy()->addMinutes(0));

                // dd($slotStart, $slotEnd);
            return $windowOk($slotStart, $slotEnd);
        }

        // DAILY: every N days from session_start_date
        if ($repeatType === 'DAILY') {
            $daysSinceStart = (int) floor($startDate->diffInDays($date));
            if ($daysSinceStart % $repeatUnit !== 0) return false;

            if ($allDay) {
                return $windowOk($date->copy()->startOfDay(), $date->copy()->endOfDay());
            }

            // Your code forms a single window using start_time and +duration
            $slotStart = $at($schedule->session_start_time ?? '00:00');
            $slotEnd   = $slotStart->copy()->addMinutes(0);
            return $windowOk($slotStart, $slotEnd);
        }

        // WEEKLY: check repeats for this weekday, weeks interval from session_start_date
        if ($repeatType === 'WEEKLY') {
            $weeksSinceStart = (int) floor($startDate->diffInWeeks($date));
            if ($weeksSinceStart % $repeatUnit !== 0) return false;

            $dayName = $date->format('l'); // Monday, ...
            $entries = $repeatsByDay[$dayName] ?? $repeatsByDay[$dayName] = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                ->where('day', $dayName)
                ->get();

            foreach ($entries as $rep) {
                $slotStart = $at($rep->start_time ?? ($schedule->session_start_time ?? '00:00'));
                $slotEnd   = $at($rep->end_time   ?? ($schedule->session_end_time   ?? '23:59'));
                if ($windowOk($slotStart, $slotEnd)) {
                    return true;
                }
            }
            return false;
        }

        // MONTHLY: same day-of-month every N months
        if ($repeatType === 'MONTHLY') {
            $monthsSinceStart = (int) floor($startDate->diffInMonths($date));
            if ($monthsSinceStart % $repeatUnit !== 0) return false;

            // must match the start day-of-month (as your code does)
            if ((int)$date->format('d') !== (int)$startDate->format('d')) return false;

            $slotStart = $at($schedule->session_start_time ?? '00:00');
            $slotEnd   = $at($schedule->session_end_time   ?? '23:59');
            return $windowOk($slotStart, $slotEnd);
        }

        // YEARLY: same day & month every N years
        if ($repeatType === 'YEARLY') {
            $yearsSinceStart = (int) floor($startDate->diffInYears($date));
            if ($yearsSinceStart % $repeatUnit !== 0) return false;

            if ((int)$date->format('d') !== (int)$startDate->format('d')
                || (int)$date->format('m') !== (int)$startDate->format('m')) {
                return false;
            }

            $slotStart = $at($schedule->session_start_time ?? '00:00');
            // Your code made YEARLY window as start + duration
            $slotEnd   = $slotStart->copy()->addMinutes(max(1, $durationMin));
            return $windowOk($slotStart, $slotEnd);
        }

        // HOURLY: entries per weekday, filter by start-hour alignment and then every Nth slot index
        if ($repeatType === 'HOURLY') {
            $dayName = $date->format('l');
            $entries = $repeatsByDay[$dayName] ?? $repeatsByDay[$dayName] = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                ->where('day', $dayName)
                ->get();

            foreach ($entries as $rep) {
                $slotStart0 = $at($rep->start_time);
                $slotEnd    = $at($rep->end_time);

                if (!$slotEnd->gt($slotStart0)) continue;

                // Your code requires hoursSinceStart % interval == 0
                $hoursSinceStart = (int) floor($startDate->diffInHours($slotStart0));
                if ($hoursSinceStart % $repeatUnit !== 0) continue;

                // Keep only every Nth slot index; find first candidate index >= earliestAllow
                // $dur = max(1, $durationMin);
                // $m0  = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false) / $dur));
                // move to next index that is a multiple of interval
                // $m   = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));
                // $candidate = $slotStart0->copy()->addMinutes($m * $dur);

                $m0 = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false)));
                $m = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));
                $candidate = $slotStart0->copy()->addMinutes($m);
                if ($candidate->lte($slotEnd)) {
                    return true;
                }
            }
            return false;
        }

        // MINUTELY: entries per weekday, keep every Nth slot index (index % interval == 0)
        if ($repeatType === 'MINUTELY') {
            $dayName = $date->format('l');

            $entries = $repeatsByDay[$dayName] ?? $repeatsByDay[$dayName] = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                ->where('day', $dayName)
                ->get();

            foreach ($entries as $rep) {
                $slotStart0 = $at($rep->start_time);
                $slotEnd    = $at($rep->end_time);
                // dd($slotStart0, $slotEnd);
                // dd($slotEnd->gt($slotStart0));
                if (!$slotEnd->gt($slotStart0)) continue;

                // $dur = max(1, $durationMin);
                // $m0  = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false)));
                // $m   = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));

                // $m0 = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false)));

// Round $m0 up to the nearest multiple of repeatUnit
                // $m = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));
                // $candidate = $slotStart0->copy()->addMinutes($m);
                // dd($m0, $dur);

                // $candidate = $slotStart0->copy()->addMinutes($m * $dur);
                // dd($candidate, $candidate->lte($slotEnd));
                $m0 = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false)));
                $m = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));
                $candidate = $slotStart0->copy()->addMinutes($m);
                if ($candidate->lte($slotEnd)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Build disabled dates from TODAY to until_date.
     * One pass over days, constant-time availability check per day.
     */
    // private function calculateDisabledDates($schedule, Carbon $today): array
    // {
    //     // $start = Carbon::parse($schedule->session_start_date); $today->copy()->startOfDay()->max(Carbon::parse($schedule->session_start_date)->startOfDay());
    //     $start = Carbon::parse($schedule->session_start_date);
    //     $start = Carbon::today();
    //     $end   = Carbon::parse($schedule->until_date)->endOfDay();

    //     if ($start->gt($end)) return [];

    //     // Prefetch repeats once; group by weekday to avoid DB hits per day
    //     $repeats = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)->get()->groupBy('day')->all();

    //     $disabled = [];
    //     $period = CarbonPeriod::create($start->toDateString(), '1 day', $end->toDateString());
    //     // dd($period);
    //     foreach ($period as $d) {
    //         /** @var Carbon $d */
    //         if (!$this->isDateAvailable($schedule, $d, $repeats)) {

    //             $disabled[] = $d->toDateString();
    //         }
    //     }

    //     return $disabled;
    // }

    /**
     * Public entry: returns all disabled dates for the tour (today → until_date).
     */
    // private function getDisabledTourDates(int $tourId): array
    // {
    //     $today = Carbon::today();

    //     // One active schedule per tour at a time (as you stated)
    //     $schedule = TourSchedule::where('tour_id', $tourId)
    //         ->where(function ($q) use ($today) {
    //             $q->whereDate('session_start_date', '<=', $today)
    //               ->whereDate('until_date', '>=', $today)
    //               ->orWhereDate('session_start_date', '>=', $today);
    //         })
    //         ->orderBy('session_start_date')
    //         ->first();

    //     if (!$schedule) {
    //         return ['disabled_tour_dates' => []];
    //     }

    //     $disabled = $this->calculateDisabledDates($schedule, $today);

    //     return [
    //         'disabled_tour_dates' => $disabled,
    //         'start_date' => $schedule->session_start_date,
    //         'untill_date' => $schedule->until_date,

    //     ];
    // }



    // private function getDisabledTourDates(int $tourId): array
    // {
    //     $today = Carbon::today();

    //     // ✅ Fetch ALL schedules instead of one
    //     $schedules = TourSchedule::where('tour_id', $tourId)
    //         ->where(function ($q) use ($today) {
    //             $q->whereDate('session_start_date', '<=', $today)
    //               ->whereDate('until_date', '>=', $today)
    //               ->orWhereDate('session_start_date', '>=', $today);
    //         })
    //         ->orderBy('session_start_date')
    //         ->get();
    //     // dd($schedules);
    //     if ($schedules->isEmpty()) {
    //         return ['disabled_tour_dates' => []];
    //     }

    //     $disabled = [];
    //     $startDate = null;
    //     $untilDate = null;
    //     $allDisabled = [];

    //     foreach ($schedules as $schedule) {
    //         $disabledForSchedule = $this->calculateDisabledDates($schedule, $today);

    //         // $disabled = array_merge($disabled, $disabledForSchedule);

    //          if (empty($allDisabled)) {
    //             $allDisabled = $disabledForSchedule;
    //         } else {
    //             // ✅ Keep only common disabled dates across schedules
    //             $allDisabled = array_intersect($allDisabled, $disabledForSchedule);
    //         }

    //         // Track overall min start and max until
    //         if (!$startDate || Carbon::parse($schedule->session_start_date)->lt(Carbon::parse($startDate))) {
    //             $startDate = $schedule->session_start_date;
    //         }
    //         if (!$untilDate || Carbon::parse($schedule->until_date)->gt(Carbon::parse($untilDate))) {
    //             $untilDate = $schedule->until_date;
    //         }
    //     }

    //     return [
    //         'disabled_tour_dates' => array_values(array_unique($allDisabled)), // ✅ ensure unique dates
    //         'start_date' => $startDate,
    //         'until_date' => $untilDate,
    //     ];
    // }


    private function getDisabledTourDates3534(int $tourId): array
    {
        $today = Carbon::today();

        // ✅ Fetch ALL schedules instead of one
        $schedules = TourSchedule::where('tour_id', $tourId)
            ->where(function ($q) use ($today) {
                $q->whereDate('session_start_date', '<=', $today)
                  ->whereDate('until_date', '>=', $today)
                  ->orWhereDate('session_start_date', '>=', $today);
            })
            ->orderBy('session_start_date')
            ->get();

        if ($schedules->isEmpty()) {
            return ['disabled_tour_dates' => []];
        }

        $startDate = null;
        $untilDate = null;
        $allDisabled = null; // start with null so we can set the first schedule’s disabled list

        foreach ($schedules as $schedule) {
            $disabledForSchedule = $this->calculateDisabledDates($schedule, $today);

            // 🚨 If any schedule has no disabled dates → result should be empty
            if (empty($disabledForSchedule)) {
                return [
                    'disabled_tour_dates' => [],
                    'start_date' => $schedule->session_start_date,
                    'until_date' => $schedule->until_date,
                ];
            }

            if (is_null($allDisabled)) {
                $allDisabled = $disabledForSchedule;
            } else {
                // ✅ Keep only common disabled dates across schedules
                $allDisabled = array_intersect($allDisabled, $disabledForSchedule);
            }

            // Track overall min start and max until
            if (!$startDate || Carbon::parse($schedule->session_start_date)->lt(Carbon::parse($startDate))) {
                $startDate = $schedule->session_start_date;
            }
            if (!$untilDate || Carbon::parse($schedule->until_date)->gt(Carbon::parse($untilDate))) {
                $untilDate = $schedule->until_date;
            }
        }

        return [
            'disabled_tour_dates' => array_values(array_unique($allDisabled ?? [])),
            'start_date' => $startDate,
            'until_date' => $untilDate,
        ];
    }

    private function getDisabledTourDates(int $tourId): array
    {
        $schedules = TourSchedule::where('tour_id', $tourId)
            ->orderBy('session_start_date')
            ->get();

        if ($schedules->isEmpty()) {
            return [
                'disabled_tour_dates' => [],
                'per_schedule' => [],
            ];
        }

        // Global start & end
        $globalStart = Carbon::parse($schedules->min('session_start_date'));
        $globalEnd   = Carbon::parse($schedules->max('until_date'));

        $perSchedule = [];
        $scheduleMeta = [];

        // Collect per schedule availability
        foreach ($schedules as $schedule) {
            $start = Carbon::parse($schedule->session_start_date);
            $end   = Carbon::parse($schedule->until_date);

            // Custom disabled list comes from calculateDisabledDates()
            $customDisabled = $this->calculateDisabledDates($schedule, Carbon::today());

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

        // ✅ Compute overall disabled dates
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
            'disabled_tour_dates' => $overallDisabled, // ✅ correct global disabled
            'per_schedule' => $perSchedule,            // ✅ meta for debugging
            'start_date' => $globalStart->toDateString(),
            'until_date' => $globalEnd->toDateString(),
        ];
    }




    private function calculateDisabledDates($schedule, Carbon $today): array
    {
        $start = Carbon::parse($schedule->session_start_date)->max($today);
        $end   = Carbon::parse($schedule->until_date)->endOfDay();

        if ($start->gt($end)) {
            return [];
        }

        // Prefetch repeats once; group by weekday
        $repeats = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
            ->get()
            ->groupBy('day')
            ->all();

        $disabled = [];
        $period = CarbonPeriod::create($start->toDateString(), '1 day', $end->toDateString());

        foreach ($period as $d) {
            if (!$this->isDateAvailable($schedule, $d, $repeats)) {
                $disabled[] = $d->toDateString();
            }
        }

        return $disabled;
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



}
