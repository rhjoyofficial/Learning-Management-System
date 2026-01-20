<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = $request->user()->enrollments()->with('course:id,title,slug')->latest('enrolled_at')->paginate(15);
        return response()->json($enrollments);
    }

    public function store(Request $request, Course $course)
    {
        // Course must be published
        if ($course->status !== 'published') {
            return response()->json(['message' => 'Course is not available for enrollment.'], 403);
        }

        // Prevent duplicate enrollment
        if (Enrollment::where('user_id', $request->user()->id)->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'Already enrolled in this course.'], 409);
        }

        // TEMP RULE: only free courses can be enrolled manually
        if ($course->is_paid) {
            return response()->json(['message' => 'This course requires payment.'], 403);
        }

        $enrollment = Enrollment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Enrollment successful.',
            'data' => $enrollment
        ], 201);
    }
}
