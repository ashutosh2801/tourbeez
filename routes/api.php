<?php

use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\TourController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
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

    Route::middleware(["api.key"])->group(function () {
        Route::get("/cateogries", [CategoryController::class, "index"])->name(
            "index"
        );
        Route::post("/sub-cateogries", [
            CategoryController::class,
            "subcategory",
        ])->name("sub.category");

        Route::post("/tours", [TourController::class, "index"])->name("tour.index");
        Route::get("/tour/{id}", [TourController::class, "show"])->name(
            "tour.show"
        );
    });

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

    Route::post("/signup", [AuthController::class, "signup"]);
    Route::post("/login", [AuthController::class, "login"]);
    Route::post("/forget_password", [AuthController::class, "forgot_password"]);

    Route::middleware("auth:sanctum")->group(function () {
        Route::post("/logout", [AuthController::class, "logout"]);
        Route::post("/dashboard", [DashboardController::class, "index"]);
    });
    Route::middleware(["api.key"])->group(function () {
        Route::get("/tourlist", [DashboardController::class, "tourlist"]);
        Route::get("/singletour", [DashboardController::class, "singletour"]);

    });