# Employee Task Management System

A comprehensive Laravel-based task management system built with Filament PHP, designed to streamline task assignment, tracking, and collaboration between administrators and employees.

## Features

### 🎯 Multi-Panel Architecture
- **Admin Panel** (`/admin`): Complete system control and oversight
- **Employee Panel** (`/employee`): Employee-specific task management
- **Landing Page** (`/`): Public-facing homepage with authentication

### 👥 Role-Based Access Control
- **Admin Role**: Full system access, task assignment, and employee management
- **Employee Role**: Task viewing, status updates, and time tracking
- Granular permissions system using Spatie Laravel Permission

### 📋 Task Management
- **Task Creation & Assignment**: Admins can create and assign tasks to employees
- **Priority Levels**: Low, Medium, High, and Urgent
- **Status Workflow**: 
  - Pending → In Progress → Completed/In Review
  - Waiting On (for blocked tasks)
  - Cancelled
- **Due Date Tracking**: Visual indicators for overdue tasks
- **Task Replies**: Discussion thread on each task

### ⏱️ Time Tracking
- **Timer-based Tracking**: Start/stop timer for accurate time measurement
- **Manual Time Entry**: Add time entries retroactively
- **Time Summary**: View total time spent per task
- **Active Timer Widget**: Shows current running timer across the system

### 🔄 Waiting On Feature
- **Dependency Management**: Mark tasks as waiting on other team members
- **Automatic Notifications**: Database notifications sent to blocking persons
- **Resolution Tracking**: Complete history of waiting requests
- **Status Auto-update**: Task status changes automatically based on dependencies

### 📊 Kanban Board
- **Jira-style Interface**: Drag-and-drop task management
- **Real-time Updates**: Auto-refreshes every 10 seconds
- **Visual Priority Indicators**: Color-coded priority levels
- **Date Filtering**: View tasks by date range

### 📱 Dashboard Widgets
- **Task Statistics**: Overview of task counts by status
- **Recent Tasks**: Quick access to latest assignments
- **Active Timer**: Persistent timer display

## Tech Stack

- **Backend**: Laravel 11.x
- **Admin Panel**: Filament PHP v3
- **Database**: SQLite (configurable)
- **Authentication**: Laravel Sanctum + Spatie Permission
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
- **Real-time**: Livewire for reactive components

## Installation

1. Clone the repository
```bash
git clone [repository-url]
cd claude-project
```

2. Install dependencies
```bash
composer install
npm install
```

3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

4. Database setup
```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
```

5. Build assets
```bash
npm run build
```

6. Start the development server
```bash
php artisan serve
```

## Default Credentials

After seeding, you can login with:
- **Admin**: Check DatabaseSeeder for admin credentials
- **Employee**: Created through admin panel

## Key Permissions

### Admin Permissions
- `view_any_tasks`: View all tasks in the system
- `create_tasks`: Create new tasks
- `assign_tasks`: Assign tasks to employees
- `update_any_tasks`: Update any task
- `delete_any_tasks`: Delete tasks

### Employee Permissions
- `view_tasks`: View assigned tasks
- `update_tasks`: Update own task status
- `track_time`: Use time tracking features

## Directory Structure

```
app/
├── Filament/
│   ├── Employee/
│   │   ├── Pages/
│   │   │   └── TaskKanban.php
│   │   ├── Resources/
│   │   │   └── TaskResource.php
│   │   └── Widgets/
│   │       ├── ActiveTimerWidget.php
│   │       ├── RecentTasksWidget.php
│   │       └── TaskStatsWidget.php
│   └── Resources/
│       ├── EmployeeResource.php
│       └── TaskResource.php
├── Models/
│   ├── Employee.php
│   ├── Reply.php
│   ├── Task.php
│   ├── TaskTimeTracking.php
│   └── WaitingOn.php
└── Providers/
    └── Filament/
        ├── AdminPanelProvider.php
        └── EmployeePanelProvider.php
```

## Database Schema

### Key Tables
- **users**: Extended with employee fields (employee_code, position, department)
- **tasks**: Core task data with status, priority, assignments
- **task_time_trackings**: Time tracking entries
- **waiting_ons**: Task dependency tracking
- **replies**: Task discussion threads

## Usage Guide

### For Administrators
1. Access admin panel at `/admin`
2. Create employees with appropriate roles
3. Create and assign tasks
4. Monitor task progress and employee performance

### For Employees
1. Access employee panel at `/employee`
2. View assigned tasks in list or kanban view
3. Update task status as work progresses
4. Track time using timer or manual entries
5. Add "Waiting On" when blocked by others
6. Communicate via task replies

## Security Features

- Role-based access control at all levels
- Task visibility scoped by assignment
- Secure authentication with Laravel Sanctum
- Permission checks on all operations
- CSRF protection on all forms

## Contributing

Please follow Laravel and Filament best practices when contributing to this project.

## License

This project is proprietary software. All rights reserved.