<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\NotificationType;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $fillable = [
        'user_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'action_taken',
        'read_at',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function isInvitation(): bool
    {
        return $this->type === NotificationType::INVITATION;
    }

    public function isPending(): bool
    {
        return $this->isInvitation() && $this->action_taken === null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopePendingInvitations($query)
    {
        return $query->where('type', NotificationType::INVITATION)
            ->whereNull('action_taken');
    }
}
