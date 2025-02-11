<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostcommentActiveResource extends JsonResource
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
        "comment" => $this->comment,
        "comment" => $this->comment,
        "user" => $this->user->name,
        "user_image" => $this->user->image,
        "created_at" => $this->created_at,
    ];    }
}
