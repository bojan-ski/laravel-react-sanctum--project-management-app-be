<?php

namespace App\Observers;

use App\Models\Notification;
use App\Events\NotificationSent;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     */
    public function created(Notification $notification): void
    {
        broadcast(new NotificationSent(
            notification: $notification,
            receiverId: $notification->user_id
        ))->toOthers();
    }
}
