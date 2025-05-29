<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->unique()->nullable()->after('id');
            $table->string('mobile_number')->nullable()->after('email');
            $table->date('date_of_joining')->nullable()->after('mobile_number');
            $table->string('position')->nullable()->after('date_of_joining');
            $table->string('department')->nullable()->after('position');
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active')->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employee_code', 'mobile_number', 'date_of_joining', 'position', 'department', 'status']);
        });
    }
};
