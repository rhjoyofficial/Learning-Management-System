<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            ModuleLessonSeeder::class,
            CouponSeeder::class,
            EnrollmentSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
