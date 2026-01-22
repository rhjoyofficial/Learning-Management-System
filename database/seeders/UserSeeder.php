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
                'name' => 'প্রশিক্ষক শাইখ জিয়াউল হাসান আযহারী',
                'bio' => 'শাইখ জিয়াউল হাসান আযহারী মিশরের আল-আযহার বিশ্ববিদ্যালয় থেকে শিক্ষিত একজন জনপ্রিয় ইসলামি আলোচক. তিনি বাংলাদেশের একজন প্রখ্যাত ইসলামি চিন্তাবিদ এবং বক্তা।',
                'avatar' => '/images/user-avatar.png',
                'password' => Hash::make('password'),
            ]
        );

        $instructorRole = Role::where('name', 'instructor')->first();
        if ($instructorRole) {
            $instructor->roles()->syncWithoutDetaching($instructorRole);
        }

        $studentsData = [
            [
                'name'  => 'Rakib Hasan Joy',
                'email' => 'rhjoy@official.com',
                'phone' => '01712345678',
            ],
            [
                'name'  => 'Nusrat Jahan',
                'email' => 'nusrat@student.com',
                'phone' => '01823456789',
            ],
            [
                'name'  => 'Mahmudul Islam',
                'email' => 'mahmud@student.com',
                'phone' => '01934567890',
            ],
            [
                'name'  => 'Sadia Akter',
                'email' => 'sadia@student.com',
                'phone' => '01645678901',
            ],
            [
                'name'  => 'Tanvir Ahmed',
                'email' => 'tanvir@student.com',
                'phone' => '01556789012',
            ],
        ];

        $students = collect($studentsData)->map(function ($data) {
            return User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'],
                'password' => Hash::make('password'), // default password
            ]);
        });

        $studentRole = Role::where('name', 'student')->first();

        if ($studentRole) {
            foreach ($students as $student) {
                $student->roles()->syncWithoutDetaching($studentRole);
            }
        }
    }
}
