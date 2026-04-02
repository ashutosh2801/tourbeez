<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Services\ApiService;
use Illuminate\Http\Request;
use App\Models\Tour;
use Illuminate\Support\Facades\Response;
use Str;

class TestController extends Controller
{
    public function test() {
        // ----- Adjust for /tbadmin/ subfolder -----
        $path = "/things-to-do-in-niagara-falls/48315-c1";
        $prefix = '';
        if (str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
        }

        $segments = explode('/', $path); 
        // print_r($segments); exit;
                
        $citySlug = $segments[0];   // things-to-do-in-toronto
        $slug_id  = explode("-",$segments[1]);   // 10519-c1
        $id       = $slug_id[0];
        $type     = $slug_id[1];   // c1

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

        $apiService = new ApiService();
        $items = $apiService->request(
                        'get', 'https://tourbeez.com/api/popular-destinations?page=1&limit=10', [],
                        ['apiKey' => 'eyJpdiI6Ill5T0I5WGRNcHowVDFvYU51eHRUQkE9PSIsInZhbHVlIjoiT3']
                    );

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
