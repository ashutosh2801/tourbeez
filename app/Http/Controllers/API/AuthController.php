<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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

        $user = User::create([
            'first_name'=> $request->first_name,
            'last_name' => $request->last_name,
            'name'      => $request->first_name . ' ' . $request->last_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ]);

        $user->id  = $user->id;
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'user' => $user,
            'token' => $token,
        ]);
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

        // $validated = $request->validate([
        //     'first_name'=> 'required|string|max:100',
        //     'last_name' => 'required|string|max:100',
        //     'email'     => 'required|email|unique:users,email,' . $user->id,
        //     'phone'     => 'nullable|string|max:20',
        //     'dob'       => 'nullable|string|max:20',
        //     'country'   => 'nullable|string|max:100',
        // ]);
        
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
}
