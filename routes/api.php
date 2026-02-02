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
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController;

require __DIR__ . '/auth.php';

// Regular user routes
Route::middleware(['auth:sanctum', IsRegularUserMiddleware::class])->group(function () {
    // project routes
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);

        Route::middleware(IsProjectOwnerMiddleware::class)->group(function () {
            Route::get('/{project}/edit', [ProjectController::class, 'edit']);
            Route::put('/{project}/update', [ProjectController::class, 'update']);
            Route::delete('/{project}/delete_file', [DocumentController::class, 'deleteFile']);
            Route::put('/{project}/{status}', [ProjectController::class, 'status']);
            Route::delete('/{project}/destroy', [ProjectController::class, 'destroy']);
        });

        Route::get('/{project}', [ProjectController::class, 'show'])->middleware(IsProjectMemberMiddleware::class);
    });

    // project members routes
    Route::middleware(IsProjectOwnerMiddleware::class)
        ->prefix('projects/{project}/members')
        ->group(function () {
            Route::get('/available', [ProjectMemberController::class, 'availableUsers']);
            Route::post('/invite', [ProjectMemberController::class, 'invite']);
            Route::delete('/{member}', [ProjectMemberController::class, 'remove']);
        });

    // task routes
    Route::middleware(IsProjectOwnerMiddleware::class)->group(function () {
        Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    });

    // notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread_count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark_all_as_read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/{notification}/accept', [NotificationController::class, 'acceptInvitation']);
        Route::post('/{notification}/decline', [NotificationController::class, 'declineInvitation']);
    });

    // profile routes
    Route::prefix('profile')->group(function () {
        Route::post('/upload_avatar', [ProfileController::class, 'uploadAvatar']);
        Route::put('/change_password', [ProfileController::class, 'changePassword']);
        Route::delete('/', [ProfileController::class, 'deleteAccount']);
    });
});

// Admin user routes
Route::middleware(['auth:sanctum', IsAdminUserMiddleware::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
