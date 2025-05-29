<?php

namespace App\Filament\Employee\Resources\TaskResource\Pages;

use App\Filament\Employee\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
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

                            return $options;
                        })
                        ->required()
                        ->default(fn ($record) => $record->status)
                        ->helperText('Update the task status'),
                ])
                ->action(function (array $data, $record) {
                    $record->update(['status' => $data['status']]);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn ($record) => $record->status !== 'completed')
                ->successNotificationTitle('Task status updated successfully'),
        ];
    }
}
