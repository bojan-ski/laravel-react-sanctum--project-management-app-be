<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskActivity extends Model
{
    use HasFactory;

    protected $table = 'task_activities';
    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'changes',
        'document_path'
    ];

    // related to task
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    // action related to user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
