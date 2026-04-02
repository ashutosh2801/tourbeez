<?php

use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LoginWithOTPController;
use App\Http\Controllers\RorController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TourController;
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
// Auth routes
// require __DIR__.'/auth.php';
require('auth.php');

// Admin Routes
require('admin.php');

Route::get('/testone', [TestController::class, 'test']);
Route::get('/export', [TestController::class, 'index']);
Route::get('/seotest', [TestController::class, 'seotest']);
Route::get('/formtest', [TestController::class, 'formtest']);

Route::get('/sitemap/index.xml', [SitemapController::class, 'index']);
Route::get('/sitemap/categories.xml', [SitemapController::class, 'categories']);
Route::get('/sitemap/destinations.xml', [SitemapController::class, 'destinations']);
Route::get('/sitemap/tours-{page}.xml', [SitemapController::class, 'tours']);
Route::get('/sitemap/pages.xml', [SitemapController::class, 'pages']);

Route::get('/ror/index.xml', [RorController::class, 'index']);
Route::get('/ror/categories.xml', [RorController::class, 'categories']);
Route::get('/ror/destinations.xml', [RorController::class, 'destinations']);
Route::get('/ror/tours-{page}.xml', [RorController::class, 'tours']);
Route::get('/ror/pages.xml', [RorController::class, 'pages']);

Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');

Route::post('/mailgun/events/{event}', [EmailController::class, 'handle']);
Route::post('/tour/single', [\App\Http\Controllers\API\TourController::class,'single'])->name('tour.single');
Route::post('/tour/calendar', [\App\Http\Controllers\API\TourController::class,'singleCalendar'])->name('tour.calendar');
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




