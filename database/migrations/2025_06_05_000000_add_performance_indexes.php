<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'tasks_status_due_date_index');
            $table->index(['status', 'created_at'], 'tasks_status_created_at_index');
            $table->index(['created_by'], 'tasks_created_by_index');
        });

        Schema::table('task_user', function (Blueprint $table) {
            $table->index(['user_id', 'task_id'], 'task_user_user_task_index');
        });

        Schema::table('task_time_trackings', function (Blueprint $table) {
            $table->index(['user_id', 'is_active'], 'task_time_trackings_user_active_index');
            $table->index(['task_id'], 'task_time_trackings_task_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['status'], 'users_status_index');
            $table->index(['employee_code'], 'users_employee_code_index');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_status_due_date_index');
            $table->dropIndex('tasks_status_created_at_index');
            $table->dropIndex('tasks_created_by_index');
        });

        Schema::table('task_user', function (Blueprint $table) {
            $table->dropIndex('task_user_user_task_index');
        });

        Schema::table('task_time_trackings', function (Blueprint $table) {
            $table->dropIndex('task_time_trackings_user_active_index');
            $table->dropIndex('task_time_trackings_task_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_index');
            $table->dropIndex('users_employee_code_index');
        });
    }
};
