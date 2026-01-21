<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'is_paid' => $this->is_paid,
            'price' => $this->price,
            'is_enrolled' => auth()->check()
                ? auth()->user()
                ->enrollments()
                ->where('course_id', $this->id)
                ->whereNull('revoked_at')
                ->exists()
                : false,
            'modules' => ModuleResource::collection($this->modules),
        ];
    }
}
