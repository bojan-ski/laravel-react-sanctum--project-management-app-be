<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\NotificationResource;
use App\Exceptions\NotificationException;
use App\Services\NotificationService;
use App\Services\InvitationService;
use App\Traits\ApiResponse;
use App\Models\Notification;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly NotificationService $notificationService,
        protected readonly InvitationService $InvitationService
    ) {}

    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $unreadOnly = $request->boolean('unread');

        $notifications = $this->notificationService->getUserNotifications(
            user: $request->user(),
            unreadOnly: $unreadOnly
        );

        return $this->success(
            message: 'Notifications retrieved',
            data: NotificationResource::collection($notifications)
        );
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return $this->success(
            message: 'Unread count retrieved',
            data: ['count' => $count]
        );
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        try {
            $this->notificationService->markAsRead($notification);

            return $this->success(
                message: 'Notification marked as read',
                data: [
                    'id' => $notification->id
                ]
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $count = $this->notificationService->markAllAsRead($request->user());

            return $this->success(
                message: "{$count} notification(s) marked as read",
                data: ['count' => $count]
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Accept project invitation
     */
    public function acceptInvitation(
        Notification $notification,
        Request $request
    ): JsonResponse {
        $this->InvitationService->validateInvitation($notification);

        try {
            $updatedNotification = $this->InvitationService->acceptInvitation(
                notification: $notification,
                user: $request->user()
            );

            return $this->success(
                message: 'Invitation accepted',
                data: new NotificationResource($updatedNotification)
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Decline project invitation
     */
    public function declineInvitation(
        Request $request,
        Notification $notification
    ): JsonResponse {
        $this->InvitationService->validateInvitation($notification);

        try {
            $updatedNotification = $this->InvitationService->declineInvitation(
                notification: $notification,
                user: $request->user()
            );

            return $this->success(
                message: 'Invitation declined',
                data: new NotificationResource($updatedNotification)
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }
}
