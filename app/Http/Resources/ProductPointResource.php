<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPointResource extends JsonResource
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
            "product" => new ProductResource($this->product),
            "created_by" => new UserResource($this->createdBy),
            "points" => $this->points,
            "disabled_at" => $this->disabled_at,
            "created_at" => $this->created_at,
        ];
    }
}
