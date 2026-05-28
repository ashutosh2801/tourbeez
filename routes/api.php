<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CommonController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\TourController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\VoucherController;
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

// Route::get('/tour-sessions', [OrderController::class, 'getSessionTimes']);
// Route::get('/tour/{slug}', [TourController::class, 'fetch_one']);
// Route::get('/location-banner', [CommonController::class, 'getLocationBanner']);

Route::get('/test', function () {
    return 'ok';
});

// Route::get('/tour/{slug}', [TourController::class, 'fetch_one']);
// Route::get('/tour/{slug}/booking', [TourController::class, 'fetch_booking']);
// Route::get('/home-listing',[CommonController::class,'home_listing']);

Route::post('/mailgun/events/{event}', [EmailController::class, 'handle']);

Route::middleware(['api.key'])->group(function () {
    Route::get('/categories',[CategoryController::class,'index'])->name('categories');
    Route::post('/sub-cateogries',[CategoryController::class,'subcategory'])->name('sub.category');
    
    Route::get('/home-listing',[CommonController::class,'home_listing']);
    Route::get('/popular-cities',[CommonController::class,'popular_cities']);
    Route::get('/popular-destinations',[CommonController::class,'popular_destinations']);
    Route::get('/destinations',[CommonController::class,'destinations']);
    Route::get('/single-city/{id}',[CommonController::class,'single_city']);
    Route::post('/contact',[CommonController::class,'contact']);
    Route::post('/careers',[CommonController::class,'careers']);
    Route::get('/recommendations', [CommonController::class, 'recommendations']);    
    Route::get('/location-banner', [CommonController::class, 'getLocationBanner']);

    Route::get('/category-tours', [TourController::class, 'toursByCategory'])->name('tour.category');
    Route::get('/tours',[TourController::class,'index']);
    Route::get('/tour/search', [TourController::class, 'search']);
    Route::get('/tour/{slug}', [TourController::class, 'fetch_one']);
    Route::get('/tour/{slug}/booking', [TourController::class, 'fetch_booking']);
    Route::get('/tour/{id}/addons', [TourController::class, 'fetch_addons']);
    Route::get('/tour/{id}/deposit-rule', [TourController::class, 'fetch_deposit_rule']);
    Route::get('/sub-tours/{id}/date/{date}', [TourController::class, 'getSubTour']);
    Route::get('/subtours/{id}/date/{date}', [TourController::class, 'fetch_sub_tours']);

    Route::post('/cart/add', [OrderController::class, 'add_to_cart']);
    Route::post('/cart/update/{id}', [OrderController::class, 'update_cart']);
    Route::post('/cart/update_error',[OrderController::class,'update_error']);
    Route::get('/cart', [OrderController::class, 'cart']);
    Route::get('/checkout', [OrderController::class, 'checkout']);
    Route::get('/order/checkout/{orderID}',[OrderController::class,'getOrderDetailByOrderID']);
    Route::get('/orders/{id}',[OrderController::class,'index']);
    Route::get('/order/{id}',[OrderController::class,'view']);
    Route::post('/tour-sessions', [OrderController::class, 'getSessionTimes']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::get('/wishlist/tours', [WishlistController::class, 'wishlist_tours']);
    Route::post('/wishlist/update', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{tourId}', [WishlistController::class, 'destroy']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot', [AuthController::class, 'forgot']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password/update/{id}', [AuthController::class, 'password_update']);

    Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);
    Route::post('/verify-payment', [PaymentController::class, 'verifyPayment']);
    Route::post('/create-payment-intent', [PaymentController::class, 'createOrUpdate']);
    Route::post('/save-card', [PaymentController::class, 'saveCard']);

    // Route::get('/supplier/register', [SupplierController::class, 'showForm'])->name('supplier.register');
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::get('/fetch_coupon/{coupon}', [PromoController::class, 'fetch_coupon']);
    Route::get('/fetch_voucher/{voucher}', [VoucherController::class, 'fetch_voucher']);

});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/profile/update/{id}', [AuthController::class, 'update']);
});

