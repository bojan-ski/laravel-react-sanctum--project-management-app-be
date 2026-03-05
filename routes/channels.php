<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Task;

Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('task.{taskId}', function ($user, $taskId) {
    $task = Task::find($taskId);

    if (!$task || !$task->canView($user)) {
        return false;
    }

    return [
        'id' => (int) $user->id,
        'name' => (string) $user->name,
    ];
});
