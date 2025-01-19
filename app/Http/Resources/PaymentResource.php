<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            "payment_method" => $this->payment_method,
            "amount" => $this->amount,
            "status" => $this->status,
            "notes" => $this->notes,
            "paymob_order_id" => $this->paymob_order_id,
            "transaction_id" => $this->transaction_id,
            "order_id" => $this->order_id,
            'order' => new OrderResource($this->order),
            "created_at" => $this->created_at,
        ];
    }
}
