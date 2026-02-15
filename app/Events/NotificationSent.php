<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;

class NotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Notification $notification,
        public readonly int $receiverId
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("notifications.{$this->receiverId}"),
        ];
    }

    /**
     * The data to broadcast with the event.
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => (new NotificationResource($this->notification))->resolve(),
        ];
    }
}
