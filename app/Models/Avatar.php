<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Avatar extends Model
{
    use HasFactory;

    protected $table = 'avatars';
    protected $fillable = [
        'user_id',
        'filename',
        'avatar_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAvatarUrlAttribute(): string
    {
        return $this->avatar_path ? Storage::url($this->avatar_path) : '';
    }

    public function getFullAvatarPathAttribute(): string
    {
        return $this->avatar_path ? Storage::disk('public')->path($this->avatar_path) : '';
    }
}
