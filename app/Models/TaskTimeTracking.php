<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimeTracking extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'description',
        'entry_type',
        'is_active',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and set duration when ending a time entry
     */
    public function endTracking(): void
    {
        if ($this->is_active && $this->started_at) {
            $this->ended_at = now();
            $this->duration_minutes = $this->started_at->diffInMinutes($this->ended_at);
            $this->is_active = false;
            $this->save();
        }
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationForHumansAttribute(): string
    {
        if (! $this->duration_minutes) {
            if ($this->is_active && $this->started_at) {
                $minutes = $this->started_at->diffInMinutes(now());
            } else {
                return '0 minutes';
            }
        } else {
            $minutes = $this->duration_minutes;
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return sprintf('%d hour%s %d minute%s',
                $hours,
                $hours > 1 ? 's' : '',
                $remainingMinutes,
                $remainingMinutes > 1 ? 's' : ''
            );
        }

        return sprintf('%d minute%s', $minutes, $minutes > 1 ? 's' : '');
    }

    /**
     * Scope to get active time entries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get completed time entries
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_active', false)->whereNotNull('ended_at');
    }
}
