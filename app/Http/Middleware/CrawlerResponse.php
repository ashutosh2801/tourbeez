<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Cache;
use Closure;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Tour;
use App\Models\Post;

class CrawlerResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $ua = strtolower($request->userAgent());

        $bots = [
            // Social
            'facebookexternalhit', 'facebot', 'twitterbot', 'linkedinbot',
            'pinterest', 'slackbot', 'discordbot', 'whatsapp',
            'telegrambot', 'skypeuripreview', 'teamsbot',

            // Search Engines
            'googlebot', 'bingbot', 'duckduckbot',
            'baiduspider', 'yandex', 'sogou',

            // Others
            'applebot', 'embedly', 'quora link preview',
            'outbrain', 'rogerbot', 'ahrefsbot', 'semrushbot'
        ];

        foreach ($bots as $bot) {
            if (strpos($ua, $bot) !== false) {

                // ----- Adjust for /tbadmin/ subfolder -----
                $path = $request->path();
                $prefix = '';
                if (str_starts_with($path, $prefix)) {
                    $path = substr($path, strlen($prefix));
                }

                // Homepage
                if ($path === '' || $path === '/') {
                    return response()->view('share.seo', [
                        'title' => 'Your Gateway To Amazing Tours & Tickets Worldwide | TourBeez - Going Beeyond',
                        'description' => 'Discover unforgettable travel experiences with TourBeez. Book tours, activities, and tickets to top global destinations with ease and confidence. Explore, adventure, and enjoy every moment',
                        'keywords' => 'travel tours, book tickets online, destination experiences, tour booking site, top travel activities',
                        'image' => asset('public/512x512.jpg'),
                    ]);
                }
                if ($path === 'destinations') {
                    return response()->view('share.seo', [
                        'title' => 'Explore Places Around The World | TourBeez Destinations',
                        'description' => 'Browse TourBeez’s curated list of destinations worldwide. Find travel inspiration, destination guides, and the best tours in every city you want to visit',
                        'keywords' => 'destinations, travel destinations, places to visit, holiday destinations, top destinations tours',
                        'image' => asset('public/images/destination.jpg'),
                    ]);
                }
                if ($path === 'tickets') {
                    return response()->view('share.seo', [
                        'title' => 'Book Your Tour & Activity Tickets Easily | TourBeez Tickets',
                        'description' => 'Buy tickets for tours, shows, and attractions via TourBeez. Find the best-priced tickets with secure booking and skip-the-line options for popular events',
                        'keywords' => 'tour tickets, attraction tickets, show tickets, book tickets online, skip line tickets',
                        'image' => asset('public/images/tickets.jpg'),
                    ]);
                }
                if ($path === 'about-us') {
                    return response()->view('share.seo', [
                        'title' => 'Our Story & Mission In Travel & Tours | About TourBeez',
                        'description' => 'Learn about TourBeez: our mission, values, and dedication to providing exceptional travel experiences. Meet the team behind the tours and tickets you love',
                        'keywords' => 'about TourBeez, travel company story, our mission, tour booking platform, company values, travel partner',
                        'image' => asset('public/images/about-us.jpg'),
                    ]);
                }
                if ($path === 'contact-us') {
                    return response()->view('share.seo', [
                        'title' => 'Get In Touch For Tour Help & Support | Contact TourBeez',
                        'description' => 'Need assistance or have questions? Reach out to TourBeez. We’re here to help you with bookings, suggestions, and any travel-related support',
                        'keywords' => 'contact TourBeez, customer support, travel assistance, tour help, get in touch',
                        'image' => asset('public/images/contact-us.jpg'),
                    ]);
                }
                if ($path === 'wishlist') {
                    return response()->view('share.seo', [
                        'title' => 'Save Your Favourite Tours & Tickets | TourBeez Wishlist',
                        'description' => 'Keep track of your favourite tours, attractions, and tickets with your TourBeez Wishlist. Easily revisit your favourites, compare options, and book when you\'re ready',
                        'keywords' => 'wishlist tours, saved tours, favourite tickets, tour wish list, save tour deals',
                        'image' => asset('public/images/wishlist.jpg'),
                    ]);
                }
                if ($path === 'terms-and-conditions') {
                    return response()->view('share.seo', [
                        'title' => 'TourBeez Booking Rules & Agreements | Terms & Conditions',
                        'description' => 'Review the terms and conditions governing the use of TourBeez. Includes booking rules, user responsibilities, and service agreements',
                        'keywords' => 'terms and conditions, booking terms, user agreement, service terms, legal terms',
                        'image' => asset('public/images/terms-condition.jpg'),
                    ]);
                }
                if ($path === 'privacy-policy') {
                    return response()->view('share.seo', [
                        'title' => 'How TourBeez Protects Your Data | Privacy Policy',
                        'description' => 'Read TourBeez\'s privacy policy. Learn how we collect, use, and protect your personal data when you book tours or use our services',
                        'keywords' => 'privacy policy, data protection, user privacy, personal information, TourBeez security',
                        'image' => asset('public/images/privacy-policy.jpg'),
                    ]);
                }
                if ($path === 'cancellation-policy') {
                    return response()->view('share.seo', [
                        'title' => 'TourBeez Bookings & Refunds | Cancellation Policy',
                        'description' => 'View TourBeez\'s cancellation policy. Understand how booking changes, refunds, and cancellations are handled for tours and tickets',
                        'keywords' => 'cancellation policy, refund policy, tour cancellation, booking changes, ticket refund',
                        'image' => asset('public/images/cancel-policy.jpg'),
                    ]);
                }
                if ($path === 'login') {
                    return response()->view('share.seo', [
                        'title' => 'Access Your Account & Manage Bookings | TourBeez Login',
                        'description' => 'Log in to your TourBeez account to view and manage your tours, tickets, and wishlist. Secure access for fast booking history, updates, and personalized deals',
                        'keywords' => 'lTourBeez login, account login, manage bookings, user account, tour booking account',
                        'image' => asset('public/images/login-banner.jpg'),
                    ]);
                }

                // ----- Tour Page -----
                if (str_starts_with($path, 'tour/')) {
                    $slug = explode('/', $path)[1];
                    $tour = Tour::where('slug', $slug)->first();
                    if ($tour) {
                        $image      = uploaded_asset($tour->main_image->id);
                        //$medium_url = str_replace($tour->main_image->file_name, $tour->main_image->medium_name, $image);
                        $thumb_url  = str_replace($tour->main_image->file_name, $tour->main_image->thumb_name, $image);

                        return response()->view('share.tour', [
                            'title' => $tour->detail->meta_title ?? $tour->detail->name . ' | ' . env('APP_NAME'),
                            'description' => $tour->detail->meta_description ?? $tour->short_description,
                            'image' => $thumb_url ?? asset('public/512x512.jpg'),
                            'url' => url()->current(),
                        ]);
                    }
                }

                $segments = explode('/', $path);
                if (count($segments) == 3) {
                    $citySlug = $segments[0];   // toronto
                    $id       = $segments[1];   // 10519
                    $type     = $segments[2];   // c1
                    $d = null;
                    if ($type == 'c1') {
                        $d = City::findOrFail( $id );
                    }
                    else if ( $type == 's1' ) {
                        $d = State::findOrFail( $id );
                    }
                    else if ( $type == 'c2' ) {
                        $d = Country::findOrFail( $id );
                    }
                    else if ( $type == 'c3' ) {
                        $d = Category::findOrFail( $id );
                    }                    
                    if($d){
                        return response()->view('share.tour', [
                            'title' => countThingsToDo($id, $type).' Things To Do In ' .ucfirst( $d->name ).' | ' .env('APP_NAME') ,
                            'description' => 'Discover tour in '.ucfirst( $d->name ).'. Enjoy unforgettable experiences, attractions, and adventures with TourBeez.',
                            'image' => uploaded_asset( $d->upload_id ) ?? asset('public/512x512.jpg'),
                            'url' => url()->current(),
                        ]); 
                    }
                                       
                }               

                // ----- Static pages -----
                // $page = Page::where('slug', $path)->first();
                // if ($page) {
                //     return response()->view('seo', [
                //         'title' => $page->meta_title,
                //         'description' => $page->meta_description,
                //         'image' => asset('demo/assets/logo-DYnsi7vK.jpg'),
                //         'url' => url()->current(),
                //     ]);
                // }             
            }
        }

        return $next($request);
    }

    
}
