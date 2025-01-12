<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
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
            "product_id" => $this->product_id,
            "product" => $this->product->title,
            "review" => $this->review,
            "rating" => $this->rating,
            "status" => $this->status,
            "user" => $this->user->name,
            "user_id" => $this->user->id,
            "client" => $this->client->name,
            "client_id" => $this->client->id,
            "created_at" => $this->created_at,
        ];
    }
}
