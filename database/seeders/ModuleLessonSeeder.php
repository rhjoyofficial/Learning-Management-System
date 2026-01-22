<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;

class ModuleLessonSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn('Skipping ModuleLessonSeeder: no courses found');
            return;
        }

        foreach ($courses as $course) {

            // ===== TRACKER (FREE, 1 DAY) =====
            if (str_contains(strtolower($course->slug), 'tracker')) {

                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => 'ট্র্যাকার পরিচিতি',
                    'position' => 1,
                ]);

                Lesson::create([
                    'module_id' => $module->id,
                    'title' => 'ট্র্যাকার ব্যবহার নির্দেশনা',
                    'video_url' => "videos/{$course->slug}/lesson1.mp4",
                    'duration' => 600, // 10 min
                    'is_free' => true,
                    'position' => 1,
                ]);

                continue;
            }

            // ===== PAID / REGULAR COURSES =====

            $modules = [
                'কোর্স পরিচিতি',
                'মৌলিক ধারণা',
                'ব্যবহারিক অনুশীলন',
                'উন্নত কৌশল',
            ];

            $modulePosition = 1;

            foreach ($modules as $moduleTitle) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => $moduleTitle,
                    'position' => $modulePosition,
                ]);

                // 5 lessons per module
                for ($l = 1; $l <= 20; $l++) {
                    Lesson::create([
                        'module_id' => $module->id,
                        'title' => "{$moduleTitle} - পাঠ {$l}",
                        'video_url' => "videos/{$course->slug}/module{$modulePosition}_lesson{$l}.mp4",
                        'duration' => rand(600, 1200), // 10–20 min
                        'is_free' => ($modulePosition === 1 && $l === 1),
                        'position' => $l,
                    ]);
                }

                $modulePosition++;
            }
        }
    }
}
