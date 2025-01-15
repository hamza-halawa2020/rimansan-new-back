<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            // 'user_id' => $this->user_id,
            'client_id' => $this->client_id,
            'coupon_discount' => $this->coupon_discount,
            'shipment_cost' => $this->shipment_cost,
            'total_price' => $this->total_price,
            'notes' => $this->notes,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'notes' => $this->notes,
            'admin_id' => $this->admin_id,
            // 'admin' => $this->admin->name,
            // 'address' => new AddressResource($this->address),
            'orderItems' => OrderItemResource::collection($this->orderItems),

            'created_at' => $this->created_at,
        ];
    }
}
