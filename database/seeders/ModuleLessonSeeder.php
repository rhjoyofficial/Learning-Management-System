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

            // Create 1 module (you can increase later)
            $module = Module::create([
                'course_id' => $course->id,
                'title'     => 'Getting Started',
                'position'  => 1,
            ]);

            // Create lessons under the module
            for ($l = 1; $l <= 3; $l++) {
                Lesson::create([
                    'module_id' => $module->id,
                    'title'     => "Lesson 1.{$l}",
                    'video_url' => "videos/{$course->slug}/module1_lesson{$l}.mp4",
                    'duration'  => rand(300, 900), // 5–15 minutes
                    'is_free'   => $l === 1,       // first lesson free
                    'position'  => $l,
                ]);
            }
        }
    }
}
