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

        $admin->roles()->syncWithoutDetaching(
            Role::where('name', 'admin')->first()
        );

        $instructor = User::firstOrCreate(
            ['email' => 'instructor@lms.com'],
            [
                'name' => 'Lead Instructor',
                'password' => Hash::make('password'),
            ]
        );

        $instructor->roles()->syncWithoutDetaching(
            Role::where('name', 'instructor')->first()
        );

        $fakeStudent = User::firstOrCreate(
            ['email' => 'student@lms.com'],
            [
                'name' => 'Sample Student',
                'password' => Hash::make('password'),
            ]
        );

        $fakeStudent->roles()->syncWithoutDetaching(
            Role::where('name', 'student')->first()
        );

        $students = User::factory()->count(5)->create();

        $studentRole = Role::where('name', 'student')->first();

        foreach ($students as $student) {
            $student->roles()->syncWithoutDetaching($studentRole);
        }
    }
}
