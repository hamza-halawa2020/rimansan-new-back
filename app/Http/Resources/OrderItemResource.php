<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product' =>new ProductResource($this->product),
            'quantity' => $this->quantity,
            'total' => $this->total,
            'order_id' => $this->order_id,
            'created_at' => $this->created_at,
        ];
    }
}
