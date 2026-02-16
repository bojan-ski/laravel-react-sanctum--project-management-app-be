<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Controllers\TaskController;

Route::middleware([
    'auth:sanctum',
    IsProjectOwnerMiddleware::class
])->prefix('avatar')->group(function () {
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');
});
