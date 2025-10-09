<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed', // expects `password_confirmation` field
                'min:8',
                'regex:/[A-Z]/', // uppercase
                'regex:/[0-9]/', // number
            ],
        ];

        $messages = [
            'password.regex' => 'Password must contain at least one uppercase letter and one number.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'first_name'=> $request->first_name,
                'last_name' => $request->last_name,
                'name'      => $request->first_name . ' ' . $request->last_name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
            ]);

            // $user->id  = $user->id;
            $token = $user->createToken('auth_token')->plainTextToken;

            // Load template
            $template = fetch_email_template('user_registration');

            // Parse placeholders
            $placeholders = [
                'name'      => $user->name,
                'email'     => $user->email,
                'app_name'  => config('app.name'),
                'login_url' => config('app.site_url') .  "/login",
                'year'      => date('Y')
            ];

            $parsedBody = parseTemplate($template->body, $placeholders);
            $parsedSubject = parseTemplate($template->subject, $placeholders);

            // Send to user
            Mail::to($user->email)->send(new RegistrationMail($parsedSubject, $parsedBody));

            // Load Admin template && Send to admin
            $template = fetch_email_template('user_registration_for_admin');
            $parsedBody = parseTemplate($template->body, $placeholders);
            $parsedSubject = parseTemplate($template->subject, $placeholders);
            $toRecipient = get_setting('MAIL_FROM_ADDRESS') ?? 'info@tourbeez.com';
            $ccRecipient = 'kiran@tourbeez.com';
            Mail::to( $toRecipient )
            ->cc($ccRecipient)
            ->send(new RegistrationMail($parsedSubject, $parsedBody));

            return response()->json([
                'status' => true,
                'message' => 'Account created successfully.',
                'user' => $user,
                'token' => $token,
            ]);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'user' => [],
                'token' => '',
            ]);
        }
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid email or password!'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        if( $request->session_id ) {
            Order::where('session_id', $request->session_id)->update(['user_id' => $user->id]);
        }

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // Forgot Password
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email.']);
        }

        return response()->json(['message' => 'Unable to send reset link.'], 500);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request, $id)
    {
        $user = User::findorFail($id);
        
        $name = $request->input('first_name') ?? $user->first_name;
        $name.= ' '.$request->input('last_name') ?? $user->last_name;

        $user->update([
            'first_name'=> $request->input('first_name') ?? $user->first_name,
            'last_name' => $request->input('last_name') ?? $user->last_name,
            'name'      => $name,
            'email'     => $request->input('email') ?? $user->email,
            'phone'     => $request->input('phone') ?? $user->phone,
            'dob'       => $request->input('dob') ?? $user->dob,
            'country'   => $request->input('country') ?? $user->country,
        ]);

        return response()->json(['status' => true, 'message' => 'Profile updated successfully', 'posted'=>$request->all(), 'user' => $user]);
    }

    public function password_update(Request $request, $id)
    {
        $user = User::findorFail($id);
        
        $rules = [
            'password' => [
                'required',
                'confirmed', // expects `password_confirmation` field
                'min:8',
                'regex:/[A-Z]/', // uppercase
                'regex:/[0-9]/', // number
            ],
            'password_confirmation' => 'required',
        ];

        $messages = [
            'password.regex' => 'Password must contain at least one uppercase letter and one number.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Current password is not accepted.'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['status' => true, 'message' => 'Password updated successfully', 'user' => $user]);
    }
}
