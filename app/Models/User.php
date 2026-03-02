<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    // check if user is an administrator
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    // check if user is a regular user
    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    // get user avatar
    public function avatar(): HasOne
    {
        return $this->hasOne(Avatar::class);
    }

    // user owned projects
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    // member of projects
    public function memberProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members', 'member_id', 'project_id')
            ->where(function ($query) {
                $query->whereColumn('projects.owner_id', '!=', 'project_members.member_id');
            });
    }

    // projects user is a member of and owns
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members', 'member_id', 'project_id');
    }

    // get tasks created by user
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    // get assigned tasks
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // user notifications
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
