<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsTaskCreatorOrAssigneeMiddleware;
use App\Http\Controllers\MessageController;

Route::middleware([
    'auth:sanctum',
    IsTaskCreatorOrAssigneeMiddleware::class
])
    ->prefix('tasks/{task}/messages')
    ->group(function () {
        Route::get('/', [MessageController::class, 'index'])
            ->name('messages.index');
        Route::post('/', [MessageController::class, 'store'])
            ->name('messages.store');
        Route::post('/read', [MessageController::class, 'markAsRead'])
            ->name('messages.read');
        Route::delete('/{message}', [MessageController::class, 'destroy'])
            ->name('messages.destroy');
    });
