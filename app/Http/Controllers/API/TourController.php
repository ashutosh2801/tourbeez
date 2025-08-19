<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Models\TourScheduleRepeats;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tour::query()
                ->where('status', 1)
                ->whereNull('deleted_at');

        if ($request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->q) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        if ($request->slug) {
            $query->where('slug', 'like', '%' . $request->slug . '%');
        }

        if ($request->categories) {
            $query->whereHas('categories', function ($q) use ($request) {
                $categories = explode(',', $request->categories);
                $q->whereIn('categories.id', $categories);
            });
        }

        // Filter by city_id via location relationship
        if ($request->city_id) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('city_id', $request->city_id);
                $q->orWhere('state_id', $request->city_id);
                $q->orWhere('country_id', $request->city_id);
            });
        }
 
        if ($request->min_price && $request->max_price) {
            $query->whereBetween('price', [(float)$request->min_price, (float)$request->max_price]);
        } elseif ($request->min_price) {
            $query->where('price', '>=', (float)$request->min_price);
        } elseif ($request->max_price) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        $order_by = $request->input('order_by');
        if( $order_by == 'lowtohigh' ) {
            $query->orderBy('price', 'ASC');
        }
        else if( $order_by == 'hightolow' ) {
            $query->orderBy('price', 'DESC');
        }
        else {
            $query->orderBy('sort_order', 'ASC');
        }

        $page = $request->get('page', 1);
        $cacheKey = 'tour_list_' . md5(json_encode($request->all()) . '_page_' . $page);

        // dd($query->toSql(), $query->getBindings(), $query->get());
        $paginated = Cache::remember($cacheKey, 86400, function () use ($query) {
            return $query->paginate(12);
        });

        // Transform the paginated data
        $items = [];
        foreach ($paginated->items() as $d) {

            $galleries = [];
            if(count($d->galleries)>0) {
                foreach( $d->galleries as $g ) {
                    $image      = uploaded_asset($g->id);
                    $medium_url = str_replace($g->file_name, $g->medium_name, $image);
                    $thumb_url  = str_replace($g->file_name, $g->thumb_name, $image);
                    $galleries[] = [
                        'original_image' => $image,
                        'medium_image'   => $medium_url,
                        'thumb_image'    => $thumb_url,
                    ];
                }
            }
            else {
                $image      = uploaded_asset($d->main_image->id);
                $medium_url = str_replace($d->main_image->file_name, $d->main_image->medium_name, $image);
                $thumb_url  = str_replace($d->main_image->file_name, $d->main_image->thumb_name, $image);
                $galleries[] = [
                    'original_image' => $image,
                    'medium_image'   => $medium_url,
                    'thumb_image'    => $thumb_url,
                ];
            }

            $duration = $d->schedule?->estimated_duration_num.' ' ?? '';
            $duration.= ucfirst($d->schedule?->estimated_duration_unit ?? '');

            // $items[] = [
            //     'id'             => $d->id,
            //     'title'          => $d->title,
            //     'slug'           => $d->slug,
            //     'unique_code'    => $d->unique_code,
            //     'all_images'     => $galleries,
            //     //'catogory'       => $d->catogory,
            //     'price'          => price_format($d->price),
            //     'original_price' => $d->price,
            //     'duration'       => strtolower(trim($duration)),
            //     'rating'         => randomFloat(4, 5),
            //     'comment'        => rand(50, 100),
            // ];


            $discount         = $d->coupon_value;
            $original_price   = $d->price;
            $discounted_price = $d->price;
 
            if ($discount && $discount > 0) {
                if ($d->coupon_type == 'fixed') {
                    // Original price = price + coupon value
                    $original_price   = round($d->price + $discount);
                    $discounted_price = $d->price;
                } elseif ($d->coupon_type == 'percentage') {
                    // Original price = inflated by coupon percentage
                    $original = $d->price / (1 - ($discount / 100));
                    $original_price = round($original);
                    $discounted_price = $d->price;
                }
            }
 
            $items[] = [
                'id'             => $d->id,
                'title'          => $d->title,
                'slug'           => $d->slug,
                'unique_code'    => $d->unique_code,
                'all_images'     => $galleries,
                //'catogory'       => $d->catogory,
                'price'          => price_format($d->price),
                'original_price' => $original_price,
                'duration'       => strtolower(trim($duration)),
                'rating'         => randomFloat(4, 5),
                'comment'        => rand(50, 100),
                'discount'          =>  $discount,
                'discount_type'     =>  strtoupper($d->coupon_type),
                'discounted_price'  => $discounted_price,
                'offer_ends_in'        => $tour->offer_ends_in,
 
            ];
        }

        // Return the transformed data along with pagination info
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
            'url' => '',
            'label'=> 'Home'
        ];
        $breadcrumbs[] = [
            'url' => 'tours',
            'label'=> 'Tours'
        ];
        if($tour->location) {
            $location = $tour->location;
            if($location->country) {
                $breadcrumbs[] = ['url' => 'c2/'.$location->country->id.'/'.Str::slug($location->country->name), 'label' => 'Things To Do in '.$location->country->name];
            }
            if($location->state) {
                $breadcrumbs[] = ['url' => 's1/'.$location->state->id.'/'.Str::slug($location->state->name), 'label' => 'Things To Do in '.$location->state->name];
            }
            if($location->city) {
                $breadcrumbs[] = ['url' => 'c1/'.$location->city->id.'/'.Str::slug($location->city->name), 'label' => 'Things To Do in '.$location->city->name];
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
                $original = $tour->price / (1 - ($tour->coupon_value / 100));
                $original = round($original);
                $discounted_price = $tour->price;
            }
        }

        if ($tour) {
            // 💡 You can now format or transform fields as needed
            // return $this->getNextAvailableDate($tour->id);
            $formattedTour = [
                'id'            => $tour->id,
                'title'         => $tour->title,
                'price'         => format_price($tour->price), // formatted price
                'original_price'=> $original, // without formatted price
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
                'offer_ends_in'        => $tour->offer_ends_in,

                'discount'       =>  $tour->coupon_value,
                'discount_type'       =>  strtoupper($tour->coupon_type),
                'discounted_price'     => $discounted_price,
                'tour_start_date'      => $this->getNextAvailableDate($tour->id),
                'disabled_tour_dates'      => $this->getDisabledTourDates($tour->id),
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
     * Search home page tour  
     */
    public function search(Request $request) {
        
        $search = $request->input('q', '');
        $date = $request->input('date', '');

        // Build cache key
        //$cacheKey = 'search_tours_' . md5($search . '_' . $date);

        $cities = City::where('status', 'active')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '' . $search . '%');
                });
            })
            ->orderBy('name', 'asc')
            ->limit(2)
            ->get();


            
        // $categories = Category::orderBy('name', 'asc')
        //     ->when($search, function ($query, $search) {
        //         $query->where(function ($q) use ($search) {
        //             $q->where('name', 'LIKE', '' . $search . '%');
        //         });
        //     })
        //     ->limit(3)
        //     ->get();    
        
        $total_cities       = $cities->count();
        // $total_categories   = $categories->count();
        // $total_tours        = 8 - ($total_cities + $total_categories);

        $total_tours        = 8 - ($total_cities);
        //$tours = Cache::remember($cacheKey, now()->addMinutes(20), function () use ($search, $date) {
            //return 
        $tours = Tour::with(['location' => function ($query) {
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

        //});

        $data = [];
        if($total_cities>0) {
            foreach($cities as $city) {
                $data[] = ['icon'=>'city', 'title' => $this->highlightMatch($city->name, $search), 'slug' => 'c1/'.$city->id.'/'.Str::slug($city->name), 'address' => ucfirst($city->state?->name).', '.ucfirst($city->state?->country?->name)];
            }
        }
        // if($total_categories>0) {
        //     foreach($categories as $category) {
        //         $data[] = ['icon'=>'category', 'title' => $this->highlightMatch($category->name, $search), 'slug' => 'c3/'.$category->id.'/'.$category->slug , 'address' => ''];
        //     }
        // }
        if($tours->count()>0) {
            foreach($tours as $tour) {
                $image_id = $tour->main_image->id ?? 0;
                $image  = uploaded_asset($image_id, 'thumb');
                $data[] = ['icon'=>$image, 'title' => $this->highlightMatch($tour->title, $search), 'slug' => 'tour/'.$tour->slug, 'address' => $tour->location->address];
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
    $scheduleStartDate = Carbon::parse($schedule->session_start_date);
    $scheduleEndDate   = Carbon::parse($schedule->until_date);

    // Start from today or schedule start (whichever is later)
    $next = $scheduleStartDate;

    while ($next->lte($scheduleEndDate)) {
        if ($this->hasValidSlot($schedule, $next)) {
            return ['date' => $next->toDateString()];
        }

        // Move forward by repeat interval
        switch ($repeatType) {
            case 'DAILY':   $next->addDays($interval); break;
            case 'WEEKLY':  $next->addWeeks($interval); break;
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
    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_start_time);
    $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_end_time);

    $earliestAllowed = now()->addMinutes($minimumNoticePeriod);

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

        // Times for this date
        $allDay = (bool)($schedule->sesion_all_day ?? false);
        $dayStr = $date->toDateString();

        // Helper: window available?
        $windowOk = function (Carbon $slotStart, Carbon $slotEnd) use ($earliestAllow): bool {
            if ($slotEnd->lte($slotStart)) return false;
            // A slot exists if some timepoint within [slotStart, slotEnd] is >= earliestAllow
            return $slotEnd->gte($earliestAllow);
        };

        // Helper: parse HH:MM onto $date
        $at = fn(string $time) => Carbon::parse("{$dayStr} {$time}");

        // NONE: one-off date
        if ($repeatType === 'NONE') {
            if (!$date->isSameDay($startDate)) return false;
            $slotStart = $allDay
                ? $date->copy()->startOfDay()
                : $at($schedule->session_start_time ?? '00:00');
            $slotEnd = $allDay
                ? $date->copy()->endOfDay()
                : (isset($schedule->session_end_time)
                    ? $at($schedule->session_end_time)
                    : $slotStart->copy()->addMinutes(max(1, $durationMin)));
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
            $slotEnd   = $slotStart->copy()->addMinutes(max(1, $durationMin));
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
                $dur = max(1, $durationMin);
                $m0  = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false) / $dur));
                // move to next index that is a multiple of interval
                $m   = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));
                $candidate = $slotStart0->copy()->addMinutes($m * $dur);
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

                if (!$slotEnd->gt($slotStart0)) continue;

                $dur = max(1, $durationMin);
                $m0  = max(0, (int)ceil($slotStart0->diffInMinutes($earliestAllow, false) / $dur));
                $m   = ($m0 % $repeatUnit === 0) ? $m0 : $m0 + ($repeatUnit - ($m0 % $repeatUnit));
                $candidate = $slotStart0->copy()->addMinutes($m * $dur);
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
    private function calculateDisabledDates($schedule, Carbon $today): array
    {
        // $start = Carbon::parse($schedule->session_start_date); $today->copy()->startOfDay()->max(Carbon::parse($schedule->session_start_date)->startOfDay());
        $start = Carbon::parse($schedule->session_start_date);
        $end   = Carbon::parse($schedule->until_date)->endOfDay();

        if ($start->gt($end)) return [];

        // Prefetch repeats once; group by weekday to avoid DB hits per day
        $repeats = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)->get()->groupBy('day')->all();

        $disabled = [];
        $period = CarbonPeriod::create($start->toDateString(), '1 day', $end->toDateString());

        foreach ($period as $d) {
            /** @var Carbon $d */
            if (!$this->isDateAvailable($schedule, $d, $repeats)) {
                $disabled[] = $d->toDateString();
            }
        }

        return $disabled;
    }

    /**
     * Public entry: returns all disabled dates for the tour (today → until_date).
     */
    private function getDisabledTourDates(int $tourId): array
    {
        $today = Carbon::today();

        // One active schedule per tour at a time (as you stated)
        $schedule = TourSchedule::where('tour_id', $tourId)
            ->where(function ($q) use ($today) {
                $q->whereDate('session_start_date', '<=', $today)
                  ->whereDate('until_date', '>=', $today)
                  ->orWhereDate('session_start_date', '>=', $today);
            })
            ->orderBy('session_start_date')
            ->first();

        if (!$schedule) {
            return ['disabled_tour_dates' => []];
        }

        $disabled = $this->calculateDisabledDates($schedule, $today);

        return ['disabled_tour_dates' => $disabled];
    }


}
