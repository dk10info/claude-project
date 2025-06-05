<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTaskDueReminders extends Command
{
    /**
     * To list the help option run below command
     * php artisan tasks:send-due-reminders --help
     *
     * To execute & send reminder notification to admin & employee run below command
     * php artisan tasks:send-due-reminders --days=0,1
     */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-due-reminders {--days=3,1 : Days before due date to send reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send task due date reminder notifications to employees and admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for tasks with upcoming due dates...');

        // Get reminder days from option (default: 3,1)
        $reminderDays = collect(explode(',', $this->option('days')))
            ->map(fn ($day) => (int) trim($day))
            ->filter(fn ($day) => $day >= 0)
            ->toArray();

        $totalSent = 0;

        foreach ($reminderDays as $days) {
            $totalSent += $this->sendRemindersForDays($days);
        }

        // Also check for overdue tasks
        $totalSent += $this->sendOverdueReminders();

        $this->info("Task due reminders sent: {$totalSent}");

        return Command::SUCCESS;
    }

    protected function sendRemindersForDays(int $days): int
    {
        $targetDate = Carbon::today()->addDays($days);

        $tasks = Task::where('due_date', $targetDate)
            ->whereIn('status', ['pending', 'in_progress', 'in_review', 'waiting_on'])
            ->with(['assignedUsers', 'createdBy'])
            ->get();

        $count = 0;

        foreach ($tasks as $task) {
            // Send Filament notification to all assigned employees
            foreach ($task->assignedUsers as $assignedUser) {
                TaskDueReminderNotification::send($task, $days, $assignedUser);
                $count++;
            }

            // Send to admin who created the task (if not already assigned)
            if ($task->createdBy && ! $task->isAssignedTo($task->createdBy->id)) {
                TaskDueReminderNotification::send($task, $days, $task->createdBy);
                $count++;
            }

            // Send to all other admins
            $assignedUserIds = $task->assignedUsers->pluck('id')->toArray();
            $admins = User::role('admin')
                ->where('id', '!=', $task->created_by)
                ->whereNotIn('id', $assignedUserIds)
                ->get();

            foreach ($admins as $admin) {
                TaskDueReminderNotification::send($task, $days, $admin);
                $count++;
            }
        }

        if ($tasks->count() > 0) {
            $this->line("  - {$tasks->count()} tasks due in {$days} day(s): {$count} notifications sent");
        }

        return $count;
    }

    protected function sendOverdueReminders(): int
    {
        $overdueDate = Carbon::today()->subDay();

        $tasks = Task::where('due_date', '<=', $overdueDate)
            ->whereIn('status', ['pending', 'in_progress', 'waiting_on'])
            ->with(['assignedUsers', 'createdBy'])
            ->get();

        $count = 0;

        foreach ($tasks as $task) {
            $daysOverdue = Carbon::today()->diffInDays($task->due_date, false);

            // Send Filament notification to all assigned employees
            foreach ($task->assignedUsers as $assignedUser) {
                TaskDueReminderNotification::send($task, $daysOverdue, $assignedUser);
                $count++;
            }

            // Send to admin who created the task (if not already assigned)
            if ($task->createdBy && ! $task->isAssignedTo($task->createdBy->id)) {
                TaskDueReminderNotification::send($task, $daysOverdue, $task->createdBy);
                $count++;
            }

            // Send to all other admins
            $assignedUserIds = $task->assignedUsers->pluck('id')->toArray();
            $admins = User::role('admin')
                ->where('id', '!=', $task->created_by)
                ->whereNotIn('id', $assignedUserIds)
                ->get();

            foreach ($admins as $admin) {
                TaskDueReminderNotification::send($task, $daysOverdue, $admin);
                $count++;
            }
        }

        if ($tasks->count() > 0) {
            $this->line("  - {$tasks->count()} overdue tasks: {$count} notifications sent");
        }

        return $count;
    }
}
