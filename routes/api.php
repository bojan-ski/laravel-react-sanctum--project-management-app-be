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
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProjectMemberController;

// auth routes
Route::post('/login', [AuthController::class, 'login']);

// regular user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(IsRegularUserMiddleware::class)->group(function () {
        // project routes
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);

        Route::middleware(IsProjectOwnerMiddleware::class)->group(function () {
            Route::get('/projects/{project}/edit', [ProjectController::class, 'edit']);
            Route::put('/projects/{project}/update', [ProjectController::class, 'update']);
            Route::delete('/projects/{project}/delete_file', [DocumentController::class, 'deleteFile']);
            Route::delete('/projects/{project}/destroy', [ProjectController::class, 'destroy']);
        });

        Route::get('/projects/{project}', [ProjectController::class, 'show'])->middleware(IsProjectMemberMiddleware::class);

        // project members routes
        Route::middleware(IsProjectOwnerMiddleware::class)->prefix('projects/{project}/members')->group(function () {
            Route::get('/', [ProjectMemberController::class, 'index']);
            Route::get('/available', [ProjectMemberController::class, 'availableUsers']);
            Route::post('/invite', [ProjectMemberController::class, 'invite']);
        });

        // profile routes
        Route::put('/profile/change_password', [ProfileController::class, 'changePassword']);
        Route::delete('/profile', [ProfileController::class, 'deleteAccount']);
    });
});

// admin user routes
Route::middleware(['auth:sanctum', IsAdminUserMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
