<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CallServiceController;
use App\Http\Controllers\TrashSoldController;

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

// All User Can Access this Route
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/verify/email', [AuthController::class, 'verifyEmail']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Just Masyarakat Can Acceess this Route
Route::middleware(['auth:sanctum', 'emailVerified', 'role:masyarakat',])->group(function () {
    Route::post('/call_service/store', [CallServiceController::class, 'store']);
    Route::put('/call_service/cancel/{id}', [CallServiceController::class, 'cancel']);
    Route::get('/trash_sold/myTrashSold', [TrashSoldController::class, 'myTrashSold']);
    Route::get('/trash_sold/detail/{id}', [TrashSoldController::class, 'show']);
});

// Just Admin Bank Sampah Can Acceess this Route
Route::middleware(['auth:sanctum', 'emailVerified', 'role:adminBankSampah',])->group(function () {
    Route::put('/call_service/confirm/{id}', [CallServiceController::class, 'confirm']);
    Route::get('/trash_sold/get', [TrashSoldController::class, 'index']);
    Route::post('/trash_sold/store', [TrashSoldController::class, 'store']);
    Route::put('/trash_sold/update/{id}', [TrashSoldController::class, 'update']);
    Route::get('/trash_sold/detail/{id}', [TrashSoldController::class, 'show']);
});

// Just Admin Minimarket Can Access this Route
Route::middleware(['auth:sanctum', 'emailVerified', 'role:adminMinimarket'])->group(function () {
    Route::post('/product/store', [ProductController::class, 'store']);
    Route::post('/product/update/{id}', [ProductController::class, 'update']);
    Route::delete('/product/delete/{id}', [ProductController::class, 'delete']);
});