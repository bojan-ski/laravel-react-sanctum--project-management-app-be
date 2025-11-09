<?php

namespace App\Enums;

enum NotificationType: string
{
    case INVITATION = 'invitation';
    case TASK_ASSIGNED = 'task_assigned';
    case COMMENT_MENTION = 'comment_mention';
    case PROJECT_UPDATE = 'project_update';
}
