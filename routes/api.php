<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdminUserMiddleware;
use App\Http\Middleware\IsRegularUserMiddleware;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(IsRegularUserMiddleware::class)->group(function () {
        Route::put('/profile/change_password', [ProfileController::class, 'changePassword']);
        Route::delete('/profile', [ProfileController::class, 'deleteAccount']);
    });
});

Route::middleware(['auth:sanctum', IsAdminUserMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
