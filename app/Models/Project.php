<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Traits\HasDocument;

class Project extends Model
{
    use HasFactory;
    use HasDocument;

    protected $table = 'projects';
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'status',
        'deadline',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'deadline' => 'date'
    ];

    //  add user id (owner_id) on new project create 
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->owner_id = Auth::id();
        });
    }

    // get project owner
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // get project members (including owner)
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'member_id')
            ->withTimestamps()
            ->withPivot('joined_at');
    }

    // check if user is owner of project
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    // check if user is member of project (including owner)
    public function isMember(User $user): bool
    {
        return $this->members()->where('member_id', $user->id)->exists();
    }

    // get project tasks
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
    
    public function isProjectActive(): bool
    {
        return $this->status === ProjectStatus::ACTIVE;
    }

    // get project statistics
    public function getStatistics(): array
    {
        $total = $this->tasks_count ?? $this->tasks()->count();
        $completed = $this->completed_tasks_count ?? $this->tasks()->where('status', TaskStatus::DONE)->count();
        $members = $this->members_count ?? $this->members()->count();

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'pending_tasks' => $total - $completed,
            'completion_percentage' => $total > 0
                ? round(($completed / $total) * 100)
                : 0,
            'total_members' => $members,
        ];
    }
}
