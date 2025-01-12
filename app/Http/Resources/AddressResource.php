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
            'country' => $this->country?->name ?? 'N/A',
            'country_id' => $this->country?->id ?? null,
            'city' => $this->city?->name ?? 'N/A',
            'city_id' => $this->city?->id ?? null,
            'user_id' => $this->user?->id ?? null,
            'user' => $this->user?->name ?? 'N/A',
            'client_id' => $this->client?->id ?? null,
            'client' => $this->client?->name ?? 'N/A',
            'created_at' => $this->created_at,
        ];
    }
}
