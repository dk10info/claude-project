<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Task Management';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Task Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->minDate(now()),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'in_review' => 'In Review',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\Select::make('assignedUsers')
                            ->label('Assign to Employees')
                            ->relationship('assignedUsers', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(function ($livewire) {
                                $query = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['employee']));

                                if (! $livewire->record || ! $livewire->record->exists) {
                                    $query->where('status', 'active');
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->employee_code})")
                            ->required()
                            ->helperText('Select one or more employees to assign this task'),
                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['assignedUsers', 'createdBy']))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('assignedUsers.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        $users = $record->assignedUsers;
                        if ($users->isEmpty()) {
                            return '-';
                        }

                        return $users->map(fn ($user) => "{$user->name} ({$user->employee_code})")
                            ->join(', ');
                    })
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state < now() ? 'danger' : 'gray'),
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->icons([
                        'heroicon-o-arrow-down' => 'low',
                        'heroicon-o-minus' => 'medium',
                        'heroicon-o-arrow-up' => 'high',
                        'heroicon-o-exclamation-triangle' => 'urgent',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_progress',
                        'primary' => 'in_review',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('total_tracked_time')
                    ->label('Time Tracked')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'in_review' => 'In Review',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('assignedUsers')
                    ->label('Assigned To')
                    ->options(function () {
                        return User::role(['employee'])
                            ->where('status', 'active')
                            ->select('id', 'name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_from')
                            ->label('Due From'),
                        Forms\Components\DatePicker::make('due_until')
                            ->label('Due Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('overdue')
                    ->label('Overdue Tasks')
                    ->queries(
                        true: fn (Builder $query) => $query->where('due_date', '<', now())->whereNot('status', 'completed'),
                        false: fn (Builder $query) => $query->where('due_date', '>=', now()),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reassign')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('assignedUsers')
                            ->label('Reassign To')
                            ->options(function () {
                                return User::role(['employee'])
                                    ->where('status', 'active')
                                    ->select('id', 'name')
                                    ->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->helperText('Select one or more employees'),
                    ])
                    ->action(function (Task $record, array $data): void {
                        $oldAssignees = $record->assignedUsers->pluck('name', 'id');
                        $newAssigneeIds = $data['assignedUsers'];
                        $reassignedBy = auth()->user();

                        // Sync the new assignees
                        $record->assignedUsers()->sync($newAssigneeIds);

                        // Get new assignees
                        $newAssignees = User::whereIn('id', $newAssigneeIds)->get();

                        // Notify new assignees who weren't previously assigned
                        foreach ($newAssignees as $newAssignee) {
                            if (! $oldAssignees->has($newAssignee->id)) {
                                $newAssignee->notify(
                                    Notification::make()
                                        ->title('Task Assigned to You')
                                        ->body("You have been assigned the task: {$record->title} by {$reassignedBy->name}")
                                        ->icon('heroicon-o-arrow-path')
                                        ->info()
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('view')
                                                ->label('View Task')
                                                ->url('/employee/tasks/'.$record->id)
                                                ->markAsRead(),
                                        ])
                                        ->toDatabase()
                                );
                            }
                        }

                        // Notify removed assignees
                        $removedAssigneeIds = $oldAssignees->keys()->diff($newAssigneeIds);
                        $removedAssignees = User::whereIn('id', $removedAssigneeIds)->get();

                        foreach ($removedAssignees as $removedAssignee) {
                            $removedAssignee->notify(
                                Notification::make()
                                    ->title('Task Unassigned')
                                    ->body("You have been removed from the task '{$record->title}' by {$reassignedBy->name}")
                                    ->icon('heroicon-o-arrow-path')
                                    ->warning()
                                    ->toDatabase()
                            );
                        }

                        $newAssigneeNames = $newAssignees->pluck('name')->join(', ');
                        Notification::make()
                            ->title('Task Reassigned')
                            ->body("Task reassigned to: {$newAssigneeNames}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Task $record) => in_array($record->status, ['pending', 'in_progress'])),
                Tables\Actions\Action::make('cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Task $record): void {
                        $cancelledBy = auth()->user();
                        $record->update(['status' => 'cancelled']);

                        // Notify all assigned employees about task cancellation
                        foreach ($record->assignedUsers as $assignedUser) {
                            $assignedUser->notify(
                                Notification::make()
                                    ->title('Task Cancelled')
                                    ->body("The task '{$record->title}' has been cancelled by {$cancelledBy->name}")
                                    ->icon('heroicon-o-x-circle')
                                    ->danger()
                                    ->toDatabase()
                            );
                        }

                        Notification::make()
                            ->title('Task Cancelled')
                            ->body('The task has been cancelled successfully.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Task $record) => ! in_array($record->status, ['completed', 'cancelled'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $deletedBy = auth()->user();

                            // Notify all assigned employees about their task deletion
                            foreach ($records as $task) {
                                foreach ($task->assignedUsers as $assignedUser) {
                                    $assignedUser->notify(
                                        Notification::make()
                                            ->title('Task Deleted')
                                            ->body("The task '{$task->title}' has been deleted by {$deletedBy->name}")
                                            ->icon('heroicon-o-trash')
                                            ->danger()
                                            ->toDatabase()
                                    );
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
