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

        foreach ($courses as $course) {
            for ($m = 1; $m <= 3; $m++) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => "Module {$m}",
                    'position' => $m,
                ]);

                for ($l = 1; $l <= 3; $l++) {
                    Lesson::create([
                        'module_id' => $module->id,
                        'title' => "Lesson {$m}.{$l}",
                        'video_url' => "videos/{$course->slug}/module{$m}_lesson{$l}.mp4",
                        'duration' => rand(300, 900), // 5–15 min
                        'is_free' => $l === 1, // first lesson free
                        'position' => $l,
                    ]);
                }
            }
        }
    }
}
