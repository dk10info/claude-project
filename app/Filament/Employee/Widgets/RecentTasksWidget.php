<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTasksWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'My Recent Tasks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereHas('assignedUsers', function ($query) {
                        $query->where('users.id', auth()->id());
                    })
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->orderBy('due_date', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
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
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.employee.resources.tasks.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
