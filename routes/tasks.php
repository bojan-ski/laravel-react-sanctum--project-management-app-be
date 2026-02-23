<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Middleware\IsTaskCreatorOrAssigneeMiddleware;
use App\Http\Middleware\IsTaskAssigneeMiddleware;
use App\Http\Middleware\IsTaskOwnerMiddleware;

Route::middleware([
    'auth:sanctum',
    IsProjectOwnerMiddleware::class
])->prefix('projects')->group(function () {
    Route::post('/{project}/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');
});

Route::get('/tasks', [TaskController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('tasks.index');

Route::middleware([
    'auth:sanctum',
    IsTaskCreatorOrAssigneeMiddleware::class
])->prefix('tasks')->group(function () {
    Route::get('/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');

    Route::post('/{task}/document', [TaskController::class, 'uploadTaskDocument'])
        ->middleware(IsTaskAssigneeMiddleware::class)
        ->name('tasks.document.upload');

    Route::middleware(IsTaskOwnerMiddleware::class)->group(function () {
        Route::put('/{task}/status', [TaskController::class, 'updateStatus'])
            ->name('tasks.status.update');
        Route::put('/{task}/priority', [TaskController::class, 'updatePriority'])
            ->name('tasks.priority.update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])
            ->name('tasks.destroy');
    });
});
