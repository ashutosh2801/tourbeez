<?php
use App\Models\Setting;
use App\Models\Tour;
use App\Models\Translation;

// use App\Models\EmailTemplate;
// use App\Models\SmsTemplate;
// use App\Models\Notification;
use App\Upload;
use App\Models\TourUpload;
use App\User;
use Ashutosh2801\Colorcodeconverter\Colorcodeconverter;
use Carbon\Carbon;

if(!function_exists('ordinal')) {
    function ordinal($number) {
        $ends = ['th','st','nd','rd','th','th','th','th','th','th'];
        if ((($number % 100) >= 11) && (($number % 100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }
}

if(!function_exists('price_format')) {
    function price_format($value)
    {
        return '$'.number_format($value, 2);
    }
}

if(!function_exists('taxes_format')) {
    function taxes_format($type, $value)
    {
        if($type == 'PERCENT')
        return number_format($value,1).'%';
        elseif($type == 'FIXED_PER_ORDER')
        return '$'.number_format($value, 2);
    }
}

if(!function_exists('date__format')) {
    function date__format($value)
    {
        return date('M d, Y H:i', strtotime($value));
    }
}

if (!function_exists('site_url')) {
    function site_url()
    {
        return !empty(env('APP_URL')) ? env('APP_URL') : url('');
    }
}

if (! function_exists('getTourPricingDetails')) {
    function getTourPricingDetails($data, $tour_pricing_id=0)
    {
        foreach ($data as $item) {
            if (isset($item->tour_pricing_id) && $item->tour_pricing_id == $tour_pricing_id) {
                return [
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            }
        }

        return null;
    }
}

if (! function_exists('get_tax')) {
    function get_tax($subtotal, $type, $value) {
        if($type == 'PERCENT') {
            return ($subtotal * ($value/100));
        }
        elseif($type == 'FIXED_PER_ORDER') {
            return $value;
        }
        return 0;
    }
}

//highlights the selected navigation on admin panel
if (! function_exists('areActiveRoutes')) {
    function areActiveRoutes(Array $routes, $output = "active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }
    }
}


//return file uploaded via uploader
if (!function_exists('uploaded_asset')) {
    function uploaded_asset($id)
    {
        if (($asset = Upload::find($id)) != null) {
            return static_asset($asset->file_name);
        }
        return static_asset('placeholder.jpg');
    }
}

if (!function_exists('main_image_html')) {
    function main_image_html($id, $type='thumb')
    {
        $image = uploaded_asset($id);
        if($image != null) {
            $img = '<img class="img-md" src="'. $image .'" height="45px"  alt="'. translate('photo') .'">';
        }
        else {
            $img = '<img class="img-md" src="'. static_asset('assets/img/avatar-place.png') .'" height="45px"  alt="'. translate('photo') .'">';
        }

        // $data = $this->hasOne(TourImage::class)->where('is_main', 1);
        // if(isset($data->image) && public_path('tour/' . $data->image) ) {
        //     $image_file = asset('tour/'.$data->image);
        //     return '<img src="'.$image_file.'" alt="'.$this->title.'" width="'.$width.'" />';
        // }
        return $img;
    }
}

/**
 * Generate an asset path for the application.
 *
 * @param  string  $path
 * @param  bool|null  $secure
 * @return string
 */
if (! function_exists('static_asset')) {
    function static_asset($path, $secure = null)
    {
        if(env('FILESYSTEM_DRIVER') == 's3'){
            return Storage::disk('s3')->url($path);
        }
        else {
            return app('url')->asset($path, $secure);
        }
    }
}

if (!function_exists('isHttps')) {
    function isHttps()
    {
        return !empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS']);
    }
}


if (!function_exists('getBaseURL')) {
    function getBaseURL()
    {
        $root = '//' . $_SERVER['HTTP_HOST'];
        $root.= '/admin/';
        //$root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

        return $root;
    }
}

if (!function_exists('getFileBaseURL')) {
    function getFileBaseURL()
    {
        if(env('FILESYSTEM_DRIVER') == 's3'){
            return env('AWS_URL').'/';
        }
        else {
            //return getBaseURL().'public/';
            $root = '//' . $_SERVER['HTTP_HOST'];
            $root.= '/';
            return $root;
        }
    }
}

if (!function_exists('translate')) {
    function translate($key, $lang = null)
    {
        if($lang == null){
            $lang = App::getLocale();
        }

        $lang_key = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($key)));

        $translations_default = Cache::rememberForever('translations-'.env('DEFAULT_LANGUAGE', 'en'), function () {
            return Translation::where('lang', env('DEFAULT_LANGUAGE', 'en'))->pluck('lang_value', 'lang_key')->toArray();
        });

        if(!isset($translations_default[$lang_key])){
            $translation_def = new Translation;
            $translation_def->lang = env('DEFAULT_LANGUAGE', 'en');
            $translation_def->lang_key = $lang_key;
            $translation_def->lang_value = $key;
            $translation_def->save();
            Cache::forget('translations-'.env('DEFAULT_LANGUAGE', 'en'));
        }

        $translation_locale = Cache::rememberForever('translations-'.$lang, function () use ($lang) {
            return Translation::where('lang', $lang)->pluck('lang_value', 'lang_key')->toArray();
        });

        //Check for session lang
        if(isset($translation_locale[$lang_key])){
            return $translation_locale[$lang_key];
        }
        elseif(isset($translations_default[$lang_key])){
            return $translations_default[$lang_key];
        }
        else{
            return $key;
        }
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Get settings value
if (!function_exists('get_setting')) {
    function get_setting($key, $default = null)
    {
        $settings = Cache::remember('settings', 86400, function () {
            return Setting::all();
        });

        $setting = $settings->where('type', $key)->first();
        
        return $setting == null ? $default : $setting->value;
    }
}

// email template data
if (!function_exists('get_email_template')) {
    function get_email_template($identifier, $colmn_name = null)
    {
        $value = EmailTemplate::where('identifier', $identifier)->first()->$colmn_name;
        return $value;
    }
}

// SMS template data
if (!function_exists('get_sms_template')) {
    function get_sms_template($identifier, $colmn_name = null)
    {
        $value = SmsTemplate::where('identifier', $identifier)->first()->$colmn_name;
        return $value;
    }
}


// Addon Activation Check
if (!function_exists('addon_activation')) {
    function addon_activation($identifier, $default = null)
    {
        $activation = Addon::where('unique_identifier', $identifier)->where('activated',1)->first();
        return $activation == null ? false : true;
    }
}

// Send SMS
if (! function_exists('sendSMS')) {
    function sendSMS($to, $from, $text, $template_id)
    {
        if (get_setting('nexmo_activation') == 1) {
            $api_key = env("NEXMO_KEY"); //put ssl provided api_token here
            $api_secret = env("NEXMO_SECRET"); // put ssl provided sid here

            $params = [
                "api_key" => $api_key,
                "api_secret" => $api_secret,
                "from" => $from,
                "text" => $text,
                "to" => $to
            ];

            $url = "https://rest.nexmo.com/sms/json";
            $params = json_encode($params);

            $ch = curl_init(); // Initialize cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
                'accept:application/json'
            ));
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        elseif (get_setting('twillo_activation') == 1) {
            $sid = env("TWILIO_SID"); // Your Account SID from www.twilio.com/console
            $token = env("TWILIO_AUTH_TOKEN"); // Your Auth Token from www.twilio.com/console

            $client = new Client($sid, $token);
            try {
                $message = $client->messages->create(
                  $to, // Text this number
                  array(
                    'from' => env('VALID_TWILLO_NUMBER'), // From a valid Twilio number
                    'body' => $text
                  )
                );
            } catch (\Exception $e) {

            }

        }
        elseif (get_setting('ssl_wireless_activation') == 1) {
            $token = env("SSL_SMS_API_TOKEN"); //put ssl provided api_token here
            $sid = env("SSL_SMS_SID"); // put ssl provided sid here

            $params = [
                "api_token" => $token,
                "sid" => $sid,
                "msisdn" => $to,
                "sms" => $text,
                "csms_id" => date('dmYhhmi').rand(10000, 99999)
            ];

            $url = env("SSL_SMS_URL");
            $params = json_encode($params);

            $ch = curl_init(); // Initialize cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
                'accept:application/json'
            ));

            $response = curl_exec($ch);

            curl_close($ch);

            return $response;
        }
        elseif (get_setting('fast2sms_activation')== 1) {

            if(strpos($to, '+91') !== false){
                $to = substr($to, 3);
            }

            if(env("ROUTE") == 'dlt_manual'){
                $fields = array(
                    "sender_id" => env("SENDER_ID"),
                    "message" => $text,
                    "template_id" => $template_id,
                    "entity_id" => env("ENTITY_ID"),
                    "language" => env("LANGUAGE"),
                    "route" => env("ROUTE"),
                    "numbers" => $to,
                );
            }
            else {
                $fields = array(
                    "sender_id" => env("SENDER_ID"),
                    "message" => $text,
                    "language" => env("LANGUAGE"),
                    "route" => env("ROUTE"),
                    "numbers" => $to,
                );
            }


            $auth_key = env('AUTH_KEY');

            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_SSL_VERIFYHOST => 0,
              CURLOPT_SSL_VERIFYPEER => 0,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($fields),
              CURLOPT_HTTPHEADER => array(
                "authorization: $auth_key",
                "accept: */*",
                "cache-control: no-cache",
                "content-type: application/json"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            return $response;
        }
        elseif (get_setting('mimo_activation')== 1) {
            $token = MimoUtility::getToken();

            MimoUtility::sendMessage($text, $to, $token);
            MimoUtility::logout($token);
        }
        elseif (get_setting('mimsms_activation') == 1) {
            $url = "https://esms.mimsms.com/smsapi";
              $data = [
                "api_key" => env('MIM_API_KEY'),
                "type" => "text",
                "contacts" => $to,
                "senderid" => env('MIM_SENDER_ID'),
                "msg" => $text,
              ];
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              $response = curl_exec($ch);
              curl_close($ch);
              return $response;
        }
    }
}

// system configurations value
if (!function_exists('get_remaining_package_value')) {
    function get_remaining_package_value($id, $colmn_name)
    {
      $value = Member::where('user_id', $id)->first()->$colmn_name;
      return $value;
    }
}

//
if (!function_exists('package_validity')) {
    function package_validity($id)
    {
      $package_validity = Member::where('user_id', $id)->first()->package_validity;
      if( $package_validity == null || ($package_validity < date('Y-m-d')) ){
          return false;
      }
      else{
          return true;
      }
    }
}

//formats price to home default price with convertion
if (! function_exists('single_price')) {
    function single_price($price)
    {
        return format_price(convert_price($price));
    }
}

//converts currency to home default currency
if (! function_exists('convert_price')) {
    function convert_price($price)
    {
        $business_settings = Setting::where('type', 'system_default_currency')->first();
        if($business_settings != null){
            $currency = Currency::find($business_settings->value);
            $price = floatval($price) / floatval($currency->exchange_rate);
        }

        $code       = Currency::findOrFail( get_setting('system_default_currency') )->code;
        $currency   = Currency::where('code', $code)->first();
        $price      = floatval($price) * floatval($currency->exchange_rate);

        return $price;
    }
}

//formats currency
if (! function_exists('format_price')) {
    function format_price($price)
    {
        if (get_setting('decimal_separator') == 1) {
            $fomated_price = number_format($price, get_setting('no_of_decimals'));
        }
        else {
            $fomated_price = number_format($price, get_setting('no_of_decimals') , ',' , ' ');
        }

        if(get_setting('symbol_format') == 1){
            return currency_symbol().$fomated_price;
        }
        return $fomated_price.currency_symbol();
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol()
    {
        $code       = Currency::findOrFail( get_setting('system_default_currency') )->code;
        $currency   = Currency::where('code', $code)->first();
        return $currency->symbol;
    }
}

if (! function_exists('order_status')) {
    function order_status($val)
    {
        switch($val) {
            case 1:
                return '<span class="badge badge-inline badge-success">New</span>';
                break;
            case 2:
                return '<span class="badge badge-inline badge-danger">On Hold</span>';
                break;
            case 3:
                return '<span class="badge badge-inline badge-danger">Pending supplier</span>';
                break; 
            case 4:
                return '<span class="badge badge-inline badge-warning">Pending customer</span>';
                break;
            case 5:
                return '<span class="badge badge-inline badge-warning">Confirmed</span>';
                break;
            case 6:
                return '<span class="badge badge-inline badge-warning">Cancelled</span>';   
                break;  
            case 7:
                return '<span class="badge badge-inline badge-warning">Abandoned cart</span>';   
                break; 
            default:
                return '<span class="badge badge-inline badge-warning">Cancelled</span>';   
                break;   
        }
    }
}

if (! function_exists('order_status_list')) {
    function order_status_list()
    {
        return [
            1 => "New",
            2 => "On Hold",
            3 => "Pending supplier",
            4 => "Pending customer",
            5 => "Confirmed",
            6 => "Cancelled",
            7 => "Abandoned cart"
        ];
    }
}

if (! function_exists('membership_type')) {
    function membership_type($id)
    {
        if($id == 1) 
        return 'CIO';
        else 
        return 'Vendor';
    }
}


// Unique code create and check
if (!function_exists('unique_code')) {
    function unique_code()
    {
        $id = Tour::withTrashed()->latest('id')->first()->id+1;
        $code = get_setting('tour_code_prifix').date('Ym').$id;
        return $code;
    }
}

// Unique id create and check
if (!function_exists('unique_notify_id')) {
    function unique_notify_id()
    {
        if(Notification::all()->count() > 0)
        {
            return Notification::latest('id')->first()->id+1;
        }
        else {
            return 1;
        }

    }
}



// Filter min value
if (!function_exists('filter_min_value')) {
    function filter_min_value($value)
    {
        return empty($value) || !is_numeric($value) || $value <= 0.00
                ? 0
                : $value;
    }
}


if (!function_exists('chat_threads')) {
    function chat_threads()
    {
        $data  = array();
        if (Auth::check()) {
            foreach (ChatThread::where('sender_user_id', Auth::user()->id)->orWhere('receiver_user_id', Auth::user()->id)->get() as $key => $chat_thread) {
                if(count($chat_thread->chats()->where('sender_user_id', '!=', Auth::user()->id)->where('seen', 0)->get()) > 0){
                    $data[] = $chat_thread->id;
                }
            }
        }

        return $data;
    }
}

if (!function_exists('timezones')) {
    function timezones(){
        $timezones = Array(
            '(GMT-12:00) International Date Line West' => 'Pacific/Kwajalein',
            '(GMT-11:00) Midway Island' => 'Pacific/Midway',
            '(GMT-11:00) Samoa' => 'Pacific/Apia',
            '(GMT-10:00) Hawaii' => 'Pacific/Honolulu',
            '(GMT-09:00) Alaska' => 'America/Anchorage',
            '(GMT-08:00) Pacific Time (US & Canada)' => 'America/Los_Angeles',
            '(GMT-08:00) Tijuana' => 'America/Tijuana',
            '(GMT-07:00) Arizona' => 'America/Phoenix',
            '(GMT-07:00) Mountain Time (US & Canada)' => 'America/Denver',
            '(GMT-07:00) Chihuahua' => 'America/Chihuahua',
            '(GMT-07:00) La Paz' => 'America/Chihuahua',
            '(GMT-07:00) Mazatlan' => 'America/Mazatlan',
            '(GMT-06:00) Central Time (US & Canada)' => 'America/Chicago',
            '(GMT-06:00) Central America' => 'America/Managua',
            '(GMT-06:00) Guadalajara' => 'America/Mexico_City',
            '(GMT-06:00) Mexico City' => 'America/Mexico_City',
            '(GMT-06:00) Monterrey' => 'America/Monterrey',
            '(GMT-06:00) Saskatchewan' => 'America/Regina',
            '(GMT-05:00) Eastern Time (US & Canada)' => 'America/New_York',
            '(GMT-05:00) Indiana (East)' => 'America/Indiana/Indianapolis',
            '(GMT-05:00) Bogota' => 'America/Bogota',
            '(GMT-05:00) Lima' => 'America/Lima',
            '(GMT-05:00) Quito' => 'America/Bogota',
            '(GMT-04:00) Atlantic Time (Canada)' => 'America/Halifax',
            '(GMT-04:00) Caracas' => 'America/Caracas',
            '(GMT-04:00) La Paz' => 'America/La_Paz',
            '(GMT-04:00) Santiago' => 'America/Santiago',
            '(GMT-03:30) Newfoundland' => 'America/St_Johns',
            '(GMT-03:00) Brasilia' => 'America/Sao_Paulo',
            '(GMT-03:00) Buenos Aires' => 'America/Argentina/Buenos_Aires',
            '(GMT-03:00) Georgetown' => 'America/Argentina/Buenos_Aires',
            '(GMT-03:00) Greenland' => 'America/Godthab',
            '(GMT-02:00) Mid-Atlantic' => 'America/Noronha',
            '(GMT-01:00) Azores' => 'Atlantic/Azores',
            '(GMT-01:00) Cape Verde Is.' => 'Atlantic/Cape_Verde',
            '(GMT) Casablanca' => 'Africa/Casablanca',
            '(GMT) Dublin' => 'Europe/London',
            '(GMT) Edinburgh' => 'Europe/London',
            '(GMT) Lisbon' => 'Europe/Lisbon',
            '(GMT) London' => 'Europe/London',
            '(GMT) UTC' => 'UTC',
            '(GMT) Monrovia' => 'Africa/Monrovia',
            '(GMT+01:00) Amsterdam' => 'Europe/Amsterdam',
            '(GMT+01:00) Belgrade' => 'Europe/Belgrade',
            '(GMT+01:00) Berlin' => 'Europe/Berlin',
            '(GMT+01:00) Bern' => 'Europe/Berlin',
            '(GMT+01:00) Bratislava' => 'Europe/Bratislava',
            '(GMT+01:00) Brussels' => 'Europe/Brussels',
            '(GMT+01:00) Budapest' => 'Europe/Budapest',
            '(GMT+01:00) Copenhagen' => 'Europe/Copenhagen',
            '(GMT+01:00) Ljubljana' => 'Europe/Ljubljana',
            '(GMT+01:00) Madrid' => 'Europe/Madrid',
            '(GMT+01:00) Paris' => 'Europe/Paris',
            '(GMT+01:00) Prague' => 'Europe/Prague',
            '(GMT+01:00) Rome' => 'Europe/Rome',
            '(GMT+01:00) Sarajevo' => 'Europe/Sarajevo',
            '(GMT+01:00) Skopje' => 'Europe/Skopje',
            '(GMT+01:00) Stockholm' => 'Europe/Stockholm',
            '(GMT+01:00) Vienna' => 'Europe/Vienna',
            '(GMT+01:00) Warsaw' => 'Europe/Warsaw',
            '(GMT+01:00) West Central Africa' => 'Africa/Lagos',
            '(GMT+01:00) Zagreb' => 'Europe/Zagreb',
            '(GMT+02:00) Athens' => 'Europe/Athens',
            '(GMT+02:00) Bucharest' => 'Europe/Bucharest',
            '(GMT+02:00) Cairo' => 'Africa/Cairo',
            '(GMT+02:00) Harare' => 'Africa/Harare',
            '(GMT+02:00) Helsinki' => 'Europe/Helsinki',
            '(GMT+02:00) Istanbul' => 'Europe/Istanbul',
            '(GMT+02:00) Jerusalem' => 'Asia/Jerusalem',
            '(GMT+02:00) Kyev' => 'Europe/Kiev',
            '(GMT+02:00) Minsk' => 'Europe/Minsk',
            '(GMT+02:00) Pretoria' => 'Africa/Johannesburg',
            '(GMT+02:00) Riga' => 'Europe/Riga',
            '(GMT+02:00) Sofia' => 'Europe/Sofia',
            '(GMT+02:00) Tallinn' => 'Europe/Tallinn',
            '(GMT+02:00) Vilnius' => 'Europe/Vilnius',
            '(GMT+03:00) Baghdad' => 'Asia/Baghdad',
            '(GMT+03:00) Kuwait' => 'Asia/Kuwait',
            '(GMT+03:00) Moscow' => 'Europe/Moscow',
            '(GMT+03:00) Nairobi' => 'Africa/Nairobi',
            '(GMT+03:00) Riyadh' => 'Asia/Riyadh',
            '(GMT+03:00) St. Petersburg' => 'Europe/Moscow',
            '(GMT+03:00) Volgograd' => 'Europe/Volgograd',
            '(GMT+03:30) Tehran' => 'Asia/Tehran',
            '(GMT+04:00) Abu Dhabi' => 'Asia/Muscat',
            '(GMT+04:00) Baku' => 'Asia/Baku',
            '(GMT+04:00) Muscat' => 'Asia/Muscat',
            '(GMT+04:00) Tbilisi' => 'Asia/Tbilisi',
            '(GMT+04:00) Yerevan' => 'Asia/Yerevan',
            '(GMT+04:30) Kabul' => 'Asia/Kabul',
            '(GMT+05:00) Ekaterinburg' => 'Asia/Yekaterinburg',
            '(GMT+05:00) Islamabad' => 'Asia/Karachi',
            '(GMT+05:00) Karachi' => 'Asia/Karachi',
            '(GMT+05:00) Tashkent' => 'Asia/Tashkent',
            '(GMT+05:30) Chennai' => 'Asia/Kolkata',
            '(GMT+05:30) Kolkata' => 'Asia/Kolkata',
            '(GMT+05:30) Mumbai' => 'Asia/Kolkata',
            '(GMT+05:30) New Delhi' => 'Asia/Kolkata',
            '(GMT+05:45) Kathmandu' => 'Asia/Kathmandu',
            '(GMT+06:00) Almaty' => 'Asia/Almaty',
            '(GMT+06:00) Astana' => 'Asia/Dhaka',
            '(GMT+06:00) Dhaka' => 'Asia/Dhaka',
            '(GMT+06:00) Novosibirsk' => 'Asia/Novosibirsk',
            '(GMT+06:00) Sri Jayawardenepura' => 'Asia/Colombo',
            '(GMT+06:30) Rangoon' => 'Asia/Rangoon',
            '(GMT+07:00) Bangkok' => 'Asia/Bangkok',
            '(GMT+07:00) Hanoi' => 'Asia/Bangkok',
            '(GMT+07:00) Jakarta' => 'Asia/Jakarta',
            '(GMT+07:00) Krasnoyarsk' => 'Asia/Krasnoyarsk',
            '(GMT+08:00) Beijing' => 'Asia/Hong_Kong',
            '(GMT+08:00) Chongqing' => 'Asia/Chongqing',
            '(GMT+08:00) Hong Kong' => 'Asia/Hong_Kong',
            '(GMT+08:00) Irkutsk' => 'Asia/Irkutsk',
            '(GMT+08:00) Kuala Lumpur' => 'Asia/Kuala_Lumpur',
            '(GMT+08:00) Perth' => 'Australia/Perth',
            '(GMT+08:00) Singapore' => 'Asia/Singapore',
            '(GMT+08:00) Taipei' => 'Asia/Taipei',
            '(GMT+08:00) Ulaan Bataar' => 'Asia/Irkutsk',
            '(GMT+08:00) Urumqi' => 'Asia/Urumqi',
            '(GMT+09:00) Osaka' => 'Asia/Tokyo',
            '(GMT+09:00) Sapporo' => 'Asia/Tokyo',
            '(GMT+09:00) Seoul' => 'Asia/Seoul',
            '(GMT+09:00) Tokyo' => 'Asia/Tokyo',
            '(GMT+09:00) Yakutsk' => 'Asia/Yakutsk',
            '(GMT+09:30) Adelaide' => 'Australia/Adelaide',
            '(GMT+09:30) Darwin' => 'Australia/Darwin',
            '(GMT+10:00) Brisbane' => 'Australia/Brisbane',
            '(GMT+10:00) Canberra' => 'Australia/Sydney',
            '(GMT+10:00) Guam' => 'Pacific/Guam',
            '(GMT+10:00) Hobart' => 'Australia/Hobart',
            '(GMT+10:00) Melbourne' => 'Australia/Melbourne',
            '(GMT+10:00) Port Moresby' => 'Pacific/Port_Moresby',
            '(GMT+10:00) Sydney' => 'Australia/Sydney',
            '(GMT+10:00) Vladivostok' => 'Asia/Vladivostok',
            '(GMT+11:00) Magadan' => 'Asia/Magadan',
            '(GMT+11:00) New Caledonia' => 'Asia/Magadan',
            '(GMT+11:00) Solomon Is.' => 'Asia/Magadan',
            '(GMT+12:00) Auckland' => 'Pacific/Auckland',
            '(GMT+12:00) Fiji' => 'Pacific/Fiji',
            '(GMT+12:00) Kamchatka' => 'Asia/Kamchatka',
            '(GMT+12:00) Marshall Is.' => 'Pacific/Fiji',
            '(GMT+12:00) Wellington' => 'Pacific/Auckland',
            '(GMT+13:00) Nuku\'alofa' => 'Pacific/Tongatapu'
        );

        return $timezones;
    }
}

if (!function_exists('app_timezone')) {
    function app_timezone()
    {
        return config('app.timezone');
    }
}

if (!function_exists('hex2rgba')) {
    function hex2rgba($color, $opacity = false) {
        return Colorcodeconverter::convertHexToRgba($color, $opacity);
    }
}

if (!function_exists('get_max_date')) {
    function get_max_date()
    {
        $member_min_age = get_setting('member_min_age') != null ? get_setting('member_min_age') : 0;
        $current_date = Carbon::now();
        $max_date = $current_date->subYears($member_min_age);
        return date("Y-m-d", strtotime($max_date));
    }
}

if (!function_exists('show_profile_picture')) {
    function show_profile_picture($user)
    {
        $profile_picture_privacy = get_setting('profile_picture_privacy');
        if(Auth::check()){
            $profile_picture_show = true;

            if(Auth::user()->id != $user->id)
            {
                if ($user->photo != null && $user->photo_approved == 1) {
                    if ($profile_picture_privacy == 'only_me') {
                        $profile_picture_show = false;
                        $photo_view_request = \App\Models\ViewProfilePicture::where('user_id', $user->id)->where('requested_by',Auth::user()->id)->first();
                        if($photo_view_request != null && $photo_view_request->status == 1){
                            $profile_picture_show = true; 
                        }
                    }
                    elseif ($profile_picture_privacy == 'premium_members') {
                        if (Auth::user()->membership == 1) {
                            $profile_picture_show = false;
                        }
                    }
                }
                elseif($user->photo == null || $user->photo_approved == 0){
                    $profile_picture_show = false;
                } 
            }
        }
        else{
            $profile_picture_show = false;
            if($profile_picture_privacy == 'all' && $user->photo != null && $user->photo_approved == 1){
                $profile_picture_show = true;
            }
        }
        return $profile_picture_show;
    }
}

if (!function_exists('checkWebsiteSeoContent')) {
    
function checkWebsiteSeoContent($id, $focusKeyword)
{
    $score = 100;
    $passedChecks = [];
    $failedChecks = [];
    $warningChecks = [];

    $focusKeyword = trim($focusKeyword);
    $isValidKeyword = !empty($focusKeyword) && $focusKeyword !== 'NA';

    $tour = Tour::findOrFail($id);
    $tourDetail = $tour->detail;

    // 1. Page Title Check
        $title = trim($tour->title);

        if (!$title) {
            $failedChecks[] = "❌ Page Title is missing.";
            $score -= 10;
        } elseif (strlen($title) < 30 || strlen($title) > 60) {
            $warningChecks[] = "⚠️ Page Title is not optimal (recommended: 30–60 characters). Current: " . strlen($title);
            $score -= 5;
        } else {
            $passedChecks[] = "✅ Page Title is present and optimal. (+5)";
            $score += 5;
        }

        // Check focus keyword in title if valid
        if ($isValidKeyword) {
            if (stripos($title, $focusKeyword) === false) {
                $failedChecks[] = "❌ Focus keyword not found in the Page Title.";
                $score -= 5;
            } else {
                $passedChecks[] = "✅ Focus keyword found in the Page Title. (+5)";
                $score += 5;
            }
        }
    // 2. Meta Description & Content Gathering
    $addons = Tour::with('addonsAll')->find($id);
    $itinerary = Tour::with('itinerariesAll')->find($id);

    $brief = $tourDetail->description ?? '';
    $long = $tourDetail->long_description ?? '';
    $metadescription = $tourDetail->meta_description ?? '';

    $itineraryDescriptions = $itinerary->itinerariesAll->pluck('description')->implode(' ');
    $addonDescriptions = $addons->addonsAll->pluck('description')->implode(' ');

    $combinedContent = $brief . ' ' . $long . ' ' . $addonDescriptions . ' ' . $itineraryDescriptions;
    $description = trim($combinedContent);

    if (!$description) {
        $failedChecks[] = "❌ Meta Description is missing.";
        $score -= 15;
    } elseif (strlen($description) < 50 || strlen($description) > 160) {
        $warningChecks[] = "⚠️ Meta Description is not optimal (recommended: 50–160 characters). Current: " . strlen($description);
        $score -= 5;
    } else {
        $passedChecks[] = "✅ Meta Description is present and optimal.";
    }

    if ($isValidKeyword && stripos($description, $focusKeyword) === false) {
        $failedChecks[] = "❌ Focus keyword not found in Meta Description.";
        $score -= 5;
    } elseif ($isValidKeyword) {
        $passedChecks[] = "✅ Focus keyword found in Meta Description.";
    }

    // 3. H1 Tag
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $description) || !empty($tour->title)) {
        $passedChecks[] = "✅ H1 tag is present.";
    } else {
        $failedChecks[] = "❌ H1 tag is missing.";
        $score -= 10;
    }

    // 4. First Paragraph Keyword Check
    if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $description, $matches)) {
        $firstParagraph = strtolower(strip_tags(trim($matches[1])));
        if ($isValidKeyword && stripos($firstParagraph, $focusKeyword) === false) {
            $failedChecks[] = "❌ Focus keyword not found in the first paragraph.";
            $score -= 5;
        } elseif ($isValidKeyword) {
            $passedChecks[] = "✅ Focus keyword found in the first paragraph.";
        }
    } else {
        $warningChecks[] = "⚠️ No paragraph (<p>) tag found.";
        $score -= 3;
    }

    // 5. Word Count & Keyword Density
    $textContent = strtolower(strip_tags(
        $tour->title . ' ' . $description . ' ' . $description
    ));
    $words = str_word_count($textContent, 1);
    $wordCount = count($words);

    if ($wordCount == 0) {
        $failedChecks[] = "❌ No textual content found.";
        $score -= 20;
    } elseif ($wordCount < 300) {
        $warningChecks[] = "⚠️ Text contains fewer than 300 words (actual: $wordCount).";
        $score -= 7;
    } else {
        $passedChecks[] = "✅ Text contains enough words ($wordCount).";
    }

    $keywordCount = $isValidKeyword ? substr_count($textContent, strtolower($focusKeyword)) : 0;
    $keywordDensity = ($wordCount > 0) ? ($keywordCount / $wordCount) * 100 : 0;

    if ($isValidKeyword) {
        if ($keywordDensity < 1) {
            $failedChecks[] = "❌ Low keyword density (" . round($keywordDensity, 2) . "%). Recommended: 1%–2.5%.";
            $score -= 5;
        } elseif ($keywordDensity > 4) {
            $warningChecks[] = "⚠️ High keyword density (" . round($keywordDensity, 2) . "%). May be considered spam.";
            $score -= 5;
        } else {
            $passedChecks[] = "✅ Keyword density is optimal (" . round($keywordDensity, 2) . "%).";
        }
    }

          // 6. Images
        $imgs = TourUpload::where('tour_id', $id)->whereNotNull('upload_id')->get();

        $missingAlt = 0;
        $keywordInAlt = false;

        if ($imgs->count() === 0) {
            $failedChecks[] = "❌ No images found.";
            $score -= 15;
        } else {
            $passedChecks[] = "✅ Images are present.";

            foreach ($imgs as $img) {
                // Primary alt from tour_uploads table
                $alt = trim($img->alt ?? '');

                // Fallback alt from uploads.title
                $title = trim(optional($img->upload)->title ?? '');

                // Use alt or title
                $finalAlt = $alt !== '' ? $alt : $title;

                if ($finalAlt === '') {
                    $missingAlt++;
                }

                if ($focusKeyword !== 'NA' && stripos($finalAlt, $focusKeyword) !== false) {
                    $keywordInAlt = true;
                }
            }

            // Alt attribute checks
            if ($imgs->count() > 0) {
                $passedChecks[] = "✅ All images have alt attributes.";
            } elseif ($missingAlt <= 2) {
                $warningChecks[] = "⚠️ $missingAlt image(s) missing alt attributes.";
                $score -= 5;
            } elseif ($missingAlt <= 5) {
                $failedChecks[] = "❌ $missingAlt image(s) missing alt attributes.";
                $score -= 10;
            } else {
                $failedChecks[] = "❌ Too many images ($missingAlt) missing alt attributes.";
                $score -= 20;
            }

            // Keyword in alt check
         /*   if (!$keywordInAlt && $focusKeyword !== 'NA') {
                $failedChecks[] = "❌ Focus keyword not found in any image alt attribute.";
                $score -= 5;
            } else {
                $passedChecks[] = "✅ Focus keyword found in image Alt tag.";
            }
                */
        }

    // 7. Content Length & Keyword in Description
    $mainContent = trim(strip_tags($description));
    $fallbackContent = trim(strip_tags($description ?? ''));
    $finalContent = $mainContent !== '' ? $mainContent : $fallbackContent;

    $contentWordCount = str_word_count($finalContent);
    $keywordInContent = $isValidKeyword && stripos($finalContent, $focusKeyword) !== false;

    if ($finalContent === '') {
        $failedChecks[] = "❌ No description content found.";
        $score -= 15;
    } else {
        $passedChecks[] = "✅ Description content is present.";
    }

    if ($contentWordCount < 300) {
        $failedChecks[] = "❌ Your text doesn't contain enough words, a minimum of 300 words is recommended.";
        $score -= 10;
    } else {
        $passedChecks[] = "✅ Description content has sufficient length ($contentWordCount words).";
    }

    if (!$keywordInContent && $isValidKeyword) {
        $failedChecks[] = "❌ Focus keyword not found in description content.";
        $score -= 5;
    } elseif ($isValidKeyword) {
        $passedChecks[] = "✅ Focus keyword found in the description.";
    }

    // 8. Internal & External Links
    $url = url()->current();
    $host = parse_url($url, PHP_URL_HOST);

    $internalLinks = 0;
    $externalLinks = 0;

    libxml_use_internal_errors(true);
    $dom = new \DOMDocument();
    $dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    foreach ($dom->getElementsByTagName('a') as $link) {
        $href = $link->getAttribute('href');
        if (!$href || $href === '#' || stripos($href, 'javascript:') !== false) continue;

        $hrefHost = parse_url($href, PHP_URL_HOST);
        if (!$hrefHost || str_contains($href, $host) || str_starts_with($href, '/')) {
            $internalLinks++;
        } else {
            $externalLinks++;
        }
    }

    // 9. Focus Keyword Presence in All Description Fields with Scoring
    if ($isValidKeyword) {
        $keywordChecks = [
            'Title' => $title,
            'Meta Description' => $metadescription,
            'Brief Description' => $brief,
            'Long Description' => $long,
            'Short Description' => $tour->short_description ?? '',
            'Itinerary Descriptions' => $itineraryDescriptions,
            'Addon Descriptions' => $addonDescriptions,
        ];

        foreach ($keywordChecks as $label => $text) {
            if (stripos($text, $focusKeyword) === false) {
                $warningChecks[] = "⚠️ Focus keyword not found in $label.";
                $score -= 2;
            } else {
                $passedChecks[] = "✅ Focus keyword found in $label.";
                $score += 1;
            }
        }
    }

    // Final score clamping
    $score = max(0, min(100, $score));

    return [
        'score' => $score,
        'percentage' => round($score),
        'passed' => $passedChecks,
        'warning' => $warningChecks,
        'failed' => $failedChecks,
        'wordCount' => $wordCount,
        'keywordDensity' => round($keywordDensity, 2)
    ];
}

}


?>
