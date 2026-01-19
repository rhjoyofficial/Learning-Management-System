<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->get(['id', 'name', 'slug', 'parent_id']);

        return response()->json([
            'data' => $categories
        ]);
    }
}
