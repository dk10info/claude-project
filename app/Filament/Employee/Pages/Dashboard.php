<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament-panels::pages.dashboard';

    public function getHeading(): string
    {
        return 'Employee Dashboard';
    }

    public function getSubheading(): ?string
    {
        return 'Welcome to your employee portal';
    }
}
