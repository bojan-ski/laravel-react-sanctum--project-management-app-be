<?php

namespace App\Enums;

enum NotificationType: string
{
    case INVITATION = 'invitation';
    case PROJECT_UPDATE = 'project_update';
    case PROJECT_DELETED = 'project_deleted';
    case LEFT_THE_PROJECT = 'left_the_project';
    case REMOVED_FROM_PROJECT = 'removed_from_project';
    case TASK_ASSIGNED = 'task_assigned';
    case TASK_STATUS_CHANGED = 'task_status_changed';
    case TASK_PRIORITY_CHANGED = 'task_priority_changed';
    case TASK_DELETED = 'task_deleted';
    case TASK_MESSAGE = 'task_message';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
