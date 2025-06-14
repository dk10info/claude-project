<?php

namespace App\Filament\Employee\Resources\TaskResource\Pages;

use App\Filament\Employee\Resources\TaskResource;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
