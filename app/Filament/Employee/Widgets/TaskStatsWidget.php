<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $userId = auth()->id();

        // Get all non-cancelled tasks assigned to the employee
        $totalTasks = Task::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->whereNot('status', 'cancelled')
            ->count();

        // Get pending tasks
        $pendingTasks = Task::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->where('status', 'pending')
            ->count();

        // Get in progress tasks
        $inProgressTasks = Task::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->where('status', 'in_progress')
            ->count();

        // Get completed tasks
        $completedTasks = Task::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->where('status', 'completed')
            ->count();

        $inReviewTasks = Task::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->where('status', 'in_review')
            ->count();

        // Get overdue tasks
        $overdueTasks = Task::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        return [
            Stat::make('Total Active Tasks', $totalTasks)
                ->description('All non-cancelled tasks')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Pending Tasks', $pendingTasks)
                ->description($overdueTasks > 0 ? $overdueTasks.' overdue' : 'Awaiting start')
                ->descriptionIcon($overdueTasks > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-clock')
                ->color($overdueTasks > 0 ? 'danger' : 'warning'),

            Stat::make('In Progress', $inProgressTasks)
                ->description('Currently working on')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Completed Tasks', $completedTasks)
                ->description('Successfully finished')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('In Review Tasks', $inReviewTasks)
                ->description('Waiting for review')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('secondary'),
        ];
    }
}
