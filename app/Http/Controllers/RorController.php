<?php

namespace App\Http\Controllers;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;
use App\Models\Category;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class RorController extends Controller
{
    
    // Main Sitemap Index
    public function index()
    {
        $tourLimit = 50000;
        $tourCount = Tour::where('status', 1)->count();
        $tourPage  = (int) ceil($tourCount / $tourLimit);

        $sitemap = SitemapIndex::create()
            ->add(url('/ror/categories.xml'))
            ->add(url('/ror/destinations.xml'))
            ->add(url('/ror/pages.xml'));

        for ($i = 1; $i <= $tourPage; $i++) {
            $sitemap->add(url("/ror/tours-{$i}.xml"));
        }

        return $sitemap->toResponse(request());
    }

    // Categories Sitemap
    public function categories()
    {
        $categories = Category::all();
        return response()
            ->view('ror.categories', compact('categories'))
            ->header('Content-Type', 'application/xml');
    }

    // Destinations Sitemap
    public function destinations()
    {
        $cities = DB::table('tour_locations as tl')
                ->join('cities as c', 'c.id', '=', 'tl.city_id')
                ->select('c.id', 'c.name', 'c.updated_at')
                ->groupBy('c.id', 'c.name') 
                ->orderByRaw('c.name ASC') 
                ->get();

        $states = DB::table('tour_locations as tl')
                ->join('states as s', 's.id', '=', 'tl.state_id')
                ->select('s.id', 's.name', 's.updated_at')
                ->groupBy('s.id', 's.name') 
                ->orderByRaw('s.name ASC') 
                ->get();
        $countries = DB::table('tour_locations as tl')
                ->join('countries as c', 'c.id', '=', 'tl.country_id')
                ->select('c.id', 'c.name', 'c.updated_at')
                ->groupBy('c.id', 'c.name') 
                ->orderByRaw('c.name ASC') 
                ->get();

        // foreach($cities as $d) {
        //     echo 'Redirect /'. Str::slug($d->name).'/'.$d->id.'/c1 /things-to-do-in-'. Str::slug($d->name).'/'.$d->id.'-c1<br>';
        // }
        // foreach($countries as $d) {
        //     echo 'Redirect /'. Str::slug($d->name).'/'.$d->id.'/c2 /things-to-do-in-'. Str::slug($d->name).'/'.$d->id.'-c2<br>';
        // }
        // $categories = Category::all(); 
        // foreach($categories as $d) {
        //     echo 'Redirect /'. Str::slug($d->name).'/'.$d->id.'/c3 /things-to-do-in-'. Str::slug($d->name).'/'.$d->id.'-c3<br>';
        // }
        // foreach($states as $d) {
        //     echo 'Redirect /'. Str::slug($d->name).'/'.$d->id.'/s1 /things-to-do-in-'. Str::slug($d->name).'/'.$d->id.'-s1<br>';
        // }
        // exit;    

        return response()
            ->view('ror.destinations', compact('cities', 'states', 'countries'))
            ->header('Content-Type', 'application/xml');
    }

    // Pages Sitemap
    public function pages() 
    {
        $pages = collect([
            [ 'title' => 'Tours, Activities &amp; Travel Experiences Worldwide | TourBeez', 'href' => '/', 'description' => 'Discover unforgettable travel experiences with TourBeez. Book tours, activities, and tickets to top global destinations with ease and confidence. Explore, adventure, and enjoy every moment' ],
            [ 'title' => 'Top Travel Destinations, Tours & Activities Worldwide | TourBeez', 'href' => '/destinations', 'description' => 'Browse top travel destinations, tours and activities with TourBeez. Find and book great experiences now with easy booking and best ticket deals.' ],
            [ 'title' => 'Book Tickets for Tours & Experiences, Fast Online Booking | TourBeez', 'href' => '/tickets', 'description' => 'Get tickets for tours, attractions and activities with TourBeez. Book now with easy booking and great deals on top experiences.' ],
            [ 'title' => 'Meet TourBeez, Expert Tour & Activity Booking Service', 'href' => '/about-us', 'description' => 'Get to know TourBeez and our mission for easy tour and activity bookings. Book top experiences with great prices today.' ],
            [ 'title' => 'Contact TourBeez, Get Help with Tours & Bookings', 'href' => '/contact-us', 'description' => 'Reach out to TourBeez for support on tours, bookings and tickets. Contact us now for quick assistance and easy travel help.' ],
            [ 'title' => 'Save Your Favourite Tours & Tickets | TourBeez Wishlist', 'href' => '/wishlist', 'description' => 'Keep track of your favourite tours, attractions, and tickets with your TourBeez Wishlist. Easily revisit your favourites, compare options, and book when you\'re ready' ],
            [ 'title' => 'Terms and Conditions, Booking Rules & User Agreement | TourBeez', 'href' => '/terms-and-conditions', 'description' => 'Read TourBeez terms and conditions for bookings, usage and responsibilities. Get clear info on rules and agreements before you book.' ],
            [ 'title' => 'Privacy Policy, Data Protection & User Privacy | TourBeez', 'href' => '/privacy-policy', 'description' => 'Read TourBeez privacy policy to understand how we protect your data and personal information. Get clear details on privacy practices today.' ],
            [ 'title' => 'Cancellation Policy, Refund Terms & Booking Changes | TourBeez', 'href' => '/cancellation-policy', 'description' => 'Read TourBeez cancellation policy and refund terms. Get clear info on booking changes, refunds and how to cancel tours with ease.' ],
            [ 'title' => 'Access Your Account & Manage Bookings | TourBeez Login', 'href' => '/login', 'description' => 'Log in to your TourBeez account to view and manage your tours, tickets, and wishlist. Secure access for fast booking history, updates, and personalized deals' ],
            [ 'title' => 'Partner with TourBeez, Supplier & Tour Provider Opportunities | TourBeez', 'href' => '/supplier', 'description' => 'Join TourBeez as a supplier to list your tours and activities. Partner now for more bookings and easy platform access.' ],
            [ 'title' => 'Careers', 'href' => 'https://www.indeed.com/cmp/Tour-Beez-Inc', 'description' => "" ],
            [ 'title' => 'TourBeez Blog | Travel Stories, Destination Guides &amp; Tips', 'href' => '/blog', 'description' => "Discover the world with TourBeez Blog — your go-to source for travel inspiration, destination insights, and smart tips to make every journey memorable." ],
        ])->map(fn ($p) => (object) $p);

        return response()
            ->view('ror.pages', compact('pages'))
            ->header('Content-Type', 'application/xml');
    }

    // Tours Sitemap    
    public function tours()
    {
        $limit  = 5000;
        $page   = 1;
        $tours  = Tour::select('id', 'slug', 'title')
                    ->with(['detail:id,tour_id,description,meta_title,meta_description'])                    
                    ->limit($limit)
                    ->offset(($page - 1) * $limit)
                    ->get();

        return response()
            ->view('ror.tours', compact('tours'))
            ->header('Content-Type', 'application/xml');
    }
}
