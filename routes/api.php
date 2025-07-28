<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CommonController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\TourController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('api.key')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['api.key'])->group(function () {
    Route::get('/categories',[CategoryController::class,'index'])->name('categories');
    Route::post('/sub-cateogries',[CategoryController::class,'subcategory'])->name('sub.category');
    
    Route::get('/home-listing',[CommonController::class,'home_listing'])->name('home_listing');
    Route::get('/popular-cities',[CommonController::class,'popular_cities'])->name('popular_cities');
    Route::get('/single-city/{id}',[CommonController::class,'single_city'])->name('single_city');
    Route::post('/contact',[CommonController::class,'contact'])->name('contact');

    Route::get('/tours',[TourController::class,'index'])->name('tour.index');
    Route::get('/tour/search', [TourController::class, 'search']);
    Route::get('/tour/{slug}', [TourController::class, 'fetch_one']);

    Route::post('/cart/add', [OrderController::class, 'add_to_cart']);
    Route::post('/cart/update/{id}', [OrderController::class, 'update_cart']);
    Route::get('/cart', [OrderController::class, 'cart']);
    Route::get('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders/{id}',[OrderController::class,'index']);
    Route::get('/order/{id}',[OrderController::class,'view']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::get('/wishlist/tours', [WishlistController::class, 'wishlist_tours']);
    Route::post('/wishlist/update', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{tourId}', [WishlistController::class, 'destroy']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot', [AuthController::class, 'forgot']);
    Route::post('/logout', [AuthController::class, 'logout']);

});

Route::post('/create-payment-intent', [PaymentController::class, 'createOrUpdate']);
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);
Route::post('/verify-payment', [PaymentController::class, 'verifyPayment']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/profile/update/{id}', [AuthController::class, 'update']);
});



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
