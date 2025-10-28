<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
}
