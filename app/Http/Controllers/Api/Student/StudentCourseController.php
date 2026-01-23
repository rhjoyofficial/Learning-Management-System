<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    /**
     * Get course player data with modules and lessons
     */
    public function show(Request $request, Course $course)
    {
        $user = $request->user();

        // Check if user is enrolled
        $isEnrolled = $user ? $user->isEnrolledIn($course) : false;

        // Load course with modules and lessons
        $course->load(['modules.lessons' => function ($query) {
            $query->orderBy('position');
        }]);

        // Get user's lesson progress if enrolled
        $completedLessonIds = [];
        if ($isEnrolled) {
            $completedLessonIds = $user->lessonProgress()
                ->where('is_completed', true)
                ->pluck('lesson_id')
                ->toArray();
        }

        // Format modules and lessons
        $modules = $course->modules->map(function ($module) use ($isEnrolled, $completedLessonIds) {
            return [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $module->lessons->map(function ($lesson) use ($isEnrolled, $completedLessonIds) {
                    // If enrolled, all lessons are unlocked
                    // If not enrolled, only free lessons are unlocked
                    $isLocked = !$isEnrolled && !$lesson->is_free;
                    $isCompleted = in_array($lesson->id, $completedLessonIds);

                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'is_free' => $lesson->is_free,
                        'is_completed' => $isCompleted,
                        'is_locked' => $isLocked,
                    ];
                }),
            ];
        });

        return response()->json([
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'instructor' => $course->instructor->name ?? 'Unknown',
                'is_enrolled' => $isEnrolled,
            ],
            'modules' => $modules,
        ]);
    }

    /**
     * Get resume lesson (last watched or first unlocked)
     */
    public function resume(Request $request, Course $course)
    {
        $user = $request->user();

        if (!$user) {
            // For guests, return first free lesson
            $firstFreeLesson = $course->modules()
                ->with('lessons')
                ->get()
                ->flatMap(fn($m) => $m->lessons)
                ->firstWhere('is_free', true);

            return response()->json([
                'lesson_id' => $firstFreeLesson?->id,
            ]);
        }

        $isEnrolled = $user->isEnrolledIn($course);

        // Get last watched lesson
        $lastProgress = $user->lessonProgress()
            ->whereHas('lesson.module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->latest('updated_at')
            ->first();

        if ($lastProgress && $lastProgress->lesson) {
            // Check if the lesson is still accessible
            $isAccessible = $isEnrolled || $lastProgress->lesson->is_free;

            if ($isAccessible) {
                return response()->json([
                    'lesson_id' => $lastProgress->lesson_id,
                ]);
            }
        }

        // Otherwise, return first accessible lesson
        $allLessons = $course->modules()
            ->with('lessons')
            ->get()
            ->flatMap(fn($m) => $m->lessons)
            ->sortBy('position');

        $firstAccessibleLesson = $isEnrolled
            ? $allLessons->first()
            : $allLessons->firstWhere('is_free', true);

        return response()->json([
            'lesson_id' => $firstAccessibleLesson?->id,
        ]);
    }

    /**
     * Watch a lesson (get video data)
     */
    public function watch(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        // Load the lesson's module and course
        $lesson->load('module.course');
        $course = $lesson->module->course;

        // Check access permission
        $isEnrolled = $user ? $user->isEnrolledIn($course) : false;
        $hasAccess = $lesson->is_free || $isEnrolled;

        if (!$hasAccess) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You need to enroll in this course to watch this lesson.',
            ], 403);
        }

        // Track lesson view if user is enrolled
        if ($isEnrolled && $user) {
            $user->lessonProgress()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                ['last_watched_at' => now()]
            );
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
}
