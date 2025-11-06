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
            //->add(url('/sitemaps/categories.xml'))
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

        $desinations = DB::table('tour_locations as tl')
                ->join('cities as c', 'c.id', '=', 'tl.city_id')
                ->select('c.id', 'c.name', 'c.upload_id', 'c.updated_at')
                ->groupBy('c.id', 'c.name', 'c.upload_id') 
                ->orderByRaw('RAND()') 
                ->get();

        foreach ($desinations as $destination) {
            $slug = Str::slug($destination->name);
            $sitemap->add(
                Url::create(url("https://tourbeez.com/c1/{$destination->id}/{$slug}"))
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

        foreach (Tour::all() as $tour) {
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
