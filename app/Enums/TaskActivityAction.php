<?php

namespace App\Enums;

enum TaskActivityAction: string
{
    case STATUS_CHANGED = 'status_changed';
    case PRIORITY_CHANGED = 'priority_changed';
    case DOCUMENT_UPLOADED = 'document_uploaded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
