<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Controllers\TaskController;

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
});
