<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdminUserMiddleware;
use App\Http\Middleware\IsRegularUserMiddleware;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Middleware\IsProjectMemberMiddleware;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Admin\UserController;

require __DIR__ . '/auth.php';
require __DIR__ . '/notifications.php';
require __DIR__ . '/projects.php';
require __DIR__ . '/documents.php';
require __DIR__ . '/members.php';
require __DIR__ . '/tasks.php';
require __DIR__ . '/profile.php';
require __DIR__ . '/avatar.php';

// Admin user routes
Route::middleware(['auth:sanctum', IsAdminUserMiddleware::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
