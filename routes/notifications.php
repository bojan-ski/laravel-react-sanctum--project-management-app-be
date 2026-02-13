<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsRegularUserMiddleware;
use App\Http\Middleware\IsNotificationOwnerMiddleware;
use App\Http\Controllers\NotificationController;

Route::middleware([
    'auth:sanctum',
    IsRegularUserMiddleware::class
])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/unread_count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.count.unread');

    Route::middleware(IsNotificationOwnerMiddleware::class)->group(function () {
        Route::post('/read_all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.read.all');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read.single');
        Route::post('/{notification}/accept_invitation', [NotificationController::class, 'acceptInvitation'])
            ->name('notifications.invitation.accept');
        Route::post('/{notification}/decline_invitation', [NotificationController::class, 'declineInvitation'])
            ->name('notifications.invitation.decline');
    });
});
