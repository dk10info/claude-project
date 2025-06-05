<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected array $previousAssignedUserIds = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function ($record) {
                    // Send notification to all assigned employees about task deletion
                    $deletedBy = auth()->user();

                    foreach ($record->assignedUsers as $assignedUser) {
                        $assignedUser->notify(
                            Notification::make()
                                ->title('Task Deleted')
                                ->body("The task '{$record->title}' has been deleted by {$deletedBy->name}")
                                ->icon('heroicon-o-trash')
                                ->danger()
                                ->toDatabase()
                        );
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Store the current assigned users
        $this->previousAssignedUserIds = $this->record->assignedUsers->pluck('id')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $currentAssignedUserIds = $this->record->assignedUsers->pluck('id')->toArray();
        $updatedBy = auth()->user();

        // Find newly assigned users
        $newlyAssignedIds = array_diff($currentAssignedUserIds, $this->previousAssignedUserIds);
        $removedIds = array_diff($this->previousAssignedUserIds, $currentAssignedUserIds);
        $unchangedIds = array_intersect($this->previousAssignedUserIds, $currentAssignedUserIds);

        // Notify newly assigned employees
        foreach (User::whereIn('id', $newlyAssignedIds)->get() as $newUser) {
            $newUser->notify(
                Notification::make()
                    ->title('New Task Assigned')
                    ->body("You have been assigned to task: {$this->record->title} by {$updatedBy->name}")
                    ->icon('heroicon-o-clipboard-document-check')
                    ->info()
                    ->actions([
                        NotificationAction::make('view')
                            ->label('View Task')
                            ->url('/employee/tasks/'.$this->record->id)
                            ->markAsRead(),
                    ])
                    ->toDatabase()
            );
        }

        // Notify removed employees
        foreach (User::whereIn('id', $removedIds)->get() as $removedUser) {
            $removedUser->notify(
                Notification::make()
                    ->title('Task Unassigned')
                    ->body("You have been unassigned from task: {$this->record->title} by {$updatedBy->name}")
                    ->icon('heroicon-o-x-circle')
                    ->warning()
                    ->toDatabase()
            );
        }

        // Notify unchanged assigned users about updates
        if (! empty($unchangedIds)) {
            foreach (User::whereIn('id', $unchangedIds)->get() as $unchangedUser) {
                $unchangedUser->notify(
                    Notification::make()
                        ->title('Task Updated')
                        ->body("The task '{$this->record->title}' has been updated by {$updatedBy->name}")
                        ->icon('heroicon-o-pencil-square')
                        ->info()
                        ->actions([
                            NotificationAction::make('view')
                                ->label('View Task')
                                ->url('/employee/tasks/'.$this->record->id)
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );
            }
        }
    }
}
