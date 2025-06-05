<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Reply;
use App\Models\TaskTimeTracking;
use App\Models\User;
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

    protected function getForms(): array
    {
        return [
            'form',
        ];
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
                    ->placeholder('Type your reply here...'),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        return [
            Actions\EditAction::make(),

            // Admin can add manual time for any user
            Actions\Action::make('adminManualEntry')
                ->label('Add Time Entry')
                ->icon('heroicon-o-clock')
                ->form([
                    Forms\Components\Select::make('user_id')
                        ->label('Employee')
                        ->options(function () {
                            return \App\Models\User::whereHas('roles', function ($query) {
                                $query->whereIn('name', ['employee']);
                            })->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable(),
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
                        ->placeholder('What was worked on?')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $startTime = \Carbon\Carbon::parse($data['started_at']);
                    $endTime = \Carbon\Carbon::parse($data['ended_at']);
                    $duration = $startTime->diffInMinutes($endTime);

                    TaskTimeTracking::create([
                        'task_id' => $this->record->id,
                        'user_id' => $data['user_id'],
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

                    $this->dispatch('time-entry-added');
                }),
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
                                'in_review' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
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

            Reply::create([
                'task_id' => $this->record->id,
                'user_id' => Auth::id(),
                'content' => $data['reply_content'],
            ]);

            Notification::make()
                ->title('Reply added successfully')
                ->success()
                ->send();

            // Notify all assigned employees
            $admin = auth()->user();
            foreach ($this->record->assignedUsers as $assignedUser) {
                $assignedUser->notify(
                    Notification::make()
                        ->title('New Reply on Task')
                        ->body("{$admin->name} added a reply on task: {$this->record->title}")
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->info()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('View Task')
                                ->url(TaskResource::getUrl('view', ['record' => $this->record->id], true, 'employee'))
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

    protected function canAddReply(): bool
    {
        $user = Auth::user();

        // Admin can always reply
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    #[On('reply-added')]
    public function refreshPage(): void
    {
        $this->refreshFormData(['replies']);
    }

    #[On('time-entry-added')]
    public function refreshTimeTracking(): void
    {
        $this->refreshFormData(['timeTrackings', 'total_tracked_time']);
    }
}
