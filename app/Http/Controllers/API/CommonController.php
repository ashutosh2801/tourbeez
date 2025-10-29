<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\CommonMail;
use App\Mail\ContactMail;
use App\Models\Banner;
use App\Models\Category;
use App\Models\City;
use App\Models\Contact;
use App\Models\Country;
use App\Models\State;
use App\Models\Tour;
use App\Models\User;
use App\Notifications\NewContactNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CommonController extends Controller
{
    protected $rules = [];
    protected $messages = [];
    /**
     * Display a cities, blog and destinations of the tours.
     */
    public function home_listing(Request $request)
    {
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
                    ->limit(50)
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
                ->limit(25)
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
                'name'  => ucfirst($b->name),
                'url'   => ('https://tourbeez.com/blog/' . $b->slug), // or $b->guid if using permalink
                'image' => $image_url,
                'date' => date('d M, Y', strtotime($b->post_date))
            ];
        }

        return response()->json(['status' => true, 'popular_cities' => $cities, 'home_tours' => $tours, 'home_blogs' => $blogs], 200);
    }

    public function popular_cities(Request $request)
    {

        $data = DB::table('tour_locations as tl')
                ->join('tours as t', 't.id', '=', 'tl.tour_id')
                ->join('cities as c', 'c.id', '=', 'tl.city_id')
                ->join('uploads as u', 'u.id', '=', 'c.upload_id')
                ->select('c.id', 'c.name', 'c.upload_id')
                ->distinct()
                ->orderByRaw('RAND()') // ✅ Correct way to randomize rows
                ->where('c.upload_id', '>=', 1)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('tour_schedules as ts')
                        ->whereColumn('ts.tour_id', 't.id'); // ✅ corrected alias
                    })            
                ->limit(25)
                ->get();

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

        return response()->json(['status' => true, 'popular_cities' => $cities], 200);
    }



    public function popular_destinations(Request $request)
    {
        $limit = $request->input('limit', 15); 
        $page = $request->input('page', 1);  

        $query = DB::table('tour_locations as tl')
                ->join('tours as t', 't.id', '=', 'tl.tour_id')
                ->join('cities as c', 'c.id', '=', 'tl.city_id')
                ->leftJoin('states as s', 's.id', '=', 'tl.state_id')
                ->leftJoin('countries as co', 'co.id', '=', 'tl.country_id')
                ->select(
                    'c.id',
                    'c.name',
                    'c.upload_id',
                    'tl.state_id',
                    'tl.country_id',
                    's.name as state_name',
                    'co.name as country_name',
                    DB::raw('(
                        SELECT COUNT(DISTINCT t2.id)
                        FROM tours t2
                        JOIN tour_locations tl2 ON tl2.tour_id = t2.id
                        WHERE tl2.city_id = c.id
                        AND t2.status = 1
                        AND t2.deleted_at IS NULL
                        AND EXISTS (
                            SELECT 1
                            FROM tour_schedules ts2
                            WHERE ts2.tour_id = t2.id
                                AND ts2.until_date >= CURDATE()
                        )
                    ) as total_tours')
                )
                ->distinct()
                ->where('c.upload_id' , '>=', 1)
                ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('tour_schedules as ts')
                                ->whereColumn('ts.tour_id', 't.id')
                                ->where('ts.until_date', '>=', DB::raw('CURDATE()'));
                        })
                ->orderByRaw('RAND()');

        $paginated = $query->paginate($limit, ['*'], 'page', $page);

        $cities = [];
        foreach ($paginated->items() as $d) {
            $cities[] = [
                'id'    => $d->id,
                'name'  => 'Things to do in ' . ucfirst($d->name),
                'url'   => '/' . Str::slug($d->name) . '/' . $d->id . '/c1',
                'image' => uploaded_asset($d->upload_id),
                'extra' => ucwords($d->state_name) . ', ' . ucwords($d->country_name),
                'total_tours' => $d->total_tours
            ];
        }

        return response()->json([
            'status'         => true,
            'popular_cities' => $cities,
            'current_page'   => $paginated->currentPage(),
            'last_page'      => $paginated->lastPage(),
            'per_page'       => $paginated->perPage(),
            'total'          => $paginated->total(),
            'next_page_url'  => $paginated->nextPageUrl(),
            'prev_page_url'  => $paginated->previousPageUrl(),
        ]);
    }

    public function single_city(Request $request, $id)
    {
        $type = $request->input('type', 'city'); // Default to 'city' if not provided

        $cacheKey = 'single_city_' . $id . '_' . $type;
        $d = Cache::remember($cacheKey, 86400, function() use ($id, $type) {
            if ($type == 's1') {
                return State::findOrFail($id);
            } elseif ($type == 'c2') {
                return Country::findOrFail($id);
            } elseif ($type == 'c3') {
                return Category::findOrFail($id);
            }
            // Default case for 'city'
            return City::findOrFail( $id );
        });
        
        $meta_title = countThingsToDo($id, $type).' Things To Do In ' .ucfirst( $d->name ).' | ' .env('APP_NAME');
        $meta_description = 'Discover tour in '.ucfirst( $d->name ).'. Enjoy unforgettable experiences, attractions, and adventures with TourBeez.';

        $data = [];
        // Prepare the response data based on the  city type
        if ($type == 'c1') {
                $data['city'] = [
                    'id'    => $d->id,
                    'name'  => ucfirst( $d->name ),
                    'url'   => '/'.Str::slug( $d->name ).'/'.$d->id.'/c1',
                    'image' => uploaded_asset( $d->upload_id ),
                    'meta_title'      => $meta_title,
                    'meta_description'=> $meta_description,
                ];
        }

        // Prepare the response data based on the  city and state type
        if ( $type == 's1' ) {
            $data['state'] = [
                'id'    => $d->state->id,
                'name'  => 'Things to do in '.ucfirst( $d->state->name ),
                'url'   => '/'.Str::slug( $d->state->name ).'/'.$d->state->id.'/s1',
                'image' => $d->state->upload_id ? uploaded_asset( $d->state->upload_id ) : '',
                'meta_title'      => $meta_title,
                'meta_description'=> $meta_description,
            ];
        }

        // Prepare the response data based on the  city, state and country type
        if ( $type == 'c2' ) {
            $data['country'] = [
                'id'    => $d->state->country->id,
                'name'  => 'Things to do in '.ucfirst( $d->state->country->name ),
                'url'   => '/'.Str::slug( $d->state->country->name ).'/'.$d->state->country->id.'/c2',
                'image' => $d->state?->country?->upload_id ? uploaded_asset( $d->state->country->upload_id ) : '',
                'meta_title'      => $meta_title,
                'meta_description'=> $meta_description,
            ];
        }

        if ( $type == 'c3' ) {
            $data['country'] = [
                'id'    => $d->id,
                'name'  => 'Things to do in '.ucfirst( $d->name ),
                'url'   => '/'.Str::slug( $d->name ).'/'.$d->id.'/c3',
                // 'image' => $d->upload_id ? uploaded_asset( $d->upload_id ) : '',
                'meta_title'      => $meta_title,
                'meta_description'=> $meta_description,
            ];
        }

        return response()->json(['status' => true, 'data' => $data], 200);
    }

    public function recommendations(Request $request)
    {
        $ids = $request->input('ids', []);

        $recommended = Tour::whereIn('id', $ids)
            ->inRandomOrder()
            ->limit(4)
            ->paginate(4);

        $items = [];
        foreach ($recommended->items() as $d) {

            $galleries = [];
            if(count($d->galleries)>0) {
                foreach( $d->galleries as $g ) {
                    $image      = uploaded_asset($g->id);
                    $medium_url = str_replace($g->file_name, $g->medium_name, $image);
                    $thumb_url  = str_replace($g->file_name, $g->thumb_name, $image);
                    $galleries[] = [
                        'original_image' => $image,
                        'medium_image'   => $medium_url,
                        'thumb_image'    => $thumb_url,
                    ];
                }
            }
            else {
                $image      = uploaded_asset($d->main_image->id);
                $medium_url = str_replace($d->main_image->file_name, $d->main_image->medium_name, $image);
                $thumb_url  = str_replace($d->main_image->file_name, $d->main_image->thumb_name, $image);
                $galleries[] = [
                    'original_image' => $image,
                    'medium_image'   => $medium_url,
                    'thumb_image'    => $thumb_url,
                ];
            }

            $duration = $d->schedule?->estimated_duration_num.' ' ?? '';
            $duration.= ucfirst($d->schedule?->estimated_duration_unit ?? '');

            $items[] = [
                'id'             => $d->id,
                'title'          => $d->title,
                'slug'           => $d->slug,
                'unique_code'    => $d->unique_code,
                'all_images'     => $galleries,
                //'catogory'       => $d->catogory,
                'price'          => price_format($d->price),
                'original_price' => $d->price,
                'duration'       => trim($duration),
                'rating'         => randomFloat(4, 5),
                'comment'        => rand(50, 100),
            ];
        }    

        return response()->json(['status' => true, 'data' => $items], 200);
    }
    
    public function contact(Request $request)
    {
        $this->rules = [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'required',
            'message' => 'required|string',
            // 'recaptcha_token' => 'required',
        ];
        $this->messages = [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'phone.required' => 'Phone number is required.',    
            'message.required' => 'Message is required.',
            // 'recaptcha_token.required' => 'reCAPTCHA token is required.',
        ];

        // $validated = Validator::make($request->all(), $this->rules, $this->messages);
        // if ($validated->fails()) {
        //     return response()->json(['status' => false, 'errors' => $validated->errors()], 422);
        // }        

        // $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        //     'secret'   => env('RECAPTCHA_SECRET'),
        //     'response' => $request->recaptcha_token,
        //     'remoteip' => $request->ip(),
        // ]);

        // $result = $response->json();

        // if (!($result['success'] ?? false)) {
        //     return response()->json(['message' => 'reCAPTCHA validation failed.'], 422);
        // }

        // Send email using mailable and template
        //Mail::to('ashutosh2801@gmail.com')->send(new ContactMail($validated));
        // Load template
        $template = fetch_email_template('contact_mail');

        // Parse placeholders
        $placeholders = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
            'year' => date('Y'),
            'app_name' => get_setting('site_name'),
        ];

        $parsedBody = parseTemplate($template->body, $placeholders);
        $parsedSubject = parseTemplate($template->subject, $placeholders);

         $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
        ]);

        // Send to user
        Mail::to($request->email)->send(new CommonMail($parsedSubject, $parsedBody));

        // Load Admin template && Send to admin
        $template = fetch_email_template('contact_mail_for_admin');
        $parsedBody = parseTemplate($template->body, $placeholders);
        $parsedSubject = parseTemplate($template->subject, $placeholders);
        Mail::to([ env('MAIL_FROM_ADDRESS'), 'kiran@tourbeez.com' ])->send(new CommonMail($parsedSubject, $parsedBody));

        $admin = User::where('role', 'Super Admin')->first();

        if ($admin) {
            $admin->notify(new NewContactNotification([
                'id' => $contact->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message,
            ]));
        }
        return response()->json(['message' => 'Message sent successfully.']);
    }

    public function careers(Request $request)
    {

        $validated = $request->validate([
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => 'required|email',
            'speciality'       => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'gender'           => 'required|string|max:20',
            'experience'       => 'required|string|min:0',
            'cv'               => 'nullable',
            // 'cv'               => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'recaptcha_token'  => 'required',
        ], [
            'first_name.required'      => 'First name is required.',
            'last_name.required'       => 'Last name is required.',
            'email.required'           => 'Email is required.',
            'email.email'              => 'Enter a valid email address.',
            'speciality.required'      => 'Speciality is required.',
            'phone.required'           => 'Phone number is required.',
            'gender.required'          => 'Gender is required.',
            'gender.in'                => 'Gender must be Male, Female, or Other.',
            'experience.required'      => 'Experience is required.',
            'experience.string'       => 'Experience must be a string.',
            // 'cv.file'                  => 'CV must be a file.',
            // 'cv.mimes'                 => 'CV must be a PDF or Word document.',
            // 'cv.max'                   => 'CV must not be larger than 2MB.',
            // 'recaptcha_token.required' => 'reCAPTCHA token is required.',
        ]);

        if ($request->hasFile('cv')) {
            $request->validate([
                'cv' => 'file|mimes:pdf,doc,docx|max:2048'
            ]);
        }

        $validated = Validator::make($request->all(), $this->rules, $this->messages);
        if ($validated->fails()) {
            return response()->json(['status' => false, 'errors' => $validated->errors()], 422);
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('RECAPTCHA_SECRET'),
            'response' => $request->recaptcha_token,
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        if (!($result['success'] ?? false)) {
            return response()->json(['message' => 'reCAPTCHA validation failed.'], 422);
        }

        $template = fetch_email_template('career_mail');

        // Parse placeholders 
        $placeholders = [
            'name' => $request->first_name . " " . $request->last_name,
            'email' => $request->email,
            'message' => $request->message,
            'year' => date('Y'),
            'app_name' => get_setting('site_name'),
            'app_name' => get_setting('site_name'),
            'login_url' => config('app.site_url') .  "/login",
            'speciality' => $request->speciality,
            'experience' => $request->experience,
            'phone' => $request->phone,
            'gender' => $request->gender,
        ];

        $parsedBody = parseTemplate($template->body, $placeholders);
        $parsedSubject = parseTemplate($template->subject, $placeholders);
     
        // Send to user
        Mail::to($request->email)->send(new CommonMail($parsedSubject, $parsedBody));
        $template = fetch_email_template('career_mail_for_admin');
        $parsedBody = parseTemplate($template->body, $placeholders);
        $parsedSubject = parseTemplate($template->subject, $placeholders);

        Mail::to([env('MAIL_FROM_ADMIN_ADDRESS'), 'kiran@tourbeez.com'])->send(new CommonMail($parsedSubject, $parsedBody));
        
        // Send email using mailable and template
       
        return response()->json(['message' => 'Message sent successfully.']);
    }

    

    public function getLocationBanner(Request $request){

        $banner = Banner::where('location_id', $request->location_id)->first();

        if(!$banner){
            return Banner::first();
        }
    }

    
}
