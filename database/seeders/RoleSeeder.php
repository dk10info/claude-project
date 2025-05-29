<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // Check if user with id 1 exists
        $user = User::find(1);

        if ($user) {
            // Assign admin role to existing user
            $user->assignRole($adminRole);
        } else {
            // Create new user and assign admin role
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);

            $user->assignRole($adminRole);
        }

        // Create a test employee user
        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Employee User',
                'password' => bcrypt('password'),
                'employee_code' => 'EMP001',
                'mobile_number' => '+1234567890',
                'date_of_joining' => now()->subMonths(6),
                'position' => 'Software Developer',
                'department' => 'IT',
                'status' => 'active',
            ]
        );

        $employee->assignRole($employeeRole);
    }
}
