<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProgressController extends Controller
{
    use AuthorizesRequests;

    /**
     * Update progress for a specific lesson.
     */
    public function update(Request $request, Lesson $lesson)
    {
        // 1. Check if student has access to this lesson (Free or Enrolled)
        $this->authorize('view', $lesson);

        $data = $request->validate([
            'watched_duration' => 'required|integer|min:0',
        ]);

        // 2. Logic Prep
        $watched = min($data['watched_duration'], $lesson->duration);
        $completionThreshold = (int) ceil($lesson->duration * 0.9);

        // Fetch existing progress to check if it was already completed
        $existing = LessonProgress::where('user_id', $request->user()->id)->where('lesson_id', $lesson->id)->first();

        // If it was already completed, keep it completed. Otherwise, check threshold.
        $isCompleted = ($existing && $existing->is_completed) ? true : ($watched >= $completionThreshold);

        // 3. Database Update
        DB::transaction(function () use ($request, $lesson, $watched, $isCompleted) {
            LessonProgress::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'watched_duration' => $watched,
                    'is_completed' => $isCompleted,
                    'last_watched_at' => now(),
                ]
            );

            // 4. If lesson status just changed to completed, update the course-wide percentage
            if ($isCompleted) {
                $this->recalculateCourseProgress(
                    $request->user()->id,
                    $lesson->module->course_id
                );
            }
        });

        return response()->json([
            'message' => $isCompleted ? 'Lesson completed' : 'Progress saved',
            'data' => [
                'is_completed' => $isCompleted,
                'watched_duration' => $watched,
                'lesson_duration' => $lesson->duration
            ]
        ]);
    }

    /**
     * Get the overall progress for a specific course.
     */
    public function show(Request $request, Course $course)
    {
        $progress = CourseProgress::where('user_id', $request->user()->id)->where('course_id', $course->id)->first();

        return response()->json([
            'course_id' => $course->id,
            'course_title' => $course->title,
            'completion_percentage' => $progress?->completion_percentage ?? 0,
            'is_finished' => ($progress?->completion_percentage ?? 0) === 100,
        ]);
    }

    /**
     * Recalculate and update the total completion percentage for a course.
     */
    protected function recalculateCourseProgress(int $userId, int $courseId): void
    {
        // Get total count of lessons in the entire course
        $totalLessons = Lesson::whereHas('module', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })->count();

        if ($totalLessons === 0) return;

        // Get count of lessons marked as completed by this user in this course
        $completedLessons = LessonProgress::where('user_id', $userId)->where('is_completed', true)
            ->whereHas('lesson.module', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->count();

        $percentage = (int) floor(($completedLessons / $totalLessons) * 100);

        CourseProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $courseId,
            ],
            [
                'completion_percentage' => $percentage,
                'updated_at' => now(),
            ]
        );
    }
}
