<?php

namespace App\Services;

use App\Exceptions\TaskActivityException;
use App\Enums\TaskActivityAction;
use App\Models\TaskActivity;

class TaskActivityService
{
    /**
     * Log task activity
     */
    public function logTaskActivity(
        int $taskId,
        int $userId,
        TaskActivityAction $action,
        array | string $changes
    ): TaskActivity {
        try {
            return TaskActivity::create([
                'task_id' => $taskId,
                'user_id' => $userId,
                'action' => $action,
                'changes' => $changes,
            ]);
        } catch (\Throwable $e) {
            throw TaskActivityException::logTaskActivityFailed($userId, $taskId, $action, $e);
        }
    }
}
