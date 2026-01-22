<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with(['course' => fn($q) => $q->withCount('modules')])
            ->get();

        $totalWatchTime = $user->lessonProgress()->sum('watched_duration') ?? 0;

        $completedModules = 0;
        $totalModules = 0;

        foreach ($enrollments as $enrollment) {
            $totalModules += $enrollment->course->modules->count();
            $completedModules += $enrollment->completed_modules_count ?? 0;
        }

        return response()->json([
            'stats' => [
                'enrolled_courses' => $enrollments->count() ?? 0,
                'watch_time_minutes' => floor($totalWatchTime / 60),
                'certificates' => $user->certificates()->count(),
                'completed_modules' => [
                    'completed' => $completedModules,
                    'total' => $totalModules,
                    'percent' => $totalModules > 0
                        ? round(($completedModules / $totalModules) * 100)
                        : 0
                ],

            ],
            'courses' => $enrollments->map(function ($enrollment) {
                $course = $enrollment->course;

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'thumbnail' => $course->thumbnail_url ?? null,
                    'image' => $course->image ?? null,
                    'completed_modules' => $enrollment->completed_modules_count,
                    'total_modules' => $course->modules->count(),
                    'progress_percent' => $enrollment->progress_percent ?? (
                        $course->modules->count() > 0
                        ? round(($enrollment->completed_modules_count / $course->modules->count()) * 100)
                        : 0
                    ),
                ];
            }),
        ]);
    }
}
