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


        $instructors = [
            [
                'email' => 'instructor1@lms.com',
                'name' => 'প্রশিক্ষক শাইখ জিয়াউল হাসান আযহারী',
                'bio' => 'শাইখ জিয়াউল হাসান আযহারী মিশরের আল-আযহার বিশ্ববিদ্যালয় থেকে শিক্ষিত একজন জনপ্রিয় ইসলামি আলোচক।',
                'avatar' => '/images/shaikh-jiaul-hasan-azhari.png',
            ],
            [
                'email' => 'cmmoin@gmail.com',
                'name' => 'চৌধুরী মোহাম্মদ মঈন',
                'bio' => 'একজন ক্লিনিক্যাল সার্টিফাইড নিউট্রিশনিস্ট, মোটিভেশনাল স্পিকার , ব্যবসায়ী , প্রতিষ্ঠাতা,  শিক্ষক, কনটেন্ট ক্রিয়েটর, গ্রোথ মেন্টর ।',
                'avatar' => '/images/cmmoin.png',
            ],
            [
                'email' => 'instructor3@lms.com',
                'name' => 'ড. মিজানুর রহমান আজহারী',
                'bio' => 'আন্তর্জাতিক খ্যাতিসম্পন্ন ইসলামি চিন্তাবিদ এবং গবেষক। তিনি আধুনিক প্রেক্ষাপটে ইসলামি আলোচনার জন্য পরিচিত।',
            ],
            [
                'email' => 'instructor4@lms.com',
                'name' => 'শাইখ আবু বকর মুহাম্মদ যাকারিয়া',
                'bio' => 'বিশিষ্ট ইসলামি স্কলার এবং গবেষক, যিনি মদীনা ইসলামী বিশ্ববিদ্যালয় থেকে উচ্চশিক্ষা সম্পন্ন করেছেন।',
                'avatar' => '/images/user-avatar.png',
            ],
        ];

        $instructorRole = Role::where('name', 'instructor')->first();

        foreach ($instructors as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'bio'      => $data['bio'],
                    'avatar'   => $data['avatar'] ?? '/images/default-avatar.png', // Fallback avatar
                    'password' => Hash::make('password'),
                ]
            );

            if ($instructorRole) {
                $user->roles()->syncWithoutDetaching($instructorRole);
            }
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
