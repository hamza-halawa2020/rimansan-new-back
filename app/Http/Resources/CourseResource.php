<?php

namespace App\Http\Resources;

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
            "admin" => $this->admin->name,
            "category_id" => $this->category_id,
            "category" => $this->category->name,
            "tag_id" => $this->tag_id,
            "tag" => $this->tag->name,
            "instructor_id" => $this->instructor_id,
            "instructor" => $this->instructor->name,
            "title" => $this->title,
            "description" => $this->description,
            "video_url" => $this->video_url,
            "image" => $this->image,
            "price" => $this->price,
            "certifications" => $this->certifications,
            "created_at" => $this->created_at,
        ];

    }
}
