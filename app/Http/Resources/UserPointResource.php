<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);

        return [
            'id' => $this->id,
            'points' => $this->points,
            "user_id" => new UserResource($this->user_id),
            "product_point_id" => new ProductResource($this->product_point_id),
            "order_id" => new OrderResource($this->order_id),
            "created_at" => $this->created_at,
        ];
    }
}
