<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\CommonMail;
use App\Mail\ContactMail;
use App\Models\Category;
use App\Models\City;
use App\Models\Tour;
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
                ->join('cities as c', 'c.id', '=', 'tl.city_id')
                ->join('states as s', 's.id', '=', 'c.state_id')
                ->join('countries as co', 'co.id', '=', 's.country_id')
                ->whereIn('co.name', ['Canada', 'United States'])
                ->where('co.status', 'active')
                ->where('s.status', 'active')
                ->where('c.status', 'active')
                ->select('c.id', 'c.name', 'c.upload_id')
                ->groupBy('c.id', 'c.name', 'c.upload_id')
                ->inRandomOrder()
                ->limit(15)
                ->get();
        });

        $cities = [];
        foreach($data as $d) {
            $cities[] = [
                'id'    => $d->id,
                'name'  => ucfirst( $d->name ),
                // 'url'   => '/'.$d->id.'/'.Str::slug( $d->name ),
                'url'   => '/c1/'.$d->id.'/'.Str::slug( $d->name ),
                'image' => uploaded_asset( $d->upload_id ),
                'extra' => ''
            ];
        }  


        $tour_data = Cache::remember('tours_home_list', 86400, function () {
            return  DB::table(DB::raw('(SELECT t.id, t.title as name, t.slug, t.price, t.created_at, u.upload_id 
                    FROM tours t 
                    JOIN tour_upload u ON u.tour_id = t.id 
                    WHERE t.status = 1 AND t.deleted_at IS NULL 
                    ORDER BY t.created_at DESC) as sub'))
                    ->groupBy('name')
                    ->limit(25)
                    ->get();
        });

        $tours = [];
        foreach($tour_data as $d) {
            $tours[] = [
                'id'    => $d->id,
                'name'  => ucfirst( $d->name ),
                'url'   => '/tour/'.$d->slug,
                'image' => uploaded_asset( $d->upload_id ),
                'price' => $d->price
            ];
        }  
        
        //Blog
        $blog_data = Cache::remember('tb_blog_home_list', 86400, function () {
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
        // $cacheKey = 'popular_cities_list_'. $request->id;
        // $data = Cache::remember($cacheKey, 86400, function () {
            $data =   DB::table('tour_locations as tl')
                    ->join('cities as c', 'c.id', '=', 'tl.city_id')
                    ->select('c.id', 'c.name', 'c.upload_id')
                    ->distinct()
                    ->orderBy('c.name')
                    ->limit(25)
                    ->get();
        // });

        $cities = [];
        foreach($data as $d) {
            $cities[] = [
                'id'    => $d->id,
                'name'  => ucfirst( $d->name ),
                'url'   => '/c1/'.$d->id.'/'.Str::slug( $d->name ),
                'image' => uploaded_asset( $d->upload_id ),
                'extra' => ''
            ];
        }  

        return response()->json(['status' => true, 'popular_cities' => $cities], 200);
    }

    public function single_city(Request $request, $id)
    {
        $d = Cache::remember('single_city', 86400, function() use ($id) {
            return  City::findOrFail( $id );
        });

        $data = [];
        $data['city'] = [
            'id'    => $d->id,
            'name'  => ucfirst( $d->name ),
            'url'   => '/'.$d->id.'/'.Str::slug( $d->name ),
            'image' => uploaded_asset( $d->upload_id ),
        ];
        $data['state'] = [
            'id'    => $d->state->id,
            'name'  => ucfirst( $d->state->name ),
            'url'   => '/'.$d->state->id.'/'.Str::slug( $d->state->name ),
            'image' => $d->state->upload_id ? uploaded_asset( $d->state->upload_id ) : '',
        ];
        $data['country'] = [
            'id'    => $d->state->country->id,
            'name'  => ucfirst( $d->state->country->name ),
            'url'   => '/'.$d->state->country->id.'/'.Str::slug( $d->state->country->name ),
            'image' => $d->state?->country?->upload_id ? uploaded_asset( $d->state->country->upload_id ) : '',
        ];

        return response()->json(['status' => true, 'data' => $data], 200);
    }
    
    public function contact(Request $request)
    {
        $this->rules = [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'required',
            'message' => 'required|string',
            'recaptcha_token' => 'required',
        ];
        $this->messages = [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'phone.required' => 'Phone number is required.',    
            'message.required' => 'Message is required.',
            'recaptcha_token.required' => 'reCAPTCHA token is required.',
        ];

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

        // Send to user
        Mail::to($request->email)->send(new CommonMail($parsedSubject, $parsedBody));

        // Load Admin template && Send to admin
        $template = fetch_email_template('contact_mail_for_admin');
        $parsedBody = parseTemplate($template->body, $placeholders);
        $parsedSubject = parseTemplate($template->subject, $placeholders);
        Mail::to( get_setting('MAIL_FROM_ADDRESS') )->send(new CommonMail($parsedSubject, $parsedBody));

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
            'recaptcha_token.required' => 'reCAPTCHA token is required.',
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
        ];

        $parsedBody = parseTemplate($template->body, $placeholders);
        $parsedSubject = parseTemplate($template->subject, $placeholders);
     
        // Send to user
        Mail::to($request->email)->send(new CommonMail($parsedSubject, $parsedBody));
        
        // Send email using mailable and template
       
        return response()->json(['message' => 'Message sent successfully.']);
    }
    
}
