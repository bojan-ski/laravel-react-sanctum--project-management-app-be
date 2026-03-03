<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AvatarController;

Route::middleware('auth:sanctum')
    ->prefix('avatar')
    ->group(function () {
        Route::post('/', [AvatarController::class, 'updateUserAvatar'])
            ->middleware(['throttle:5,15'])
            ->name('profile.update');
    });
