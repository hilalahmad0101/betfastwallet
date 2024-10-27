<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\User\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('user')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('/whatsappNumber', [AdminController::class, 'whatsappNumber']);
        Route::post('/webView', [AdminController::class, 'webView']);
        Route::post('/slider', [AdminController::class, 'slider']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/changeStatus/{id}', [AdminController::class, 'changeStatus']);
    });

    Route::prefix('user')->group(function () {

        Route::post('/update/profile', [AuthController::class, 'updateProfile']);
        Route::get('/whatsappNumber', [AuthController::class, 'whatsappNumber']);
        Route::get('/webView', [AuthController::class, 'webView']);
        Route::get('/slider', [AuthController::class, 'slider']);
        Route::post('/deposit', [AuthController::class, 'makeDeposit']);
        Route::get('/transaction/list', [AuthController::class, 'showTransaction']);
        Route::post('/withdraw', [AuthController::class, 'withdraw']);
    });
});
