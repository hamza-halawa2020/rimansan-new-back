<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "email" => $this->email,
            "phone" => $this->phone,
            "image" => $this->image,
            "email_verified_at" => $this->email_verified_at,
            "type" => $this->type,
            'addresses' => AddressResource::collection($this->addresses),
        ];
    }
}
