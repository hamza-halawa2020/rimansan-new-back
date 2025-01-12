<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
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
            'cost' => $this->cost,
            'country' => $this->country->name,
            'city' => $this->city->name,
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'created_at' => $this->created_at,
        ];
    }
}
