<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            "category_id" => $this->category_id,
            "category" => $this->category->name,
            "tag_id" => $this->tag_id,
            "tag" => $this->tag->name,
            "title" => $this->title,
            "content" => $this->content,
            "admin_id" => $this->admin_id,
            "admin" => $this->admin->name,
            "image" => $this->image,
            "created_at" => $this->created_at,
            "eventImages" => $this->eventImages,
            'id' => $this->id,
        ];
    }
}
