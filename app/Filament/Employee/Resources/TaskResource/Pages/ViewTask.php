<?php

namespace App\Filament\Employee\Resources\TaskResource\Pages;

use App\Filament\Employee\Resources\TaskResource;
use App\Models\Reply;
use App\Models\TaskTimeTracking;
use App\Models\WaitingOn;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    public ?array $data = [];

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->form->fill([
            'reply_content' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('reply_content')
                    ->label('Your Reply')
                    ->rows(3)
                    ->required()
                    ->placeholder('Type your reply here...')
                    ->disabled(false)
                    ->dehydrated(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $activeTracking = $this->record->activeTimeTracking($user->id);

        return [
            // Time tracking actions
            Actions\Action::make('startTimer')
                ->label('Start Timer')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function () use ($user) {
                    // Check if user already has an active timer on another task
                    $existingActive = TaskTimeTracking::where('user_id', $user->id)
                        ->where('is_active', true)
                        ->first();

                    if ($existingActive) {
                        Notification::make()
                            ->title('Active timer found')
                            ->body('Please stop the timer on the other task first.')
                            ->warning()
                            ->send();

                        return;
                    }

                    TaskTimeTracking::create([
                        'task_id' => $this->record->id,
                        'user_id' => $user->id,
                        'started_at' => now(),
                        'is_active' => true,
                        'entry_type' => 'timer',
                    ]);

                    Notification::make()
                        ->title('Timer started')
                        ->success()
                        ->send();

                    // Notify admins
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(
                            Notification::make()
                                ->title('Employee Started Timer')
                                ->body("{$user->name} started timer on task: {$this->record->title}")
                                ->icon('heroicon-o-play')
                                ->info()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View Task')
                                        ->url(TaskResource::getUrl('view', ['record' => $this->record->id], true, 'admin'))
                                        ->markAsRead(),
                                ])
                                ->toDatabase()
                        );
                    }

                    $this->dispatch('timer-started');
                })
                ->visible(fn () => ! $activeTracking && $this->canTrackTime()),

            Actions\Action::make('stopTimer')
                ->label('Stop Timer')
                ->icon('heroicon-o-stop')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Stop Timer')
                ->modalDescription('Are you sure you want to stop the timer?')
                ->form([
                    Forms\Components\Textarea::make('description')
                        ->label('Description (optional)')
                        ->placeholder('What did you work on?')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($user, $activeTracking) {
                    if ($activeTracking) {
                        $activeTracking->description = $data['description'] ?? null;
                        $activeTracking->endTracking();

                        Notification::make()
                            ->title('Timer stopped')
                            ->body("Time tracked: {$activeTracking->duration_for_humans}")
                            ->success()
                            ->send();

                        // Notify admins
                        $admins = \App\Models\User::role('admin')->get();
                        foreach ($admins as $admin) {
                            $admin->notify(
                                Notification::make()
                                    ->title('Employee Stopped Timer')
                                    ->body("{$user->name} stopped timer on task: {$this->record->title}. Time tracked: {$activeTracking->duration_for_humans}")
                                    ->icon('heroicon-o-stop')
                                    ->info()
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('view')
                                            ->label('View Task')
                                            ->url(TaskResource::getUrl('view', ['record' => $this->record->id], true, 'admin'))
                                            ->markAsRead(),
                                    ])
                                    ->toDatabase()
                            );
                        }

                        $this->dispatch('timer-stopped');
                    }
                })
                ->visible(fn () => $activeTracking && $this->canTrackTime()),

            Actions\Action::make('manualEntry')
                ->label('Add Manual Time')
                ->icon('heroicon-o-clock')
                ->form([
                    Forms\Components\DateTimePicker::make('started_at')
                        ->label('Start Time')
                        ->required()
                        ->maxDate(now()),
                    Forms\Components\DateTimePicker::make('ended_at')
                        ->label('End Time')
                        ->required()
                        ->after('started_at')
                        ->maxDate(now()),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->placeholder('What did you work on?')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($user) {
                    $startTime = \Carbon\Carbon::parse($data['started_at']);
                    $endTime = \Carbon\Carbon::parse($data['ended_at']);
                    $duration = $startTime->diffInMinutes($endTime);

                    TaskTimeTracking::create([
                        'task_id' => $this->record->id,
                        'user_id' => $user->id,
                        'started_at' => $startTime,
                        'ended_at' => $endTime,
                        'duration_minutes' => $duration,
                        'description' => $data['description'] ?? null,
                        'entry_type' => 'manual',
                        'is_active' => false,
                    ]);

                    Notification::make()
                        ->title('Time entry added')
                        ->body('Duration: '.floor($duration / 60).' hours '.($duration % 60).' minutes')
                        ->success()
                        ->send();

                    // Notify admins
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(
                            Notification::make()
                                ->title('Employee Added Manual Time Entry')
                                ->body("{$user->name} added manual time entry on task: {$this->record->title}. Duration: ".floor($duration / 60).' hours '.($duration % 60).' minutes')
                                ->icon('heroicon-o-clock')
                                ->info()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View Task')
                                        ->url(TaskResource::getUrl('view', ['record' => $this->record->id], true, 'admin'))
                                        ->markAsRead(),
                                ])
                                ->toDatabase()
                        );
                    }

                    $this->dispatch('time-entry-added');
                })
                ->visible(fn () => $this->canTrackTime()),

            Actions\Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-pencil-square')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->options(function ($record) {
                            $options = [];
                            if ($record->status === 'pending') {
                                $options['in_progress'] = 'In Progress';
                            }
                            if (in_array($record->status, ['pending', 'in_progress'])) {
                                $options['completed'] = 'Completed';
                            }
                            if ($record->status === 'completed') {
                                $options['in_review'] = 'In Review';
                            }
                            if ($record->status === 'cancelled') {
                                $options['pending'] = 'Pending';
                            }

                            return $options;
                        })
                        ->required()
                        ->default(fn ($record) => $record->status)
                        ->helperText('Update the task status'),
                ])
                ->action(function (array $data, $record) use ($user) {
                    $oldStatus = $record->status;
                    $record->update(['status' => $data['status']]);
                    $this->refreshFormData(['status']);

                    // Notify admins
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(
                            Notification::make()
                                ->title('Task Status Updated')
                                ->body("{$user->name} updated task '{$record->title}' status from {$oldStatus} to {$data['status']}")
                                ->icon('heroicon-o-pencil-square')
                                ->info()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View Task')
                                        ->url(TaskResource::getUrl('view', ['record' => $record->id], true, 'admin'))
                                        ->markAsRead(),
                                ])
                                ->toDatabase()
                        );
                    }

                    // Notify all assigned employees except who performed action
                    foreach ($record->assignedUsers as $assignedUser) {
                        if ($assignedUser->id === $user->id) {
                            continue;
                        }
                        $assignedUser->notify(
                            Notification::make()
                                ->title('Task Status Updated')
                                ->body("{$user->name} updated task '{$record->title}' status from {$oldStatus} to {$data['status']}")
                                ->icon('heroicon-o-pencil-square')
                                ->info()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View Task')
                                        ->url(TaskResource::getUrl('view', ['record' => $record->id]))
                                        ->markAsRead(),
                                ])
                                ->toDatabase()
                        );
                    }
                })
                ->visible(fn ($record) => $record->status !== 'in_review')
                ->successNotificationTitle('Task status updated successfully'),

            // Waiting On action
            Actions\Action::make('waitingOn')
                ->label('Add Waiting On')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('waiting_for')
                        ->label('Waiting For')
                        ->searchable()
                        ->options(function () {
                            return $this->record->assignedUsers()
                                ->where('users.id', '!=', Auth::id())
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    $position = $user->position ? " ({$user->position})" : '';

                                    return [$user->id => $user->name.$position];
                                });
                        })
                        ->required()
                        ->helperText('Select the employee you are waiting for'),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->rows(3)
                        ->placeholder('Describe what you are waiting for...'),
                ])
                ->action(function (array $data) use ($user) {
                    // Create waiting on record
                    WaitingOn::create([
                        'task_id' => $this->record->id,
                        'created_by' => $user->id,
                        'waiting_for' => $data['waiting_for'],
                        'description' => $data['description'],
                        'status' => 'pending',
                    ]);

                    // Update task status to waiting_on
                    $this->record->update([
                        'status' => 'waiting_on',
                    ]);

                    // Add the waiting_for user to assigned users if not already assigned
                    if (! $this->record->isAssignedTo($data['waiting_for'])) {
                        $this->record->assignedUsers()->attach($data['waiting_for']);
                    }

                    // Send notification to the person being waited on
                    $waitingForUser = \App\Models\User::find($data['waiting_for']);

                    $waitingForUser->notify(
                        Notification::make()
                            ->title('You have a new waiting request')
                            ->body("{$user->name} is waiting for your input on task: {$this->record->title}")
                            ->icon('heroicon-o-clock')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('View Task')
                                    ->url(TaskResource::getUrl('view', ['record' => $this->record->id]))
                                    ->markAsRead(),
                            ])
                            ->toDatabase()
                    );

                    Notification::make()
                        ->title('Waiting On added')
                        ->body("Task status changed to 'Waiting On'. {$waitingForUser->name} has been notified.")
                        ->success()
                        ->send();

                    // Notify admins
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(
                            Notification::make()
                                ->title('Waiting On Request Created')
                                ->body("{$user->name} is waiting on {$waitingForUser->name} for task: {$this->record->title}")
                                ->icon('heroicon-o-clock')
                                ->warning()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View Task')
                                        ->url(TaskResource::getUrl('view', ['record' => $this->record->id], true, 'admin'))
                                        ->markAsRead(),
                                ])
                                ->toDatabase()
                        );
                    }

                    $this->dispatch('waiting-on-added');
                })
                ->visible(fn () => $this->canAddWaitingOn()),

            // Resolve Waiting On action
            Actions\Action::make('resolveWaitingOn')
                ->label('Resolve Waiting')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Resolve Waiting On')
                ->modalDescription('Mark this waiting request as resolved?')
                ->action(function () use ($user) {
                    // Find active waiting on where current user is the one being waited for
                    $activeWaitingOn = $this->record->activeWaitingOns()
                        ->where('waiting_for', $user->id)
                        ->first();

                    if ($activeWaitingOn) {
                        $activeWaitingOn->resolve($user->id);

                        // Check if there are any other active waiting ons
                        if ($this->record->activeWaitingOns()->count() === 0) {
                            // Change task status back to pending
                            $this->record->update([
                                'status' => 'pending',
                            ]);
                        }

                        // Notify the person who was waiting
                        $activeWaitingOn->createdBy->notify(
                            Notification::make()
                                ->title('Waiting request resolved')
                                ->body("{$user->name} has resolved the waiting request on task: {$this->record->title}")
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->label('View Task')
                                        ->url(TaskResource::getUrl('view', ['record' => $this->record->id]))
                                        ->markAsRead(),
                                ])
                                ->toDatabase()
                        );

                        Notification::make()
                            ->title('Waiting request resolved')
                            ->success()
                            ->send();

                        // Notify admins
                        $admins = \App\Models\User::role('admin')->get();
                        foreach ($admins as $admin) {
                            $admin->notify(
                                Notification::make()
                                    ->title('Waiting Request Resolved')
                                    ->body("{$user->name} resolved the waiting request on task: {$this->record->title}")
                                    ->icon('heroicon-o-check-circle')
                                    ->success()
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('view')
                                            ->label('View Task')
                                            ->url(TaskResource::getUrl('view', ['record' => $this->record->id], true, 'admin'))
                                            ->markAsRead(),
                                    ])
                                    ->toDatabase()
                            );
                        }
                    }

                    $this->dispatch('waiting-on-resolved');
                })
                ->visible(fn () => $this->canResolveWaitingOn()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Task Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('priority')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'low' => 'info',
                                'medium' => 'warning',
                                'high' => 'danger',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'in_progress' => 'info',
                                'completed' => 'success',
                                'in_review' => 'primary',
                                'cancelled' => 'danger',
                                'waiting_on' => 'warning',
                                default => 'secondary',
                            }),
                        Infolists\Components\TextEntry::make('due_date')
                            ->date(),
                        Infolists\Components\TextEntry::make('assignedUserNames')
                            ->label('Assigned To'),
                        Infolists\Components\TextEntry::make('createdBy.name')
                            ->label('Created By'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Time Tracking')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_tracked_time')
                            ->label('Total Time Tracked')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\RepeatableEntry::make('timeTrackings')
                            ->label('Time Entries')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Tracked By'),
                                Infolists\Components\TextEntry::make('started_at')
                                    ->label('Start Time')
                                    ->dateTime('M j, Y g:i A'),
                                Infolists\Components\TextEntry::make('ended_at')
                                    ->label('End Time')
                                    ->dateTime('M j, Y g:i A')
                                    ->default(now()),
                                Infolists\Components\TextEntry::make('duration_for_humans')
                                    ->label('Duration'),
                                Infolists\Components\TextEntry::make('entry_type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'timer' => 'info',
                                        'manual' => 'warning',
                                    }),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description')
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => $record->description !== null),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->visible(fn () => $this->record->timeTrackings()->count() > 0),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Waiting On')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('waitingOns')
                            ->label('Waiting History')
                            ->schema([
                                Infolists\Components\TextEntry::make('createdBy.name')
                                    ->label('Created By'),
                                Infolists\Components\TextEntry::make('waitingFor.name')
                                    ->label('Waiting For')
                                    ->badge()
                                    ->color('warning'),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description')
                                    ->columnSpan(2),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'resolved' => 'success',
                                    }),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('M j, Y g:i A'),
                                Infolists\Components\TextEntry::make('resolved_at')
                                    ->label('Resolved')
                                    ->dateTime('M j, Y g:i A')
                                    ->visible(fn ($record) => $record->status === 'resolved'),
                                Infolists\Components\TextEntry::make('resolvedBy.name')
                                    ->label('Resolved By')
                                    ->visible(fn ($record) => $record->status === 'resolved'),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->visible(fn () => $this->record->waitingOns()->count() > 0),
                    ])
                    ->collapsible()
                    ->collapsed(fn () => $this->record->activeWaitingOns()->count() === 0),

                Infolists\Components\Section::make('Replies')
                    ->schema([
                        Infolists\Components\ViewEntry::make('replies')
                            ->view('filament.infolists.components.task-replies')
                            ->columnSpanFull()
                            ->viewData([
                                'canAddReply' => $this->canAddReply(),
                            ]),
                    ]),
            ]);
    }

    public function addReply(): void
    {
        if ($this->canAddReply()) {
            $data = $this->form->getState();
            $user = Auth::user();

            Reply::create([
                'task_id' => $this->record->id,
                'user_id' => Auth::id(),
                'content' => $data['reply_content'],
            ]);

            Notification::make()
                ->title('Reply added successfully')
                ->success()
                ->send();

            // Notify admins
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(
                    Notification::make()
                        ->title('New Reply on Task')
                        ->body("{$user->name} added a reply on task: {$this->record->title}")
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->info()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('View Task')
                                ->url(TaskResource::getUrl('view', ['record' => $this->record->id], true, 'admin'))
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );
            }

            // Notify all assigned employees except who performed action
            foreach ($this->record->assignedUsers as $assignedUser) {
                if ($assignedUser->id === $user->id) {
                    continue;
                }
                $assignedUser->notify(
                    Notification::make()
                        ->title('New Reply on Task')
                        ->body("{$user->name} added a reply on task: {$this->record->title}")
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->info()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('View Task')
                                ->url(TaskResource::getUrl('view', ['record' => $this->record->id]))
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );
            }

            $this->form->fill();
            $this->dispatch('reply-added');
        } else {
            Notification::make()
                ->title('You are not authorized to add replies to this task')
                ->danger()
                ->send();
        }
    }

    public function canAddReply(): bool
    {
        $user = Auth::user();

        // Any assigned employee can reply to the task
        if ($this->record->isAssignedTo($user->id)) {
            return true;
        }

        return false;
    }

    #[On('reply-added')]
    public function refreshPage(): void
    {
        $this->refreshFormData(['replies']);
    }

    public function canTrackTime(): bool
    {
        $user = Auth::user();

        // Any assigned employee can track time on the task
        if ($this->record->isAssignedTo($user->id)) {
            return true;
        }

        return false;
    }

    #[On('timer-started')]
    #[On('timer-stopped')]
    #[On('time-entry-added')]
    public function refreshTimeTracking(): void
    {
        $this->refreshFormData(['timeTrackings', 'total_tracked_time']);
    }

    public function canAddWaitingOn(): bool
    {
        $user = Auth::user();

        // Any assigned employee can add waiting on
        if ($this->record->isAssignedTo($user->id)) {
            // Add waiting on if task is pending
            return in_array($this->record->status, ['pending']);
        }

        return false;
    }

    public function canResolveWaitingOn(): bool
    {
        $user = Auth::user();

        // Check if current user has any active waiting on requests for this task
        return $this->record->activeWaitingOns()
            ->where('waiting_for', $user->id)
            ->exists();
    }

    #[On('waiting-on-added')]
    #[On('waiting-on-resolved')]
    public function refreshWaitingOn(): void
    {
        $this->refreshFormData(['waitingOns', 'status']);
    }
}
