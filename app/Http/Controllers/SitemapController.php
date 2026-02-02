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
    public function home() {         

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
    // Main Sitemap Index
    public function index()
    {
        return SitemapIndex::create()
            ->add(url('/sitemaps/categories.xml'))
            ->add(url('/sitemaps/destinations.xml'))
            ->add(url('/sitemaps/tours.xml'))
            ->add(url('/sitemaps/pages.xml'))
            ->toResponse(request());
    }

    // Categories Sitemap
    public function categories()
    {
        $sitemap = Sitemap::create();

        foreach (Category::all() as $category) {
            $sitemap->add(
                Url::create(url("https://tourbeez.com/{$category->slug}/{$category->id}/c2"))
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
                Url::create(url("https://tourbeez.com/{$slug}/{$destination->id}/c1"))
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
                Url::create(url("https://tourbeez.com/{$slug}/{$destination->id}/s1"))
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
                Url::create(url("https://tourbeez.com/{$slug}/{$destination->id}/c2"))
                    ->setLastModificationDate(Carbon::parse($destination->updated_at))
                    ->setChangeFrequency('weekly')
                    ->setPriority(0.8)
            );
        }

        return $sitemap->toResponse(request());
    }

    // Tours Sitemap
    public function tours()
    {
        $sitemap = Sitemap::create();
        $tours = Tour::all();

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
