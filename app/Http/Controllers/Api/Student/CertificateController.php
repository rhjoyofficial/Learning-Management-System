<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    public function generate(Request $request, Course $course)
    {
        // Ensure course is published
        if ($course->status !== 'published') {
            return response()->json(['message' => 'Course not eligible.'], 403);
        }

        // Ensure enrollment exists
        if (! $request->user()->enrollments()
            ->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'Not enrolled.'], 403);
        }

        // Ensure 100% progress
        $progress = CourseProgress::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $progress || $progress->completion_percentage < 100) {
            return response()->json(['message' => 'Course not completed.'], 403);
        }

        // Idempotency: return existing certificate
        $existing = Certificate::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json(['data' => $existing]);
        }

        // Create certificate atomically
        $certificate = DB::transaction(function () use ($request, $course) {
            return Certificate::create([
                'user_id' => $request->user()->id,
                'course_id' => $course->id,
                'certificate_number' => $this->generateNumber($course->id, $request->user()->id),
                'issued_at' => now(),
            ]);
        });

        return response()->json(['data' => $certificate], 201);
    }

    public function show(Request $request, Course $course)
    {
        $certificate = Certificate::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        return response()->json(['data' => $certificate]);
    }

    protected function generateNumber(int $courseId, int $userId): string
    {
        return sprintf(
            'LMS-%d-%d-%s-%s',
            $courseId,
            $userId,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }
}
