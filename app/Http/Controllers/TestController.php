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

        $orders = Order::with(['order_tour'])
                ->where('order_status', 5)
                // ->where('order_number', 'TAHAGJG')
                ->whereHas('order_tour', function ($query) {
                    $query->where('tour_date', '<', date('2026-04-20'))
                    ->where('tour_date', '>=', date('2026-04-19', strtotime('-1 day')));
                });
        // echo getFullSql($orders);        
        $orders = $orders->get();

        // ✅ No data found
        if ($orders->count() === 0) {
            echo 'No orders found for processing.';
            return;
        }

        // ✅ Email template
        $emailTemplate = EmailTemplate::where('identifier', 'trip_completed')->first();
        $emailSubject = $emailTemplate->subject;
        $emailBody = $emailTemplate->body;

        $i=0;
        foreach($orders as $order) {

            $pickup_address = '';
            if( $order->customer->pickup_name ) {
                $pickup_address = $order->customer->pickup_name;
            }
            else if($order->customer->pickup_id) {
                $pickup_address = $order->customer?->pickup?->location . ' ( '.$order->customer?->pickup?->address.' )';
            }

            // Replace placeholders
            $placeholders = [
                "[[CUSTOMER_NAME]]"         => $order->customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $order->customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => $order->customer->phone ?? '',

                "[[TOUR_TITLE]]"            => $order->tour->title ?? '',
                "[[TOUR_SKU]]"              => $order->tour->unique_code ?? '',
                "[[TOUR_MAP]]"              => $pickup_address ?? '',
                "[[TOUR_ADDRESS]]"          => $order->tour->location->address ?? '',
                "[[TOUR_TERMS_CONDITIONS]]" => $order->tour->terms_and_conditions ?? '',
                "[[PICKUP_ADDRESS]]"        => $pickup_address ?? '',

                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
                "[[ORDER_TOUR_DATE]]"       => $order->order_tour->tour_date ?? '',
                "[[ORDER_TOUR_TIME]]"       => $order->order_tour->tour_time ?? '',
                "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
                "[[ORDER_TOTAL]]"           => price_format_with_currency($order->total_amount, $order->currency) ?? 0,
                "[[ORDER_BALANCE]]"         => price_format_with_currency($order->balance_amount, $order->currency) ?? 0,

                "[[APP_NAME]]"              => get_setting('site_name'),
                "[[COMPANY_NAME]]"          => get_setting('site_name'),
                "[[APP_URL]]"               => get_setting('app_url'),
                "[[APP_EMAIL]]"             => get_setting('app_email'),
                "[[APP_PHONE]]"             => get_setting('app_phone'),
                "[[APP_ADDRESS]]"           => get_setting('app_address'),
                "[[YEAR]]"                  => date('Y'),

            ];
            $subject = strtr($emailSubject, $placeholders);
            $body = strtr($emailBody, $placeholders);

            // Recipients
            $recipients = [
                'email' => $order->customer->email,
                'name'  => $order->customer->name
            ];

            // echo '<pre>'; 
            // print_r([
            //     'subject' => $subject,
            //     'body' => $body,
            //     'recipients' => $recipients
            // ]); 
            // echo '</pre>';
            
            // Explicitly use Mailgun mailer
            $mailer = Mail::mailer('mailgun');

            // Send email and capture message inf
            $sentMessage = $mailer->to([$recipients]);
            $sentMessage->bcc(['tourbeez.com+9768a17f10@invite.trustpilot.com']);            
            $sentMessage = $sentMessage->send(new CommonMail($subject, $body, null, null, null, true));


            // die('Email sent');
            $messageId = null;
            if ($sentMessage instanceof \Illuminate\Mail\SentMessage) {
                $symfonySent = $sentMessage->getSymfonySentMessage();
                if ($symfonySent && method_exists($symfonySent, 'getMessageId')) {
                    $messageId = $symfonySent->getMessageId();
                    $messageId = trim($messageId, '<>');
                }
            }

            // Save to email history table
            OrderEmailHistory::create([
                'order_id'   => $order->id,
                'to_email'   => $order->customer->email ?? '',
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'subject'    => $subject,
                'body'       => $body,
                'status'     => $messageId ? 'sent' : 'failed',
                'message_id' => $messageId, // ✅ store for webhook tracking
            ]);

            if($i++%3==0) sleep(1); // Wait for 1 seconds to ensure email is sent before script ends
        }

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
