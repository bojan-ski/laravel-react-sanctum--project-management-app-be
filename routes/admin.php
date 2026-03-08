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
        Route::prefix('users')
            ->group(function () {
                Route::get('/', [UserController::class, 'index'])
                    ->name('admin.users.index');
                Route::post('/', [UserController::class, 'store'])
                    ->name('admin.users.store');
                Route::get('/{user}', [UserController::class, 'show'])
                    ->name('admin.users.show');
                Route::delete('/{user}', [UserController::class, 'destroy'])
                    ->name('admin.users.destroy');
            });

        Route::prefix('projects')
            ->group(function () {
                Route::get('/', [ProjectController::class, 'index'])
                    ->name('admin.projects.index');
                Route::get('/stats', [ProjectController::class, 'stats'])
                    ->name('admin.projects.stats');
            });
    });
