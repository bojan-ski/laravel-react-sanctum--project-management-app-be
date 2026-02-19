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
    Route::get('/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');

    Route::middleware(IsTaskOwnerMiddleware::class)->group(function () {
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])
            ->name('tasks.status.update');
        Route::patch('/tasks/{task}/priority', [TaskController::class, 'updatePriority'])
            ->name('tasks.priority.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
            ->name('tasks.destroy');
    });
});
