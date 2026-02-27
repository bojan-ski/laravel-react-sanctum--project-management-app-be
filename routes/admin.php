<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdminUserMiddleware;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController;

Route::middleware([
    'auth:sanctum',
    IsAdminUserMiddleware::class
])
    ->prefix('admin')
    ->group(function () {
        // users routes
        Route::get('/users', [UserController::class, 'index'])
            ->name('admin.users.index');
        Route::post('/users', [UserController::class, 'store'])
            ->name('admin.users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('admin.users.destroy');

        // projects routes
        Route::get('/projects', [ProjectController::class, 'index'])
            ->name('admin.projects.index');
    });
