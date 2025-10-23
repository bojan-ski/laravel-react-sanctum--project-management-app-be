<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'date',
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
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // get project members (including owner)
    public function members()
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
}
