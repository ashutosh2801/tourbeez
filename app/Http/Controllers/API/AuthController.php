<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Mail\EmailManager;
use Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
 {
	
	public function signup(Request $request)
	 {
		$validator=Validator::make($request->all(), [
			'name'		=> 'required|string|max:255', 
			'email'		=> 'required|email|unique:users,email', 
			'password'	=> 'required|string|min:3', 
			'avatar'	=> 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
		
		if($validator->fails()) {
			return response()->json([
				'status'=>false, 
				'message'=>'Validation Error', 
				'errors'=>$validator->errors(), 
			], 422);
		}
		
		$avatarPath=null;
		
		if($request->hasFile('avatar')) {
			$avatar=$request->file('avatar');
			$avatarName=time().'_'.uniqid().'.'.$avatar->getClientOriginalExtension();
			$avatar->move(public_path('uploads/all'), $avatarName);
			$avatarPath='uploads/all/'.$avatarName;
		}

		
		$user=User::create([
			'user_type'		=> "member", 
			'name'			=> $request->name, 
			'email'			=> $request->email, 
			'password'		=> Hash::make($request->password), 
			'phonenumber'	=> $request->phone_number, 
		]);
		
        $message = "Thank you ! Your Registration has been Successfully!!";
        Mail::to($user->email)->send(new EmailManager($user,$message));
		$token=$user->createToken('api_token')->plainTextToken;
		
		return response()->json([
			'status'		=> true, 
			'message'		=> 'User registered successfully', 
			'token'			=> $token, 
			'remember_token'=> $user->remember_token, 
			'data'			=>$user, 
		], 201);
	}
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
    public function forgot_password(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
        $request->only('email')
        );

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => __($status)])
        : response()->json(['message' => __($status)], 400);
    }
    
    public function logout(Request $request)
    {
        if (!$request->user()) 
        {
            return response()->json([
                'status' => false,
                'message' => 'User already logged out or token invalid',
            ], 401);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }
}
