<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Employees';

    protected static ?string $modelLabel = 'Employee';

    protected static ?string $pluralModelLabel = 'Employees';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee Information')
                    ->schema([
                        Forms\Components\TextInput::make('employee_code')
                            ->label('Employee Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn ($context) => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mobile_number')
                            ->label('Mobile Number')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_joining')
                            ->label('Date of Joining')
                            ->required(),
                        Forms\Components\TextInput::make('position')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('department')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave',
                                'terminated' => 'Terminated',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Role & Permissions')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(
                                Role::whereIn('name', ['employee'])
                                    ->pluck('name', 'name')
                                    ->toArray()
                            )
                            ->default('employee')
                            ->required()
                            ->helperText('Select the role for this employee. This will determine their permissions.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Show permissions that will be granted
                                $role = Role::findByName($state);
                                $permissions = $role->permissions->pluck('name')->implode(', ');
                                $set('role_permissions', $permissions ?: 'No permissions');
                            }),
                        Forms\Components\Placeholder::make('role_permissions')
                            ->label('Permissions that will be granted')
                            ->content(fn ($get) => $get('role_permissions') ?? 'Select a role to see permissions'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mobile_number')
                    ->label('Mobile Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_joining')
                    ->label('Date of Joining')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'employee' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'on_leave',
                        'danger' => ['inactive', 'terminated'],
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On Leave',
                        'terminated' => 'Terminated',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->options(fn () => User::whereHasRole('employee')
                        ->whereNotNull('department')
                        ->distinct()
                        ->pluck('department', 'department')
                        ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['employee']);
            });
    }
}
