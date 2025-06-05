<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get all users assigned to this task
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function timeTrackings(): HasMany
    {
        return $this->hasMany(TaskTimeTracking::class);
    }

    public function waitingOns(): HasMany
    {
        return $this->hasMany(WaitingOn::class);
    }

    public function activeWaitingOns(): HasMany
    {
        return $this->waitingOns()->where('status', 'pending');
    }

    public function isWaitingOn(): bool
    {
        return $this->status === 'waiting_on';
    }

    /**
     * Get active time tracking for a specific user
     */
    public function activeTimeTracking($userId = null)
    {
        $query = $this->timeTrackings()->active();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    /**
     * Get total tracked time in minutes
     */
    public function getTotalTrackedMinutesAttribute(): int
    {
        return $this->timeTrackings()
            ->completed()
            ->sum('duration_minutes') ?? 0;
    }

    /**
     * Get total tracked time in human readable format
     */
    public function getTotalTrackedTimeAttribute(): string
    {
        $totalMinutes = $this->total_tracked_minutes;

        if ($totalMinutes === 0) {
            return 'No time tracked';
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0) {
            return sprintf('%d hour%s %d minute%s',
                $hours,
                $hours > 1 ? 's' : '',
                $minutes,
                $minutes > 1 ? 's' : ''
            );
        }

        return sprintf('%d minute%s', $minutes, $minutes > 1 ? 's' : '');
    }

    /**
     * Check if user has active time tracking on this task
     */
    public function hasActiveTimeTracking($userId): bool
    {
        return $this->timeTrackings()
            ->active()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get days until due date (negative if overdue)
     */
    public function getDaysUntilDueAttribute(): int
    {
        if (! $this->due_date) {
            return 0;
        }

        return now()->startOfDay()->diffInDays($this->due_date, false);
    }

    /**
     * Check if task is due soon (within specified days)
     */
    public function isDueSoon(int $days = 3): bool
    {
        return $this->days_until_due <= $days && $this->days_until_due >= 0;
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->days_until_due < 0;
    }

    /**
     * Get the priority badge color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if a specific user is assigned to this task
     */
    public function isAssignedTo($userId): bool
    {
        return $this->assignedUsers()->where('users.id', $userId)->exists();
    }

    /**
     * Get comma-separated list of assigned user names
     */
    public function getAssignedUserNamesAttribute(): string
    {
        $names = $this->assignedUsers->pluck('name')->toArray();

        return implode(', ', $names);
    }
}
