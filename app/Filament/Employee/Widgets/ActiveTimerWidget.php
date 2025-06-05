<?php

namespace App\Filament\Employee\Widgets;

use App\Filament\Resources\TaskResource;
use App\Models\TaskTimeTracking;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ActiveTimerWidget extends Widget
{
    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.employee.widgets.active-timer-widget';

    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    public $activeTimer = null;

    public function mount(): void
    {
        $this->loadActiveTimer();
    }

    public function loadActiveTimer(): void
    {
        $this->activeTimer = TaskTimeTracking::with('task')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();
    }

    public function stopTimer(): void
    {
        if ($this->activeTimer) {
            $this->activeTimer->endTracking();

            // Send notification
            Notification::make()
                ->title('Timer stopped')
                ->body("Time tracked: {$this->activeTimer->duration_for_humans}")
                ->success()
                ->send();

            // Notify admins
            $user = auth()->user();
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(
                    Notification::make()
                        ->title('Employee Stopped Timer')
                        ->body("{$user->name} stopped timer on task: {$this->activeTimer->title}. Time tracked: {$this->activeTimer->duration_for_humans}")
                        ->icon('heroicon-o-stop')
                        ->info()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('View Task')
                                ->url(TaskResource::getUrl('view', ['record' => $this->activeTimer->task_id], true, 'admin'))
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );
            }

            $this->dispatch('timerStopped');

            $this->loadActiveTimer();
        }
    }

    public function getElapsedTime(): string
    {
        if (! $this->activeTimer || ! $this->activeTimer->started_at) {
            return '00:00:00';
        }

        $elapsed = $this->activeTimer->started_at->diffInSeconds(now());
        $hours = floor($elapsed / 3600);
        $minutes = floor(($elapsed % 3600) / 60);
        $seconds = $elapsed % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
