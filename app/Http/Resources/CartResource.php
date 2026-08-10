<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        $productPrice = optional($this->product)->priceAfterDiscount ?? 0;
        $totalPrice = $productPrice * $this->quantity;


        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->product ? new ProductResource($this->product) : null,
            'user_id' => $this->user_id,
            'quantity' => $this->quantity,
            'total_price' => $totalPrice,
            'created_at' => $this->created_at,
        ];
    }
}
