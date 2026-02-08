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
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'is_paid' => $this->is_paid,
            'price' => $this->price,
            'offerPrice' => $this->offer_price,
            'duration' => $this->duration,
            'modules_count' => $this->modules_count,
            'enrollments_count' => $this->enrollments_count,
            'has_demo_video' => $this->hasDemoVideo(),
            'demo_video_url' => $this->demo_video_url ?? null,
            'promo_text' => $this->promo_text ?? null,
            'note' => $this->note ?? null,
            'is_enrolled' => auth()->check() ? auth()->user()
                ->enrollments()
                ->where('course_id', $this->id)
                ->whereNull('revoked_at')
                ->exists()
                : false,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'instructor' => [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name,
                'avatar' => $this->instructor->avatar ?? null,
                'bio' => $this->instructor->bio ?? null,
            ],
            'modules' => ModuleResource::collection($this->modules),
        ];
    }
}
