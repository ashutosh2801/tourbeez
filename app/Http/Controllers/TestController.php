<?php

namespace App\Http\Controllers;

use App\Mail\CommonMail;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderEmailHistory;
use App\Models\State;
use App\Services\ApiService;
use Illuminate\Http\Request;
use App\Models\Tour;
use Illuminate\Support\Facades\Response;
use Mail;
use Str;

class TestController extends Controller
{
    public function test() {

        $apiService = new ApiService();
                    $response = $apiService->request(
                        'get',
                        'https://tourbeez.com/api/home-listing',
                        [],
                        [
                            //'Authorization' => 'Bearer TOKEN',
                            'apiKey' => 'eyJpdiI6Ill5T0I5WGRNcHowVDFvYU51eHRUQkE9PSIsInZhbHVlIjoiT3'
                        ]
                    );
    
                    return response()->view('share.seo', [
                        'title' => 'Tours, Activities & Travel Experiences Worldwide | TourBeez',
                        'description' => 'Discover unforgettable travel experiences with TourBeez. Book tours, activities, and tickets to top global destinations with ease and confidence. Explore, adventure, and enjoy every moment',
                        'keywords' => 'International Tour Packages, Best Travel Deals Worldwide, World Tours And Trips, Customizable Holiday Packages,  Budget-friendly Travel',
                        'image' => 'https://tourbeez.com/logo.jpg',
                        // 'page' => 'home',
                        'file' => 'home',
                        'tours' => $response['home_tours'], 
                        'cities' => $response['popular_cities'], 
                        'blogs' => $response['home_blogs']

                        // "Laravel admin panel me feeback form create karna hai jisme kafi fields honge jo like input, dropdown, radio, checkbox etc and feedback message de sakta hai. jisme sare fields customizable honge aur form submit hone ke baad ek success message show hoga aur kuchh points bhi customer ko milega jo jab feedback create karte samay define hoga. Iske liye ek controller method, model aur ek blade bhi create karna hai."
                    ]);
    }
    public function index(Request $request)
    {
        $query = Tour::select([
                'id', 'title', 'slug', 'unique_code', 'price',
                'coupon_type', 'coupon_value', 'offer_ends_in','currency'
            ])
            ->with([
                'galleries:id,file_name,medium_name,thumb_name',
                'mainImage:id,file_name,medium_name,thumb_name',
                'schedule:id,tour_id,estimated_duration_num,estimated_duration_unit',
                'categories:id',
                'location:id,city_id,state_id,country_id',
                'review:id,tour_id,tag',
            ])
            ->onlyRoot()
            ->where('status', 1)
            ->whereNull('deleted_at');

        $query->whereHas('categories', fn($q) => $q->where('categories.id', 381));

        $query->orderByRaw('(CASE WHEN sort_order > 0 THEN 0 ELSE 1 END) ASC')
                  ->orderBy('sort_order', 'ASC'); // Only sort_order for default

        $items = $query->get();
        // foreach ($items as $item) {
        //         $image = $item->formatted_images;
        //         $array =  [
        //             'K456653443',
        //             $item->title,
        //             $image[0]['original_image'],
        //             $image[1]['original_image'],
        //             '',
        //             '',
        //             'https://tourbeez.com/tour/'.$item->slug
        //         ];

        //         print_r($array); echo '<br>';
        //     }

        // CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="catalog_destination.csv"',
        ];

        $columns = ['destination_id', 'name', 'image[0].url', 'image[0].tag[0]', 'type[0]', 'type[1]', 'url'];

        $callback = function() use($items, $columns) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, $columns);

            foreach ($items as $item) {

                $images = $item->formatted_images ?? [];

                $image1 = isset($images[0]['original_image']) ? $images[0]['original_image'] : '';
                $image2 = isset($images[1]['original_image']) ? $images[1]['original_image'] : '';

                fputcsv($file, [
                    'K456653443',
                    $item->title ?? '',
                    $image1,
                    $image2,
                    '',
                    '',
                    url('/tour/'.$item->slug)
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers); 
            
    }

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
