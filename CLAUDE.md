# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel-based Employee Task Management System built with Filament PHP. It provides a comprehensive solution for managing employees, assigning tasks, tracking time, and handling task dependencies through multiple role-based dashboards.

## Tech Stack

- **Framework**: Laravel 11.x
- **Admin Panel**: Filament PHP v3
- **Database**: SQLite (via XAMPP)
- **Authentication**: Laravel + Spatie Permission
- **Frontend**: Blade, Tailwind CSS, Alpine.js
- **Real-time**: Livewire components
- **Environment**: XAMPP on Windows (accessed via WSL2)

## Key Features

### 1. Multi-Panel Architecture
- **Admin Panel** (`/admin`): Full system management, employee creation, task assignment
- **Employee Panel** (`/employee`): Task viewing, status updates, time tracking
- **Landing Page** (`/`): Public homepage with login/authentication

### 2. Role-Based Access Control
- **Roles**: admin, employee
- **Permissions**: Granular permissions using Spatie Laravel Permission
- **Dynamic Assignment**: Roles assigned during employee creation
- **Middleware Protection**: EnsureUserHasRole, FilamentAuthenticate

### 3. Task Management
- **Task Model**: Priority levels (low, medium, high, urgent)
- **Status Workflow**: 
  - pending → in_progress → completed/in_review
  - waiting_on (blocked tasks)
  - cancelled
- **Kanban Board**: Jira-style drag-and-drop interface
- **Task Assignment**: Admin assigns tasks to employees

### 4. Time Tracking
- **Timer-based**: Start/stop timer functionality
- **Manual Entry**: Add time entries with date/time selection
- **Active Timer Widget**: Shows running timer across all pages
- **Time Summary**: Total time tracked per task

### 5. Waiting On Feature
- **Dependency Management**: Mark tasks as waiting on other employees
- **Database Notifications**: Automatic notifications to blocking person
- **Resolution Workflow**: Blocking person can resolve waiting status
- **History Tracking**: Complete audit trail of all waiting requests

### 6. Employee Management
- **Profile Fields**: employee_code, mobile_number, position, department
- **Status Types**: active, inactive, on_leave, terminated
- **Automatic User Creation**: Creates user account with employee

## Database Schema

### Core Tables
```
users (extended)
- employee_code, mobile_number, position, department, status

tasks
- title, description, due_date, priority, status
- assigned_to (user_id), created_by (user_id)

task_time_trackings
- task_id, user_id, started_at, ended_at
- duration_minutes, entry_type (timer/manual)

waiting_ons
- task_id, created_by, waiting_for, description
- status (pending/resolved), resolved_at, resolved_by

replies
- task_id, user_id, content
```

## Commands

```bash
# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Create notifications table (if needed)
php artisan notifications:table
```

## Permissions Structure

### Admin Permissions
- `view_any_tasks`: View all tasks
- `create_tasks`: Create new tasks
- `assign_tasks`: Assign tasks to employees
- `update_any_tasks`: Update any task
- `delete_any_tasks`: Delete tasks
- `view_any_employees`: View all employees
- `create_employees`: Create employees
- `update_any_employees`: Update employees
- `delete_any_employees`: Delete employees

### Employee Permissions
- `view_tasks`: View assigned tasks only
- `update_tasks`: Update own task status
- `track_time`: Use time tracking features
- `add_replies`: Comment on tasks

## Key Files & Locations

### Admin Panel Resources
- `app/Filament/Resources/EmployeeResource.php`: Employee CRUD
- `app/Filament/Resources/TaskResource.php`: Task management for admins

### Employee Panel
- `app/Filament/Employee/Resources/TaskResource.php`: Employee task view
- `app/Filament/Employee/Resources/TaskResource/Pages/ViewTask.php`: Detailed task page
- `app/Filament/Employee/Pages/TaskKanban.php`: Kanban board logic
- `app/Filament/Employee/Widgets/ActiveTimerWidget.php`: Timer display
- `app/Filament/Employee/Widgets/TaskStatsWidget.php`: Dashboard stats
- `app/Filament/Employee/Widgets/RecentTasksWidget.php`: Recent tasks list

### Models
- `app/Models/Task.php`: Task model with relationships
- `app/Models/User.php`: Extended with employee fields
- `app/Models/WaitingOn.php`: Waiting dependency tracking
- `app/Models/TaskTimeTracking.php`: Time tracking entries
- `app/Models/Reply.php`: Task comments

### Views
- `resources/views/filament/employee/pages/task-kanban.blade.php`: Kanban UI
- `resources/views/filament/employee/widgets/active-timer-widget.blade.php`: Timer UI
- `resources/views/filament/infolists/components/task-replies.blade.php`: Reply thread
- `resources/views/livewire/landing-page.blade.php`: Homepage

## Development Guidelines

### When Adding Features
1. Check user permissions before any operation
2. Use Filament's notification system for user feedback
3. Follow existing naming conventions
4. Add appropriate indexes for foreign keys
5. Use Livewire for reactive components

### Status Flow Rules
- Tasks start as 'pending'
- Only assigned employee can change status
- 'waiting_on' status auto-assigned when adding waiting dependency
- Status returns to 'in_progress' when all waiting resolved
- Completed tasks can go to 'in_review'

### Security Patterns
- All resources use `getEloquentQuery()` to scope data
- Middleware checks on panel providers
- Permission checks in view/edit/delete methods
- CSRF protection on all forms

### UI/UX Conventions
- Kanban board uses drag-and-drop with visual feedback
- Color coding: warning=pending, info=in_progress, success=completed
- Auto-refresh on kanban board (10 seconds)
- Notification badges for waiting requests

## Common Tasks

### Adding a New Task Status
1. Update migration to add to enum
2. Update Task model status constants
3. Add to TaskResource form/table/filters
4. Update Kanban board statuses array
5. Add color mapping in UI components

### Adding New Permissions
1. Add to PermissionSeeder
2. Run `php artisan db:seed --class=PermissionSeeder`
3. Update role assignments as needed
4. Add permission checks in resources

### Debugging Tips
- Check Laravel logs: `storage/logs/laravel.log`
- Verify permissions: `$user->hasPermissionTo('permission_name')`
- Test notifications: Check `notifications` table
- Livewire issues: Check browser console

## Important Notes

- SQLite doesn't support MODIFY COLUMN - use table recreation
- Filament notifications require database notifications table
- Employee panel routes use 'employee' slug
- Time is tracked in minutes, displayed in hours/minutes
- All timestamps use application timezone