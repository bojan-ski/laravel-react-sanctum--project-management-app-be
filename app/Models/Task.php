<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';
    protected $fillable = [
        'project_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }

    public function isOverdue(): bool
    {
        return $this->due_date &&
            $this->due_date->isPast() &&
            $this->status !== TaskStatus::DONE;
    }

    public function isCreator(User $user): bool
    {
        if ($this->created_by === $user->id) {
            return true;
        }

        if ($this->project->isOwner($user)) {
            return true;
        }

        return false;
    }

    public function isAssignee(User $user): bool
    {
        return $this->assigned_to === $user->id;
    }
}
