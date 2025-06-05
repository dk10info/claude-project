<?php

namespace App\Filament\Employee\Pages;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TaskKanban extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Task Board';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.employee.pages.task-kanban';

    public ?array $data = [];

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        // Set default to current week
        $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');

        $this->form->fill([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }

    protected function getActions(): array
    {
        if (! auth()->user()->can('assign_tasks')) {
            return [];
        }

        return [
            Action::make('assignTask')
                ->label('Assign New Task')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => '/employee/task-assignments/create'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->default(Carbon::now()->startOfWeek())
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->startDate = $state),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->default(Carbon::now()->endOfWeek())
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->endDate = $state),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function getTasksByStatus(): array
    {
        $tasks = Task::whereHas('assignedUsers', function ($query) {
            $query->where('users.id', auth()->id());
        })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('due_date', [$this->startDate, $this->endDate]);
            })
            ->with(['assignedUsers', 'createdBy'])
            ->get();

        return [
            'pending' => $tasks->where('status', 'pending'),
            'in_progress' => $tasks->where('status', 'in_progress'),
            'in_review' => $tasks->where('status', 'in_review'),
            'completed' => $tasks->where('status', 'completed'),
            'cancelled' => $tasks->where('status', 'cancelled'),
        ];
    }

    public function updateTaskStatus(int $taskId, string $newStatus): void
    {
        $task = Task::findOrFail($taskId);

        // Ensure the task is assigned to the current user
        if ($task->isAssignedTo(auth()->id())) {
            $oldStatus = $task->status;
            $task->update(['status' => $newStatus]);

            // Notify admins about status change via kanban
            $user = auth()->user();
            $admins = User::role('admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(
                    Notification::make()
                        ->title('Task Status Updated (Kanban)')
                        ->body("{$user->name} updated task '{$task->title}' status from {$oldStatus} to {$newStatus} via drag and drop")
                        ->icon('heroicon-o-arrows-right-left')
                        ->info()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('View Task')
                                ->url(\App\Filament\Resources\TaskResource::getUrl('view', ['record' => $task->id], true, 'admin'))
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );
            }

            // Notify all assigned employees except who performed action
            foreach ($task->assignedUsers as $assignedUser) {
                if ($assignedUser->id === $user->id) {
                    continue;
                }
                $assignedUser->notify(
                    Notification::make()
                        ->title('Task Status Updated (Kanban)')
                        ->body("{$user->name} updated task '{$task->title}' status from {$oldStatus} to {$newStatus} via drag and drop")
                        ->icon('heroicon-o-arrows-right-left')
                        ->info()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('View Task')
                                ->url(\App\Filament\Resources\TaskResource::getUrl('view', ['record' => $task->id]))
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );
            }

            Notification::make()
                ->title('Status Updated')
                ->body("Task status changed to {$newStatus}")
                ->success()
                ->send();

            $this->dispatch('task-updated');
        }
    }

    public function getPriorityColor(string $priority): string
    {
        return match ($priority) {
            'urgent' => 'bg-red-100 text-red-800 border-red-200',
            'high' => 'bg-orange-100 text-orange-800 border-orange-200',
            'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'low' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-gray-50 border-gray-300',
            'in_progress' => 'bg-blue-50 border-blue-300',
            'in_review' => 'bg-yellow-50 border-yellow-300',
            'completed' => 'bg-green-50 border-green-300',
            'cancelled' => 'bg-red-50 border-red-300',
            default => 'bg-gray-50 border-gray-300',
        };
    }
}
