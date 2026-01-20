<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@lms.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching($adminRole);
        }

        $instructor = User::firstOrCreate(
            ['email' => 'instructor@lms.com'],
            [
                'name' => 'Lead Instructor',
                'password' => Hash::make('password'),
            ]
        );

        $instructorRole = Role::where('name', 'instructor')->first();
        if ($instructorRole) {
            $instructor->roles()->syncWithoutDetaching($instructorRole);
        }

        $students = User::factory()->count(5)->create();

        $studentRole = Role::where('name', 'student')->first();

        if ($studentRole) {
            foreach ($students as $student) {
                $student->roles()->syncWithoutDetaching($studentRole);
            }
        }
    }
}
