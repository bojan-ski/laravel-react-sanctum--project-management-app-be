<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasDocument;
use App\Enums\TaskActivityAction;

class TaskActivity extends Model
{
    use HasFactory;
    use HasDocument;

    protected $table = 'task_activities';
    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'changes',
    ];

    protected $casts = [
        'action' => TaskActivityAction::class,
        'changes' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
