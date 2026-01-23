<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    public function show(Request $request, Course $course)
    {
        $user = $request->user();

        // Ensure enrolled
        if (! $user->isEnrolledIn($course)) {
            return response()->json([
                'message' => 'You are not enrolled in this course',
            ], 403);
        }

        $course->load([
            'modules.lessons' => function ($q) {
                $q->orderBy('position');
            },
            'instructor:id,name',
        ]);

        return response()->json([
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'instructor' => $course->instructor->name,
            ],
            'modules' => $course->modules->map(function ($module) use ($user) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'lessons' => $module->lessons->map(function ($lesson) use ($user) {
                        $progress = $user->lessonProgress()
                            ->where('lesson_id', $lesson->id)
                            ->first();

                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'is_free' => $lesson->is_free,
                            'is_completed' => (bool) $progress?->completed_at,
                            'is_locked' => false, // <-- key change
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function watch(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        $course = $lesson->module->course;

        if (! $user->isEnrolledIn($course) && ! $lesson->is_free) {
            return response()->json([
                'message' => 'Lesson is locked',
            ], 403);
        }

        return response()->json([
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'video_url' => $lesson->video_url,
                'duration' => $lesson->duration,
            ],
        ]);
    }

    public function resume(Request $request, Course $course)
    {
        $user = $request->user();

        if (! $user->isEnrolledIn($course)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Last watched lesson
        $lastProgress = $user->lessonProgress()
            ->whereHas(
                'lesson.module',
                fn($q) =>
                $q->where('course_id', $course->id)
            )
            ->latest('updated_at')
            ->first();

        if ($lastProgress) {
            return response()->json([
                'lesson_id' => $lastProgress->lesson_id,
            ]);
        }

        // Fallback: first unlocked lesson
        $lesson = $course->modules()
            ->with(['lessons' => fn($q) => $q->where('is_free', true)])
            ->first()?->lessons->first();

        return response()->json([
            'lesson_id' => $lesson?->id,
        ]);
    }
}
