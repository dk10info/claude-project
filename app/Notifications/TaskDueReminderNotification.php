<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class TaskDueReminderNotification
{
    public function __construct(
        public Task $task,
        public int $daysUntilDue
    ) {}

    public static function send(Task $task, int $daysUntilDue, User $user): void
    {
        $instance = new static($task, $daysUntilDue);
        $instance->sendToUser($user);
    }

    public function sendToUser(User $user): void
    {
        $isEmployee = $user->hasRole('employee');
        $isAdmin = $user->hasRole('admin');

        // Determine message based on role and urgency
        if ($this->daysUntilDue <= 0) {
            $title = 'Task Overdue!';
            $body = "Task '{$this->task->title}' was due on {$this->task->due_date->format('M j, Y')}";
            $color = 'danger';
        } elseif ($this->daysUntilDue === 1) {
            $title = 'Task Due Tomorrow';
            $body = "Task '{$this->task->title}' is due tomorrow ({$this->task->due_date->format('M j, Y')})";
            $color = 'warning';
        } else {
            $title = 'Task Due Soon';
            $body = "Task '{$this->task->title}' is due in {$this->daysUntilDue} days ({$this->task->due_date->format('M j, Y')})";
            $color = 'info';
        }

        // Add priority badge to body
        $priorityBadge = $this->getPriorityBadge();
        $body .= " [{$priorityBadge}]";

        // Add role-specific context
        if ($isEmployee && $this->task->isAssignedTo($user->id)) {
            $body .= ' - You are assigned to this task.';
        } elseif ($isAdmin) {
            $assignedUserNames = $this->task->assignedUserNames;
            if ($assignedUserNames) {
                $body .= " - Assigned to: {$assignedUserNames}";
            }
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->color($color)
            ->icon($this->getIconForPriority())
            ->persistent()
            ->duration(null)
            ->actions([
                Action::make('view')
                    ->label('View Task')
                    ->url($this->getTaskUrl($user))
                    ->button()
                    ->color('primary'),
            ])
            ->sendToDatabase($user);
    }

    protected function getIconForPriority(): string
    {
        return match ($this->task->priority) {
            'urgent' => 'heroicon-o-exclamation-triangle',
            'high' => 'heroicon-o-exclamation-circle',
            'medium' => 'heroicon-o-clock',
            'low' => 'heroicon-o-information-circle',
            default => 'heroicon-o-bell',
        };
    }

    protected function getPriorityBadge(): string
    {
        return match ($this->task->priority) {
            'urgent' => 'URGENT',
            'high' => 'HIGH',
            'medium' => 'MEDIUM',
            'low' => 'LOW',
            default => 'NORMAL',
        };
    }

    protected function getTaskUrl(User $user): string
    {
        $isEmployee = $user->hasRole('employee');

        if ($isEmployee) {
            return "/employee/tasks/{$this->task->id}";
        }

        return "/admin/tasks/{$this->task->id}";
    }
}
