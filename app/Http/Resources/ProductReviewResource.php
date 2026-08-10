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
        $product = $this->product;
        $productImage = $product ? optional($product->productImages->first())->image : null;

        return [
            'id' => $this->id,
            "product_id" => $this->product_id,
            "product" => optional($product)->title,
            "product_image" => $productImage,
            "review" => $this->review,
            "rating" => $this->rating,
            "status" => $this->status,
            'user' => optional($this->user)->name,
            "admin_id" => $this->admin_id,
            'admin' => optional($this->admin)->name,
            "user_id" => $this->user_id,
            'client' => optional($this->client)->name,
            "client_id" => $this->client_id,
            "created_at" => $this->created_at,
        ];
    }
}
