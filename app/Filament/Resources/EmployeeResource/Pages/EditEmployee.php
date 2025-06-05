<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get the current role of the user
        $role = $this->record->roles->first();
        if ($role) {
            $data['role'] = $role->name;
            $data['role_permissions'] = $role->permissions->pluck('name')->implode(', ') ?: 'No permissions';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove role from data as it's not a model attribute
        unset($data['role']);
        unset($data['role_permissions']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Get the selected role from form data
        $roleName = $this->data['role'] ?? 'employee';

        // Sync the role (remove old roles and assign new one)
        $this->record->syncRoles([$roleName]);

        // Send notification to employee about account update
        $this->record->notify(
            Notification::make()
                ->title('Account Updated')
                ->body('Your employee account information has been updated by an administrator.')
                ->icon('heroicon-o-pencil-square')
                ->info()
                ->toDatabase()
        );
    }
}
