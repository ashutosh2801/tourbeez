<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class SitemapController extends Controller
{
    public function home(Request $request) 
    {  
        $citySlug = 'toronto';
        $id       = '10519';
        $type     = 'c1';
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

            $paginated = $query->paginate(24);

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
                'city' => $d,
                'file' => 'listing',
                'heading' => "All $name Tours & Excursions in 2026"
            ]); 
        }
                                       
    }

    // Main Sitemap Index
    public function index()
    {
        $tourLimit = 50000;
        $tourCount = Tour::where('status', 1)->count();
        $tourPage  = (int) ceil($tourCount / $tourLimit);

        $sitemap = SitemapIndex::create()
            ->add(url('/sitemap/categories.xml'))
            ->add(url('/sitemap/destinations.xml'))
            ->add(url('/sitemap/pages.xml'));

        for ($i = 1; $i <= $tourPage; $i++) {
            $sitemap->add(url("/sitemap/tours-{$i}.xml"));
        }

        return $sitemap->toResponse(request());
    }

    // Categories Sitemap
    public function categories()
    {
        $sitemap = Sitemap::create();

        foreach (Category::all() as $category) {
            $sitemap->add(
                Url::create(url("https://tourbeez.com/things-to-do-in-{$category->slug}/{$category->id}-c3"))
                    ->setLastModificationDate($category->updated_at)
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.8)
            );
        }

        return $sitemap->toResponse(request());
    }

    // Destinations Sitemap
    public function destinations()
    {
        $sitemap = Sitemap::create();

        $cities = DB::table('tour_locations as tl')
                ->join('cities as c', 'c.id', '=', 'tl.city_id')
                ->select('c.id', 'c.name', 'c.updated_at')
                ->groupBy('c.id', 'c.name') 
                ->orderByRaw('c.name ASC') 
                ->get();
        foreach ($cities as $destination) {
            $slug = Str::slug($destination->name);
            $sitemap->add(
                Url::create(url("https://tourbeez.com/things-to-do-in-{$slug}/{$destination->id}-c1"))
                    ->setLastModificationDate(Carbon::parse($destination->updated_at))
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.8)
            );
        }

        $states = DB::table('tour_locations as tl')
                ->join('states as s', 's.id', '=', 'tl.state_id')
                ->select('s.id', 's.name', 's.updated_at')
                ->groupBy('s.id', 's.name') 
                ->orderByRaw('s.name ASC') 
                ->get();
        foreach ($states as $destination) {
            $slug = Str::slug($destination->name);
            $sitemap->add(
                Url::create(url("https://tourbeez.com/things-to-do-in-{$slug}/{$destination->id}-s1"))
                    ->setLastModificationDate(Carbon::parse($destination->updated_at))
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.8)
            );
        }

        $countries = DB::table('tour_locations as tl')
                ->join('countries as c', 'c.id', '=', 'tl.country_id')
                ->select('c.id', 'c.name', 'c.updated_at')
                ->groupBy('c.id', 'c.name') 
                ->orderByRaw('c.name ASC') 
                ->get();
        foreach ($countries as $destination) {
            $slug = Str::slug($destination->name);
            $sitemap->add(
                Url::create(url("https://tourbeez.com/things-to-do-in-{$slug}/{$destination->id}-c2"))
                    ->setLastModificationDate(Carbon::parse($destination->updated_at))
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.8)
            );
        }

        return $sitemap->toResponse(request());
    }

    // Pages Sitemap
    public function pages() 
    {
        $pages = [
            [ 'title' => 'Home', 'href' => '/' ],
            [ 'title' => 'Destinations', 'href' => '/destinations' ],
            [ 'title' => 'Tickets', 'href' => '/tickets' ],
            [ 'title' => 'Our Story', 'href' => '/about-us' ],
            // [ 'title' => 'Careers', 'href' => 'https://www.indeed.com/cmp/Tour-Beez-Inc' ],
            [ 'title' => 'Blog', 'href' => '/blog' ],
            [ 'title' => 'Wishlist', 'href' => '/wishlist' ],
            [ 'title' => 'Suppliers', 'href' => '/supplier' ],
            [ 'title' => 'Contact Us', 'href' => '/contact-us' ],
            [ 'title' => 'Cancellation options', 'href' => '/cancellation-policy' ],
            [ 'title' => 'Privacy Policy', 'href' => '/privacy-policy' ],
            [ 'title' => 'Terms & Conditions', 'href' => '/terms-and-conditions' ],
            [ 'title' => 'Niagara Falls Tours From Toronto', 'href' => '/niagara-falls-tour-from-toronto/381-c3' ],
        ];

        $sitemap = Sitemap::create();
        $date = date('Y-m-d h:i:s');
        foreach ($pages as $page) {
            $sitemap->add(
                Url::create(url($page['href']))
                    ->setLastModificationDate(Carbon::parse($date))
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.9)
            );
        }

        return $sitemap->toResponse(request());
    }

    // Tours Sitemap    
    public function tours()
    {
        $sitemap = Sitemap::create();
        $limit   = 5000;
        $page = 1;
        $tours   = Tour::limit($limit)
                    ->offset(($page - 1) * $limit)
                    ->get();

        foreach ($tours as $tour) {
            $sitemap->add(
                Url::create(url("https://tourbeez.com/tour/{$tour->slug}"))
                    ->setLastModificationDate($tour->updated_at)
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.9)
            );
        }

        return $sitemap->toResponse(request());
    }
}
