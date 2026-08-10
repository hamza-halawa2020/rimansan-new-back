<?php

namespace App\Http\Resources;

use App\Models\CourseReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            "admin_id" => $this->admin_id,
            "admin" => optional($this->admin)->name,
            "category_id" => $this->category_id,
            "category" => optional($this->category)->name,
            "tag_id" => $this->tag_id,
            "tag" => optional($this->tag)->name,
            "instructor_id" => $this->instructor_id,
            "instructor" => optional($this->instructor)->name,
            "instructor_image" => optional($this->instructor)->image,
            "title" => $this->title,
            "description" => $this->description,
            "video_url" => $this->video_url,
            "image" => $this->image,
            "price" => $this->price,
            "certifications" => $this->certifications,
            'courseReviews' => CourseReviewResource::collection($this->courseReviews->where('status', 'active')),
            "created_at" => $this->created_at,
        ];
    }
}
