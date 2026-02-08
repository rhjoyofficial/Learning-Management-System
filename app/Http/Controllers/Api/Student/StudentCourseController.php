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
        if (!$user->isEnrolledIn($course)) {
            return response()->json([
                'message' => 'You are not enrolled in this course',
            ], 403);
        }

        $isCourseAccessible = $course->isAccessibleNow();

        // If course is not accessible, return locked status with timing info
        if (!$isCourseAccessible) {
            return response()->json([
                'locked' => true,
                'message' => 'This course is not accessible at this time.',
                'start_at' => $course->start_at,
                'end_at' => $course->end_at,
                'is_upcoming' => $course->isUpcoming(),
                'has_ended' => $course->hasEnded(),
            ], 403);
        }

        // Load course data
        $course->load([
            'modules.lessons' => function ($q) {
                $q->orderBy('position');
            },
            'instructor:id,name',
        ]);

        return response()->json([
            'locked' => false,
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'instructor' => $course->instructor->name,
                'start_at' => $course->start_at,
                'end_at' => $course->end_at,
            ],
            'modules' => $course->modules->map(function ($module) use ($user, $isCourseAccessible) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'lessons' => $module->lessons->map(function ($lesson) use ($user, $isCourseAccessible) {
                        $progress = $user->lessonProgress()
                            ->where('lesson_id', $lesson->id)
                            ->first();

                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'is_free' => $lesson->is_free,
                            'is_completed' => (bool) $progress?->completed_at,
                            'is_locked' => !$isCourseAccessible,
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

        // Check enrollment
        if (!$user->isEnrolledIn($course) && !$lesson->is_free) {
            return response()->json([
                'message' => 'Lesson is locked',
            ], 403);
        }

        // Check course accessibility
        if (!$course->isAccessibleNow()) {
            return response()->json([
                'message' => 'This course is not accessible at this time.',
                'start_at' => $course->start_at,
                'end_at' => $course->end_at,
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

        if (!$user->isEnrolledIn($course)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check course accessibility
        if (!$course->isAccessibleNow()) {
            return response()->json([
                'message' => 'This course is not accessible at this time.',
                'start_at' => $course->start_at,
                'end_at' => $course->end_at,
            ], 403);
        }

        // Last watched lesson
        $lastProgress = $user->lessonProgress()
            ->whereHas(
                'lesson.module',
                fn($q) => $q->where('course_id', $course->id)
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
            ->with(['lessons' => fn($q) => $q->orderBy('position')])
            ->first()?->lessons->first();


        return response()->json([
            'lesson_id' => $lesson?->id,
        ]);
    }
}
