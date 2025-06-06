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

        // Single query to get all task counts and status data
        $tasks = Task::whereHas('assignedUsers', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->select('status', 'due_date')
            ->get();

        $totalTasks = $tasks->whereNotIn('status', ['cancelled'])->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inReviewTasks = $tasks->where('status', 'in_review')->count();

        $overdueTasks = $tasks
            ->filter(fn ($task) => $task->due_date < now())
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
