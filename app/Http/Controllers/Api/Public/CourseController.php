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
            ->with(['category:id,name', 'instructor:id,name']);

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
            $query->where('title', 'like', "%{$request->search}%");
        }

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    public function show(string $slug)
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->with(['instructor', 'category', 'modules.lessons'])
            ->firstOrFail();

        // The Resource handles all the unsetting and formatting!
        return new CourseDetailResource($course);
    }
}
