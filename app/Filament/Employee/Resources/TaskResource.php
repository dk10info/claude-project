<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'My Tasks';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Task Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'in_review' => 'In Review',
                                'waiting_on' => 'Waiting On',
                            ])
                            ->required()
                            ->helperText('Update the task status'),
                    ]),
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
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'primary' => 'in_review',
                        'danger' => 'cancelled',
                        'danger' => 'waiting_on',
                    ]),
                Tables\Columns\TextColumn::make('total_tracked_time')
                    ->label('Time Tracked')
                    ->badge()
                    ->color('success'),
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
                        'completed' => 'Completed',
                        'in_review' => 'In Review',
                        'waiting_on' => 'Waiting On',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
                Tables\Filters\TernaryFilter::make('overdue')
                    ->label('Overdue Tasks')
                    ->queries(
                        true: fn (Builder $query) => $query->where('due_date', '<', now())->whereNot('status', 'completed'),
                        false: fn (Builder $query) => $query->where('due_date', '>=', now()),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Update Status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'in_review' => 'In Review',
                            ])
                            ->required()
                            ->helperText('Update the task status'),
                    ])
                    ->using(function (Task $record, array $data): Task {
                        $user = auth()->user();
                        $oldStatus = $record->status;
                        $record->update(['status' => $data['status']]);

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

                        return $record;
                    })
                    ->modalHeading('Update Task Status')
                    ->modalButton('Update Status')
                    ->successNotificationTitle('Task status updated successfully'),
            ])
            ->bulkActions([])
            ->defaultSort('due_date', 'asc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Task Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('Title'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('due_date')
                            ->label('Due Date')
                            ->date()
                            ->color(fn ($state) => $state < now() ? 'danger' : 'gray'),
                        Infolists\Components\TextEntry::make('priority')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'low' => 'gray',
                                'medium' => 'warning',
                                'high' => 'danger',
                                'urgent' => 'danger',
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
                        Infolists\Components\TextEntry::make('createdBy.name')
                            ->label('Assigned By'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['assignedUsers', 'createdBy', 'timeTrackings'])
            ->whereHas('assignedUsers', function ($query) {
                $query->where('users.id', auth()->id());
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'view' => Pages\ViewTask::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
