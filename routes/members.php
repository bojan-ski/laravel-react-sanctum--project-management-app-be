<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsProjectOwnerMiddleware;
use App\Http\Controllers\ProjectMemberController;

Route::middleware('auth:sanctum')->prefix('projects/{project}/members')->group(function () {
    Route::delete('/leave', [ProjectMemberController::class, 'leave'])
        ->name('members.leave');

    Route::middleware(IsProjectOwnerMiddleware::class)->group(function () {
        Route::get('/available', [ProjectMemberController::class, 'availableUsers'])
            ->name('members.available');
        Route::post('/invite', [ProjectMemberController::class, 'invite'])
            ->name('members.invite');
        Route::delete('/{member}/remove', [ProjectMemberController::class, 'remove'])
            ->name('members.remove');
    });
});
