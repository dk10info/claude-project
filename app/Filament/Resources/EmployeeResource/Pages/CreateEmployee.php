<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove role from data as it's not a model attribute
        unset($data['role']);
        unset($data['role_permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Get the selected role from form data
        $roleName = $this->data['role'] ?? 'employee';

        // Assign the selected role to the newly created user
        $role = Role::findByName($roleName);
        $this->record->assignRole($role);

        // Send database notification to the newly created employee
        $this->record->notify(
            Notification::make()
                ->title('Welcome to the company!')
                ->body("Your employee account has been created successfully. You can now login with your email: {$this->record->email}")
                ->icon('heroicon-o-user-plus')
                ->success()
                ->toDatabase()
        );
    }
}
