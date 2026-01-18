<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Web Development' => ['PHP', 'Laravel', 'React'],
            'Mobile Development' => ['Android', 'Flutter'],
            'Programming Basics' => ['C', 'Java', 'Python'],
        ];

        foreach ($categories as $parent => $children) {
            $parentCategory = Category::create([
                'name' => $parent,
                'slug' => Str::slug($parent),
            ]);

            foreach ($children as $child) {
                Category::create([
                    'name' => $child,
                    'slug' => Str::slug($child),
                    'parent_id' => $parentCategory->id,
                ]);
            }
        }
    }
}
