<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CallServiceController;
use App\Http\Controllers\TrashSoldController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;

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

// Everyone Can Access this Route
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/product/get', [ProductController::class, 'index']);
Route::get('/product/detail/{id}', [ProductController::class, 'show']);

// Forget Password
Route::post('/forget_password', [AuthController::class, 'forgetPassword']);

// Look Vacancy
Route::get('/vacancy/get', [VacancyController::class, 'index']);
Route::get('/vacancy/detail/{id}', [VacancyController::class, 'show']);

// Get Profile
Route::get('/user/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');

// Article
Route::get('/article/get', [ArticleController::class, 'index']);
Route::get('/article/detail/{id}', [ArticleController::class, 'show']);

// Callback For Midtrans
Route::post('/midtrans/callback', [MidtransCallbackController::class, 'handle']);

// All User Can Access this Route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/verify/email', [AuthController::class, 'verifyEmail']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Detail Order
    Route::get('/order/detail/{id}', [OrderController::class, 'show']);
});

// Just Masyarakat Can Acceess this Route
Route::middleware(['auth:sanctum', 'emailVerified', 'role:masyarakat',])->group(function () {
    Route::post('/call_service/store', [CallServiceController::class, 'store']);
    Route::put('/call_service/cancel/{id}', [CallServiceController::class, 'cancel']);
    Route::get('/trash_sold/myTrashSold', [TrashSoldController::class, 'myTrashSold']);
    Route::get('/trash_sold/detail/{id}', [TrashSoldController::class, 'show']);
    Route::post('/application/store', [ApplicationController::class, 'store']);
    Route::post('/membership/store', [MembershipController::class, 'store']);
    Route::post('/payment/membership/{id}', [PaymentController::class, 'payMembership']);
    Route::post('/order/cart/{id}', [OrderController::class, 'cart']);
    Route::get('/order/myCart', [OrderController::class, 'myCart']);
    Route::post('/order/orderCart', [OrderController::class, 'orderCart']);
    Route::post('/order/directOrder/{id}', [OrderController::class, 'directOrder']);
    Route::get('/order/myOrder', [OrderController::class, 'myOrder']);
    Route::post('/payment/product/{id}', [PaymentController::class, 'payProduct']);
    Route::post('/review/store/{id}', [ReviewController::class, 'store']);
    Route::put('/review/update/{id}', [ReviewController::class, 'update']);
});

// Just Admin Bank Sampah Can Acceess this Route
Route::middleware(['auth:sanctum', 'emailVerified', 'role:adminBankSampah',])->group(function () {
    Route::put('/call_service/confirm/{id}', [CallServiceController::class, 'confirm']);
    Route::get('/trash_sold/get', [TrashSoldController::class, 'index']);
    Route::post('/trash_sold/store', [TrashSoldController::class, 'store']);
    // Route::put('/trash_sold/update/{id}', [TrashSoldController::class, 'update']);
    Route::get('/trash_sold/detail/{id}', [TrashSoldController::class, 'show']);
});

// Just Admin Minimarket Can Access this Route
Route::middleware(['auth:sanctum', 'emailVerified', 'role:adminMinimarket'])->group(function () {
    Route::post('/product/store', [ProductController::class, 'store']);
    Route::post('/product/update/{id}', [ProductController::class, 'update']);
    Route::delete('/product/delete/{id}', [ProductController::class, 'delete']);
    Route::post('/vacancy/store', [VacancyController::class, 'store']);
    Route::put('/vacancy/update/{id}', [VacancyController::class, 'update']);
    Route::delete('/vacancy/delete/{id}', [VacancyController::class, 'delete']);
    Route::post('/article/store', [ArticleController::class, 'store']);
    Route::delete('/article/delete/{id}', [ArticleController::class, 'delete']);
    Route::get('/order/get', [OrderController::class, 'orders']);
});