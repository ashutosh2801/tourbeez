<?php

namespace App\Http\Controllers;

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

class SitemapController extends Controller
{
    
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
