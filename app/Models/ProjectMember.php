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
        'member_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    private function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function user(): BelongsTo
    {
        return $this->member();
    }
}
