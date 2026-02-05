<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsRegularUserMiddleware;
use App\Http\Controllers\AvatarController;

Route::middleware([
    'auth:sanctum',
    IsRegularUserMiddleware::class
])->prefix('avatar')->group(function () {
    Route::post('/', [AvatarController::class, 'updateUserAvatar'])
        ->middleware(['throttle:5,15'])
        ->name('profile.update');
});
