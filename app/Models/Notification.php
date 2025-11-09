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
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // user notifications
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // get the notifiable entity
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // mark notification as read
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    // is notification read
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    // is notification unread
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    // is invitation
    public function isInvitation(): bool
    {
        return $this->type === NotificationType::INVITATION->value;
    }

    // is invitation pending
    public function isPending(): bool
    {
        return $this->isInvitation() && $this->action_taken === null;
    }

    // get unread notifications
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // get pending invitations
    public function scopePendingInvitations($query)
    {
        return $query->where('type', NotificationType::INVITATION->value)
            ->whereNull('action_taken');
    }
}
