<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InstructorCourseController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $courses = Course::where('instructor_id', $request->user()->id)
            ->with(['category:id,name']) // Eager load relationships
            ->latest()
            ->get();

        return response()->json([
            'data' => $courses
        ]);
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);

        return response()->json([
            'data' => $course->load(['category:id,name'])
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'nullable|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced',
        ]);

        $data['instructor_id'] = $request->user()->id;
        $data['is_paid'] = ($data['price'] ?? 0) > 0;
        $data['status'] = 'draft';

        // Generate unique slug
        $baseSlug = Str::slug($data['title']);
        $slug = $baseSlug;
        $counter = 1;

        while (Course::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $data['slug'] = $slug;

        $course = Course::create($data);

        return response()->json([
            'message' => 'Course created successfully',
            'data' => $course
        ], 201);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'price' => 'nullable|numeric|min:0',
            'level' => 'sometimes|in:beginner,intermediate,advanced',
            'status' => 'sometimes|in:draft,published',
        ]);

        if (isset($data['price'])) {
            $data['is_paid'] = $data['price'] > 0;
        }

        if ($course->status === 'published') {
            unset($data['price'], $data['level']);
        }

        $course->update($data);

        return response()->json([
            'message' => 'Course updated successfully',
            'data' => $course
        ]);
    }
}
