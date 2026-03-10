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
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class TestController extends Controller
{
    public function seotest(Request $request) 
    {  
        return response()->view('share.seo', [
                        'title' => 'Access Your Account & Manage Bookings | TourBeez Login',
                        'description' => 'Log in to your TourBeez account to view and manage your tours, tickets, and wishlist. Secure access for fast booking history, updates, and personalized deals',
                        'keywords' => 'TourBeez login, account login, manage bookings, user account, tour booking account',
                        'image' => asset('public/images/login-banner.jpg'),
                        'file' => 'login'
                    ]);                                       
    }

    public function formtest() {
        $url = "https://tourbeez.com/toniagara/tour/best-value-niagara-falls-day-tour-from-toronto-pickups-from-toronto-mississauga";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
    }
}
