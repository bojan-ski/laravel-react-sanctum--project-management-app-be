<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMember extends Pivot
{
    protected $table = 'project_members';
    protected $fillable = [
        'project_id',
        'user_id',
        'joined_at',
    ];

    // relation to a project
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // relation to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
