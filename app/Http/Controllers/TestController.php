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
