<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'address' => $this->address,
            'country' => $this->country->name,
            'country_id' => $this->country_id,
            'city' => $this->city->name,
            'city_id' => $this->city_id,
            'user_id' => $this->user_id,
            'user' => new OrderUserResource($this->user),
            'client_id' => $this->client_id,
            'client' => new OrderClientResource($this->client),
            'created_at' => $this->created_at,
        ];
    }
}
