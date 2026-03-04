<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsRegularUserMiddleware;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Middleware\IsProjectMemberMiddleware;
use App\Http\Controllers\ProjectController;

Route::middleware('auth:sanctum')
    ->prefix('projects')
    ->group(function () {
        Route::middleware(IsRegularUserMiddleware::class)
            ->group(function () {
                Route::get('/', [ProjectController::class, 'index'])
                    ->name('projects.index');
                Route::post('/', [ProjectController::class, 'store'])
                    ->name('projects.store');

                Route::middleware(IsProjectOwnerMiddleware::class)
                    ->group(function () {
                        Route::get('/{project}/edit', [ProjectController::class, 'edit'])
                            ->name('projects.edit');
                        Route::put('/{project}/update', [ProjectController::class, 'update'])
                            ->name('projects.update');
                        Route::put('/{project}/{status}', [ProjectController::class, 'status'])
                            ->name('projects.status.update');
                        Route::delete('/{project}', [ProjectController::class, 'destroy'])
                            ->name('projects.destroy');
                    });
            });

        Route::get('/{project}', [ProjectController::class, 'show'])
            ->middleware(IsProjectMemberMiddleware::class)
            ->name('projects.show');
    });
