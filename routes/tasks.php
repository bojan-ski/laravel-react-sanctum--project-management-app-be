<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Middleware\IsTaskOwnerMiddleware;

Route::middleware([
    'auth:sanctum',
    IsProjectOwnerMiddleware::class
])->prefix('projects')->group(function () {
    Route::post('/{project}/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');
});

Route::middleware([
    'auth:sanctum',
])->prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index'])
        ->name('tasks.index');
    Route::get('/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');

    Route::middleware(IsTaskOwnerMiddleware::class)->group(function () {
        Route::put('/{task}/status', [TaskController::class, 'updateStatus'])
            ->name('tasks.status.update');
        Route::put('/{task}/priority', [TaskController::class, 'updatePriority'])
            ->name('tasks.priority.update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])
            ->name('tasks.destroy');
    });
});
