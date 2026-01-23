<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseDetailResource;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query()
            ->where('status', 'published')
            ->with(['category:id,name', 'instructor:id,name'])
            ->withCount('modules')
            ->withCount('enrollments');

        if ($request->filled('category')) {
            $query->whereHas(
                'category',
                fn($q) =>
                $q->where('slug', $request->category)
            );
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('is_paid')) {
            $query->where('is_paid', (bool) $request->is_paid);
        }

        if ($request->filled('search')) {
            // Escape LIKE wildcards to prevent manipulation
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where('title', 'like', "%{$search}%");
        }

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    public function show(string $slug)
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->withCount('modules')
            ->withCount('enrollments')
            ->with(['instructor', 'category', 'modules.lessons'])
            ->firstOrFail();
            
        return new CourseDetailResource($course);
    }
}
