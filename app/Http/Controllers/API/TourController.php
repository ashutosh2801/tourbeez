<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
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
            });
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

            $duration = $d->schedule?->estimated_duration_num ?? '';
            $duration.= ' '.ucfirst($d->schedule?->estimated_duration_unit ?? '');
            $duration = $duration ?? 'NA';

            $items[] = [
                'id'             => $d->id,
                'title'          => $d->title,
                'slug'           => $d->slug,
                'unique_code'    => $d->unique_code,
                'all_images'     => $galleries,
                //'catogory'       => $d->catogory,
                'price'          => price_format($d->price),
                'original_price' => $d->price,
                'duration'       => $duration,
                'rating'         => randomFloat(4, 5),
                'comment'        => rand(50, 100),
            ];
        }

        // Return the transformed data along with pagination info
        return response()->json([
            'status'         => true,
            'data'           => $items,
            'current_page'   => $paginated->currentPage(),
            'last_page'      => $paginated->lastPage(),
            'per_page'       => $paginated->perPage(),
            'total'          => $paginated->total(),
            'next_page_url'  => $paginated->nextPageUrl(),
            'prev_page_url'  => $paginated->previousPageUrl(),
        ]);
    }
    
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
                'original_img' => $image,
                'medium_img' => $medium_url,
                'thumb_img' => $thumb_url
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
                'galleries'     => $galleries,
                'categories'    => $tour->categories,
                'tourtypes'     => $tour->tourtypes,
                'pickups'       => $tour->pickups,
                'itineraries'   => $tour->itineraries,
                'itinerariesAll'=> $tour->itinerariesAll,
                'faqs'          => $tour->faqs,
                'inclusions'    => $tour->inclusions,
                'exclusions'    => $tour->exclusions,
                'taxes_fees'    => $tour->taxes_fees,
                'detail'        => $tour->detail,
                'location'      => $tour->location,
                'breadcrumbs'   => $breadcrumbs,
                'schedule'      => $tour->schedule,
                'pricings'      => $tour->pricings,
                'category'      => $tour->category,
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $formattedTour
        ]);
    }

    /** Search home page  */
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
                $data[] = ['icon'=>'city', 'title' => ucfirst($city->name), 'slug' => 'c1/'.Str::slug($city->name).'/'.$city->id, 'address' => ucfirst($city->state?->name).', '.ucfirst($city->state?->country?->name)];
            }
        }
        if($total_categories>0) {
            foreach($categories as $category) {
                $data[] = ['icon'=>'category', 'title' => ucfirst($category->name), 'slug' => 'c3/'.$category->slug.'/'.$category->id , 'address' => ''];
            }
        }
        if($tours->count()>0) {
            foreach($tours as $tour) {
                $image  = uploaded_asset($tour->main_image->id, 'thumb');
                $data[] = ['icon'=>$image, 'title' => ucfirst($tour->title), 'slug' => 'tour/'.$tour->slug, 'address' => $tour->location->address];
            }
        }
        
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'No records found!'], 404);
        }

        // Return the transformed data along with pagination info
        return response()->json([
            'status'  => true,
            'data'    => $data,
        ]);
    }
}
