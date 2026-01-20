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
        $course = Course::where('is_paid', true)->first();

        if (!$course) {
            $this->command->warn('Skipping ModuleLessonSeeder: no paid course found');
            return;
        }

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Getting Started',
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => $module->id,
            'title' => 'Introduction',
            'video_url' => 'videos/intro.mp4',
            'duration' => 300,
            'is_free' => true,
            'position' => 1,
        ]);

        Lesson::create([
            'module_id' => $module->id,
            'title' => 'Installation',
            'video_url' => 'videos/install.mp4',
            'duration' => 600,
            'is_free' => false,
            'position' => 2,
        ]);
    }
}
