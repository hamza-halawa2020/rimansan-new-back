<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentResource extends JsonResource
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
            "post_id" => $this->post_id,
            "post" => $this->post->title,
            "user_id" => $this->user_id,
            "user" => $this->user->name,
            "admin_id" => $this->admin_id,
            // "admin" => $this->admin->name,
            "comment" => $this->comment,
            "status" => $this->status,
            "created_at" => $this->created_at,
            'id' => $this->id,
        ];
    }
}