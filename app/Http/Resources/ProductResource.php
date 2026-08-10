<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            "title" => $this->title,
            "description" => $this->description,
            "stock" => $this->stock,
            "priceBeforeDiscount" => $this->priceBeforeDiscount,
            "discount" => $this->discount,
            "priceAfterDiscount" => $this->priceAfterDiscount,
            "points" => $this->points,
            "category" => optional($this->category)->name,
            "category_id" => optional($this->category)->id,
            "productImages" => $this->productImages,
            // "productReviews" => $this->productReviews,
            "productReviews" => ProductReviewResource::collection($this->productReviews->where('status', 'active')),

            "relatedProducts" => RelatedProductResource::collection(
                $this->category
                    ? $this->category
                        ->products()
                        ->where('id', '!=', $this->id)
                        ->inRandomOrder()
                        // ->limit(3)
                        ->get()
                    : collect()
            ),

            "admin" => optional($this->admin)->name,
            "admin_id" => optional($this->admin)->id,
            "created_at" => $this->created_at,
        ];
    }
}
