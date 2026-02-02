<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Closure;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Tour;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use DB;

class CrawlerResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $ua = strtolower($request->userAgent());        
        $ip = $request->ip();
        $url = $request->fullUrl();

        logger()->info('UA LOG', [
            'ua'  => $ua,
            'ip'  => $ip,
            'url' => $url,
        ]);

        
        $bots = [
            // Social
            'facebookexternalhit', 'facebot', 'twitterbot', 'linkedinbot',
            'pinterest', 'slackbot', 'discordbot', 'whatsapp',
            'telegrambot', 'skypeuripreview', 'teamsbot',

            // iOS / Apple Preview (CRITICAL)
            'applebot', 'cfnetwork', 'darwin',

            // Search Engines
            'googlebot', 'bingbot', 'duckduckbot',
            'baiduspider', 'yandex', 'sogou', 'petalbot',

            // Schema / Rich Results Validators
            'google-structured-data-testing-tool', 'schema-markup-validator',
            'google rich results test',
            'lighthouse',
            'googlebot-image', 'googlebot-video',

            // Others
            'applebot', 'embedly', 'quora link preview',
            'outbrain', 'rogerbot', 'ahrefsbot', 'semrushbot'
        ];

        


        foreach ($bots as $bot) {
            
            if (strpos($ua, $bot) !== false) {

                // ----- Adjust for /tbadmin/ subfolder -----
                $path = $request->path();
                $prefix = '';
                if (str_starts_with($path, $prefix)) {
                    $path = substr($path, strlen($prefix));
                }

                $segments = explode('/', $path);

                // Home Page
                if ($path === '' || $path === '/') {

                    $data = Cache::remember('cities_home_list', 86400, function () {
                        return DB::table('tour_locations as tl')
                                ->join('tours as t', 't.id', '=', 'tl.tour_id')
                                ->join('cities as c', 'c.id', '=', 'tl.city_id')
                                ->join('uploads as u', 'u.id', '=', 'c.upload_id')
                                ->select('c.id', 'c.name', 'c.upload_id')
                                ->groupBy('c.id', 'c.name', 'c.upload_id')
                                ->orderByRaw('RAND()')
                                ->where('c.upload_id', '>=', 1)
                                ->whereExists(function ($query) {
                                    $query->select(DB::raw(1))
                                        ->from('tour_schedules as ts')
                                        ->whereColumn('ts.tour_id', 't.id')
                                        ->where('ts.until_date', '>=', DB::raw('CURDATE()'));
                                })
                                ->limit(10)
                                ->get();
                    });

                    $cities = [];
                    foreach($data as $d) {
                        $cities[] = [
                            'id'    => $d->id,
                            'name'  => ucfirst( $d->name ),
                            'url'   => '/'.Str::slug( $d->name ).'/'.$d->id.'/c1',
                            'image' => uploaded_asset( $d->upload_id ),
                            'extra' => ''
                        ];
                    }                    

                    //Tours
                    $tour_data = Cache::remember('tours_home_list', 86400, function () {
                        return DB::table(DB::raw("( 
                            SELECT 
                                t.id, 
                                t.title AS name, 
                                t.slug, 
                                t.price, 
                                t.created_at, 
                                t.unique_code, 
                                u.upload_id
                            FROM tours t
                            JOIN tour_upload u ON u.tour_id = t.id
                            JOIN tour_locations l ON l.tour_id = t.id
                            WHERE t.status = 1 
                            AND t.deleted_at IS NULL
                            AND l.city_id IS NOT NULL 
                            AND l.city_id = 10519
                            AND EXISTS (
                                SELECT 1 
                                FROM tour_schedules s 
                                WHERE s.tour_id = t.id
                                    AND s.until_date >= CURDATE()
                            )
                            GROUP BY t.unique_code
                            ORDER BY t.sort_order DESC
                            LIMIT 14
                        ) as sub"))  // ✅ NO semicolon here
                        ->get();
                    });

                    $tours = [];
                    foreach($tour_data as $d) {
                        $tours[] = [
                            'id'    => $d->id,
                            'name'  => ucfirst( $d->name ),
                            'url'   => '/tour/'.$d->slug,
                            'image' => uploaded_asset( $d->upload_id ),
                            'price' => $d->price,
                            'sku'   => $d->unique_code,
                        ];
                    }  
                    
                    //Blog
                    $blog_data = Cache::remember('blog_home_list', 86400, function () {
                        return DB::table('tb_posts as p')
                            ->leftJoin('tb_postmeta as pm', 'pm.post_id', '=', 'p.ID')
                            ->select('p.ID as id', 'p.post_title as name', 'p.post_name as slug', 'p.post_date', 'p.guid')
                            ->where('p.post_type', 'post')
                            ->where('p.post_status', 'publish')
                            ->distinct()
                            ->orderBy('p.post_date', 'desc')
                            ->limit(5)
                            ->get();
                    });

                    $blogs = [];
                    foreach ($blog_data as $b) {

                        // Get the featured image ID from post meta
                        $image_id = DB::table('tb_postmeta')
                            ->where('post_id', $b->id)
                            ->where('meta_key', '_thumbnail_id')
                            ->value('meta_value');

                        // Get the image URL using the image ID (from tb_posts.guid)
                        $image_url = null;
                        if ($image_id) {
                            $image_url = DB::table('tb_posts')
                                ->where('ID', $image_id)
                                ->value('guid');
                        }

                        $blogs[] = [
                            'id'    => $b->id,
                            'title'  => ucfirst($b->name),
                            'url'   => ('https://tourbeez.com/blog/' . $b->slug), // or $b->guid if using permalink
                            'image' => $image_url,
                            'date' => date('d M, Y', strtotime($b->post_date))
                        ];
                    }

    
                    return response()->view('share.seo', [
                        'title' => 'Tours, Activities &amp; Travel Experiences Worldwide | TourBeez',
                        'description' => 'Discover unforgettable travel experiences with TourBeez. Book tours, activities, and tickets to top global destinations with ease and confidence. Explore, adventure, and enjoy every moment',
                        'keywords' => 'International Tour Packages, Best Travel Deals Worldwide, World Tours And Trips, Customizable Holiday Packages,  Budget-friendly Travel',
                        'image' => 'https://tourbeez.com/logo.jpg',
                        // 'page' => 'home',
                        'file' => 'home',
                        'tours' => $tours, 
                        'cities' => $cities, 
                        'blogs' => $blogs
                    ]);
                }
                else if ($path === 'destinations') {

                    $query = DB::table('tour_locations as tl')
                        ->join('tours as t', 't.id', '=', 'tl.tour_id')
                        ->join('cities as c', 'c.id', '=', 'tl.city_id')
                        ->leftJoin('states as s', 's.id', '=', 'tl.state_id')
                        ->leftJoin('countries as co', 'co.id', '=', 'tl.country_id')
                        ->select(
                            'c.id',
                            'c.name',
                            'c.upload_id',
                            'tl.state_id',
                            'tl.country_id',
                            's.name as state_name',
                            'co.name as country_name',
                            DB::raw('(
                                SELECT COUNT(DISTINCT t2.id)
                                FROM tours t2
                                JOIN tour_locations tl2 ON tl2.tour_id = t2.id
                                WHERE tl2.city_id = c.id
                                AND t2.status = 1
                                AND t2.deleted_at IS NULL
                                AND EXISTS (
                                    SELECT 1
                                    FROM tour_schedules ts2
                                    WHERE ts2.tour_id = t2.id
                                        AND ts2.until_date >= CURDATE()
                                )
                            ) as total_tours')
                        )
                        ->distinct()
                        ->where('c.upload_id' , '>=', 1)
                        ->whereExists(function ($query) {
                                    $query->select(DB::raw(1))
                                        ->from('tour_schedules as ts')
                                        ->whereColumn('ts.tour_id', 't.id')
                                        ->where('ts.until_date', '>=', DB::raw('CURDATE()'));
                                })
                        ->orderByRaw('RAND()');
                    $paginated = $query->paginate(50, ['*'], 'page', 1);

                    $cities = [];
                    foreach ($paginated->items() as $d) {
                        $cities[] = [
                            'id'    => $d->id,
                            'name'  => 'Things to do in ' . ucfirst($d->name),
                            'url'   => '/' . Str::slug($d->name) . '/' . $d->id . '/c1',
                            'image' => uploaded_asset($d->upload_id),
                            'extra' => ucwords($d->state_name) . ', ' . ucwords($d->country_name),
                            'total_tours' => $d->total_tours
                        ];
                    }                    
                
                    return response()->view('share.seo', [
                        'title' => 'Top Travel Destinations, Tours & Activities Worldwide | TourBeez',
                        'description' => 'Browse top travel destinations, tours and activities with TourBeez. Find and book great experiences now with easy booking and best ticket deals.',
                        'keywords' => 'Top Travel Destinations, City Tours and Activities, Travel Experiences, Adventure Tours and Activities',
                        'image' => asset('public/images/destination.jpg'),
                        // 'page' => 'destinations',
                        'file' => 'destinations',
                        'cities' => $cities, 
                    ]);
                }
                else if ($path === 'tickets') {
                    //Tours
                    $tour_data = Cache::remember('tours_home_list', 86400, function () {
                        return DB::table(DB::raw("( 
                            SELECT 
                                t.id, 
                                t.title AS name, 
                                t.slug, 
                                t.price, 
                                t.created_at, 
                                t.unique_code, 
                                u.upload_id
                            FROM tours t
                            JOIN tour_upload u ON u.tour_id = t.id
                            LEFT JOIN tour_locations l ON l.tour_id = t.id
                            LEFT JOIN category_tour ct ON ct.tour_id = t.id
                            WHERE t.status = 1 
                            AND t.deleted_at IS NULL
                            AND l.city_id IS NOT NULL 
                            AND l.city_id = 10519
                            AND EXISTS (
                                SELECT 1 
                                FROM tour_schedules s 
                                WHERE s.tour_id = t.id
                                    AND s.until_date >= CURDATE()
                            )
                            AND ct.category_id IN (97)
                            GROUP BY t.unique_code
                            ORDER BY t.sort_order DESC
                            LIMIT 25
                        ) as sub"))  // ✅ NO semicolon here
                        ->get();
                    });

                    $tours = [];
                    foreach($tour_data as $d) {
                        $tours[] = [
                            'id'    => $d->id,
                            'name'  => ucfirst( $d->name ),
                            'url'   => '/tour/'.$d->slug,
                            'image' => uploaded_asset( $d->upload_id ),
                            'price' => $d->price,
                            'sku'   => $d->unique_code,
                        ];
                    }  

                    return response()->view('share.seo', [
                        'title' => 'Book Tickets for Tours & Experiences, Fast Online Booking | TourBeez',
                        'description' => 'Get tickets for tours, attractions and activities with TourBeez. Book now with easy booking and great deals on top experiences.',
                        'keywords' => 'TourBeez Tickets, Book Tickets for Tours & Experiences',
                        'image' => asset('public/images/tickets.jpg'),
                        'page' => 'tickets',
                        'tours' => $tours, 
                    ]);
                }
                else if ($path === 'about-us') {
                    return response()->view('share.seo', [
                        'title' => 'Meet TourBeez, Expert Tour & Activity Booking Service',
                        'description' => 'Get to know TourBeez and our mission for easy tour and activity bookings. Book top experiences with great prices today.',
                        'keywords' => 'About TourBeez',
                        'image' => asset('public/images/about-us.jpg'),
                        'file' => 'about-us'
                    ]);
                }
                else if ($path === 'contact-us') {
                    return response()->view('share.seo', [
                        'title' => 'Contact TourBeez, Get Help with Tours & Bookings',
                        'description' => 'Reach out to TourBeez for support on tours, bookings and tickets. Contact us now for quick assistance and easy travel help.',
                        'keywords' => 'Contact TourBeez',
                        'image' => asset('public/images/contact-us.jpg'),
                        'file' => 'contact-us'
                    ]);
                }
                else if ($path === 'wishlist') {
                    return response()->view('share.seo', [
                        'title' => 'Save Your Favourite Tours & Tickets | TourBeez Wishlist',
                        'description' => 'Keep track of your favourite tours, attractions, and tickets with your TourBeez Wishlist. Easily revisit your favourites, compare options, and book when you\'re ready',
                        'keywords' => 'wishlist tours, saved tours, favourite tickets, tour wish list, save tour deals',
                        'image' => asset('public/images/wishlist.jpg'),
                    ]);
                }
                else if ($path === 'terms-and-conditions') {
                    return response()->view('share.seo', [
                        'title' => 'Terms and Conditions, Booking Rules & User Agreement | TourBeez',
                        'description' => 'Read TourBeez terms and conditions for bookings, usage and responsibilities. Get clear info on rules and agreements before you book.',
                        'keywords' => 'TourBeez Terms and Conditions',
                        'image' => asset('public/images/terms-condition.jpg'),
                        'file' => 'terms-and-conditions'
                    ]);
                }
                else if ($path === 'privacy-policy') {
                    return response()->view('share.seo', [
                        'title' => 'Privacy Policy, Data Protection & User Privacy | TourBeez',
                        'description' => 'Read TourBeez privacy policy to understand how we protect your data and personal information. Get clear details on privacy practices today.',
                        'keywords' => 'TourBeez Privacy Policy',
                        'image' => asset('public/images/privacy-policy.jpg'),
                        'file' => 'privacy-policy'
                    ]);
                }
                else if ($path === 'cancellation-policy') {
                    return response()->view('share.seo', [
                        'title' => 'Cancellation Policy, Refund Terms & Booking Changes | TourBeez',
                        'description' => 'Read TourBeez cancellation policy and refund terms. Get clear info on booking changes, refunds and how to cancel tours with ease.',
                        'keywords' => 'TourBeez Cancellation Policy, Reschedule Tour',
                        'image' => asset('public/images/cancel-policy.jpg'),
                        'file' => 'cancellation-policy'
                    ]);
                }
                else if ($path === 'login') {
                    return response()->view('share.seo', [
                        'title' => 'Access Your Account & Manage Bookings | TourBeez Login',
                        'description' => 'Log in to your TourBeez account to view and manage your tours, tickets, and wishlist. Secure access for fast booking history, updates, and personalized deals',
                        'keywords' => 'TourBeez login, account login, manage bookings, user account, tour booking account',
                        'image' => asset('public/images/login-banner.jpg'),
                        'file' => 'login'
                    ]);
                }
                else if ($path === 'supplier') {
                    return response()->view('share.seo', [
                        'title' => 'Partner with TourBeez, Supplier & Tour Provider Opportunities | TourBeez',
                        'description' => 'Join TourBeez as a supplier to list your tours and activities. Partner now for more bookings and easy platform access.',
                        'keywords' => 'TourBeez Supplier, Tour Provider',
                        'image' => asset('public/images/supplier.jpg'),
                        'file' => 'supplier'
                    ]);
                }
                // ----- Tour Page -----
                else if (str_starts_with($path, 'tour/')) {
                    $slug = explode('/', $path)[1];

                    $cacheKey = 'tour_detail_' . $slug;

                    $tour = Tour::where('slug', $slug)
                            ->where('status', 1)
                            ->whereNull('deleted_at')
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

                    if (!$tour) {
                        abort(404, 'Tour not found.');
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
                        $medium_url = str_replace($addon->file_name, $addon->medium_name, $image);
                        $thumb_url  = str_replace($addon->file_name, $addon->thumb_name, $image);

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

                    if(!empty($tour->pickups) && isset($tour->pickups[0]) && $tour->pickups[0]?->name === 'No Pickup') {
                        $pickups[] = 'No Pickup';
                    }
                    else if(!empty($tour->pickups) && isset($tour->pickups[0]) && $tour->pickups[0]?->name === 'Pickup') {
                        $pickups[0] = 'Pickup';
                        
                        $comment = DB::table('pickup_tour')
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
                        $thumb_url  = $galleries[0]['thumb_url'] ?? asset('public/tourbeez-logo.jpg');

                        $formattedTour = [
                            'id'            => $tour->id,
                            'title'         => $tour->title,
                            'price'         => format_price($tour->price), // formatted price
                            'original_price'=> $original_price, // without formatted price
                            'price_type'    => $tour->price_type,
                            'unique_code'   => $tour->unique_code,
                            'slug'          => $tour->slug,
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
                            'discount'      =>  $tour->coupon_value,
                            'discount_type' =>  strtoupper($tour->coupon_type),
                            'discounted_price'      => $discounted_price,
                        ]; 

                        return response()->view('share.tour', [
                            'title'         => $tour->detail->meta_title ?? $tour->detail->name . ' | ' . env('APP_NAME'),
                            'description'   => $tour->detail->meta_description ?? $tour->short_description,
                            'image'         => $thumb_url,
                            'url'           => url()->current(),
                            'tour'          => $formattedTour,
                        ]);
                    }
                }
                // ----- Listing Page -----
                else if (count($segments) == 3) {
                    $citySlug = $segments[0];   // toronto
                    $id       = $segments[1];   // 10519
                    $type     = $segments[2];   // c1
                    $d = null;
                    if ($type === 'c1') {
                        $d = City::findOrFail( $id );
                    }
                    else if ( $type === 's1' ) {
                        $d = State::findOrFail( $id );
                    }
                    else if ( $type === 'c2' ) {
                        $d = Country::findOrFail( $id );
                    }
                    else if ( $type === 'c3' ) {
                        $d = Category::findOrFail( $id );
                    }                    

                    if($id && $type && $d){

                        $query = Tour::select([
                                'id', 'title', 'slug', 'unique_code', 'price',
                                'coupon_type', 'coupon_value', 'offer_ends_in'
                            ])
                            ->with([
                                'detail:id,tour_id,description',
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


                        if ($id) {
                            if($type === 'c3') {
                                $query->whereHas('categories', fn($q) => $q->where('categories.id', $id));
                            }
                            else {
                                $query->whereHas('location', function ($q) use ($id, $type) {
                                    match($type) {
                                        'c1' => $q->where('city_id', $id),
                                        's1' => $q->where('state_id', $id),
                                        'c2' => $q->where('country_id', $id),
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


                        if ($request->input('order_by') === 'lowtohigh') {
                            $query->orderBy('price', 'ASC');
                        } elseif ($request->input('order_by') === 'hightolow') {
                            $query->orderBy('price', 'DESC');
                        } else {
                            $query->orderByRaw('(CASE WHEN sort_order > 0 THEN 0 ELSE 1 END) ASC')
                                    ->orderBy('sort_order', 'ASC'); // Only sort_order for default
                        }


                        // Cache paginated
                        $page = $request->get('page', 1);
                        $cacheKey = 'tour_list_' . md5(json_encode($request->all()) . '_page_' . $page);

                        // dd(getFullSql($query));

                        $paginated = Cache::tags(['tours'])->remember($cacheKey, 86400, fn() => $query->paginate(24));

                        // Transform response
                        $items = $paginated->map(fn($d) => [
                            'id'              => $d->id,
                            'title'           => $d->title,
                            'slug'            => $d->slug,
                            'unique_code'     => $d->unique_code,
                            'all_images'      => $d->formatted_images,
                            'description'     => $d->detail->description,
                            'galleries'       => $d->galleries->map(fn($img) => [
                                'original_url'=> uploaded_asset($img->id),
                                'medium_url'  => str_replace($img->file_name, $img->medium_name, uploaded_asset($img->id)),
                                'thumb_url'   => str_replace($img->file_name, $img->thumb_name, uploaded_asset($img->id))
                            ]),
                            'price'           => price_format($d->price),
                            'original_price'  => $d->discounted_data['original_price'],
                            'discount'        => $d->discounted_data['discount'],
                            'discount_type'   => $d->discounted_data['discount_type'],
                            'discounted_price'=> $d->discounted_data['discounted_price'],
                            'duration'        => $d->duration,
                            'offer_ends_in'   => $d->offer_ends_in,    
                        ]);

                        $name = ucfirst( $d->name );

                        return response()->view('share.seo', [
                            'title' => 'Top Things to Do in '.$name.' Tours & Attractions | TourBeez' ,
                            'description' => 'Enjoy unforgettable experiences in '.$name.'. Explore tours, attractions & activities with TourBeez. Reserve your perfect '.$name.' trip today.',
                            'keywords' => 'Things To Do In '.$name,
                            'image' => uploaded_asset( $d->upload_id ) ?? asset('public/tourbeez-logo.jpg'),
                            'url' => url()->current(),
                            'items' => $items,
                            'file' => 'listing',
                            'heading' => "All $name Tours & Excursions in 2026"
                        ]); 
                    }
                                       
                }               
            }
        }

        return $next($request);
    }

    
}
