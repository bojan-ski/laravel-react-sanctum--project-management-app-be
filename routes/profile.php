<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsRegularUserMiddleware;
use App\Http\Controllers\ProfileController;

Route::middleware([
    'auth:sanctum',
    IsRegularUserMiddleware::class
])->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])
        ->name('profile.show');
    Route::post('/upload_avatar', [ProfileController::class, 'uploadAvatar'])
        ->middleware(['throttle:5,15'])
        ->name('profile.avatar');
    Route::put('/change_password', [ProfileController::class, 'changePassword'])
        ->middleware(['throttle:5,15'])
        ->name('profile.password.change');
    Route::delete('/destroy', [ProfileController::class, 'destroy'])
        ->middleware(['throttle:5,30'])
        ->name('profile.delete.account');
});
