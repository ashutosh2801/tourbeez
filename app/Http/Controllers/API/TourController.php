<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Tour;
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
            $query->orderBy('id', 'DESC');
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

            $items[] = [
                'id'             => $d->id,
                'title'          => $d->title,
                'slug'           => $d->slug,
                'unique_code'    => $d->unique_code,
                'all_images'     => $galleries,
                //'catogory'       => $d->catogory,
                'price'          => price_format($d->price),
                'original_price' => $d->price,
                'duration'       => strtolower(trim($duration)),
                'rating'         => randomFloat(4, 5),
                'comment'        => rand(50, 100),
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
        if (!empty($tour->pickups) && isset($tour->pickups[0]) && $tour->pickups[0]?->name !== 'No Pickups') {
            $pickups = $tour->pickups[0]?->locations ?? [];
        } else {
            $pickups[] = 'Pickups';
        }

        


        if ($tour) {
            // 💡 You can now format or transform fields as needed
            $formattedTour = [
                'id'            => $tour->id,
                'title'         => $tour->title,
                'price'         => format_price($tour->price), // formatted price
                'original_price'=> $tour->price, // without formatted price
                'unique_code'   => $tour->unique_code,
                'slug'          => $tour->slug,
                'features'      => $tour->features,
                'meta'          => $tour->meta,
                'categories'    => $tour->categories,
                'tourtypes'     => $tour->tourtypes,
                'itineraries'   => $tour->itineraries,
                'pickups'       => $pickups,
                //'itinerariesAll'=> $tour->itinerariesAll,
                //'schedule'      => $tour->schedule,
                'faqs'          => $tour->faqs,
                'inclusions'    => $tour->inclusions,
                'exclusions'    => $tour->exclusions,
                'taxes_fees'    => $tour->taxes_fees,
                'detail'        => $tour->detail,
                'location'      => $tour->location,
                'breadcrumbs'   => $breadcrumbs,
                'pricings'      => $tour->pricings,
                'category'      => $tour->category,
                'galleries'     => $galleries,
                'addons'        => $addons,
            ];
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
        if($total_categories>0) {
            foreach($categories as $category) {
                $data[] = ['icon'=>'category', 'title' => $this->highlightMatch($category->name, $search), 'slug' => 'c3/'.$category->id.'/'.$category->slug , 'address' => ''];
            }
        }
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
                'duration'       => trim($duration),
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


}
