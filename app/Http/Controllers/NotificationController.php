<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\NotificationService;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Traits\ApiResponse;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(private NotificationService $notificationService) {}

    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $unreadOnly = $request->boolean('unread');

        $notifications = $this->notificationService->getUserNotifications(
            $request->user(),
            $unreadOnly
        );

        // return json result
        return $this->success(
            NotificationResource::collection($notifications),
            'Notifications retrieved'
        );
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return $this->success(
            ['count' => $count],
            'Unread count retrieved'
        );
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(
        Request $request,
        Notification $notification
    ): JsonResponse {
        if ($request->user()->id !== $notification->user_id) {
            return $this->error('Unauthorized', 403);
        }

        $this->notificationService->markAsRead($notification);

        return $this->success(
            $notification->id,
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user());

        return $this->success(
            ['count' => $count],
            "{$count} notification(s) marked as read"
        );
    }

    /**
     * Accept project invitation
     */
    public function acceptInvitation(
        Notification $notification,
        Request $request
    ): JsonResponse {
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        if (!$notification->isInvitation()) {
            return $this->error('This is not an invitation', 400);
        }

        if (!$notification->isPending()) {
            return $this->error('This invitation has already been responded to', 400);
        }

        $response = $this->notificationService->acceptInvitation($notification, $request->user());

        if (!$response) {
            return $this->error('Failed to accept invitation', 500);
        }

        return $this->success(
            new NotificationResource($notification),
            'Invitation accepted successfully'
        );
    }

    /**
     * Decline project invitation
     */
    public function declineInvitation(
        Request $request,
        Notification $notification
    ): JsonResponse {
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        if (!$notification->isInvitation()) {
            return $this->error('This is not an invitation', 400);
        }

        if (!$notification->isPending()) {
            return $this->error('This invitation has already been responded to', 400);
        }

        $success = $this->notificationService->declineInvitation($notification);

        if (!$success) {
            return $this->error('Failed to decline invitation', 500);
        }

        return $this->success(
            new NotificationResource($notification),
            'Invitation declined'
        );
    }
}
