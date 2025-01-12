<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            "course_id" => $this->course_id,
            "course" => $this->course->title,
            "user_id" => $this->user_id,
            "user" => $this->user->name,
            "review" => $this->review,
            "rating" => $this->rating,
            "status" => $this->status,
            "created_at" => $this->created_at,
        ];
    }
}
