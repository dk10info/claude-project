<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send notification to all assigned employees
        $assignedUsers = $this->record->assignedUsers;
        $createdBy = auth()->user();

        foreach ($assignedUsers as $assignedUser) {
            $assignedUser->notify(
                Notification::make()
                    ->title('New Task Assigned')
                    ->body("You have been assigned a new task: {$this->record->title} by {$createdBy->name}")
                    ->icon('heroicon-o-clipboard-document-check')
                    ->info()
                    ->actions([
                        Action::make('view')
                            ->label('View Task')
                            ->url('/employee/tasks/'.$this->record->id)
                            ->markAsRead(),
                    ])
                    ->toDatabase()
            );
        }
    }
}
