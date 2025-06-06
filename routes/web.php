<?php

use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\LoginWithOTPController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\StateController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $readmePath = base_path('WELCOME.md');

    return view('welcome', [
        'readmeContent' => Str::markdown(file_get_contents($readmePath)),
    ]);
});

Route::post('/tour/single', [TourController::class,'single'])->name('tour.single');

Route::post('/states/get_state_by_country', [StateController::class,'get_state_by_country'])->name('states.get_state_by_country');
Route::post('/cities/get_cities_by_state', [CityController::class,'get_cities_by_state'])->name('cities.get_cities_by_state');

// Login with OTP Routes
Route::prefix('/otp')->middleware('guest')->name('otp.')->controller(LoginWithOTPController::class)->group(function(){
    Route::get('/login','login')->name('login');
    Route::post('/generate','generate')->name('generate');
    Route::get('/verification/{userId}','verification')->name('verification');
    Route::post('login/verification','loginWithOtp')->name('loginWithOtp');
});

// Socialite Routes
Route::prefix('oauth/')->group(function(){
    Route::prefix('/github/login')->name('github.')->group(function(){
        Route::get('/',[SocialiteController::class,'redirectToGithub'])->name('login');
        Route::get('/callback',[SocialiteController::class,'HandleGithubCallBack'])->name('callback');
    });

    Route::prefix('/google/login')->name('google.')->group(function(){
        Route::get('/',[SocialiteController::class,'redirectToGoogle'])->name('login');
        Route::get('/callback',[SocialiteController::class,'HandleGoogleCallBack'])->name('callback');        
    });

    Route::prefix('/facebook/login')->name('facebook.')->group(function(){
        Route::get('/',[SocialiteController::class,'redirectToFaceBook'])->name('login');
        Route::get('/callback',[SocialiteController::class,'HandleFaceBookCallBack'])->name('callback');
    });
});



// Auth routes
require __DIR__.'/auth.php';
// Admin Routes
require('admin.php');
