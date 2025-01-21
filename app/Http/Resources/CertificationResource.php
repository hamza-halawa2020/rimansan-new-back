<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
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
            'serial_number' => $this->serial_number,
            'file' => $this->file,
            'user_id' => $this->user_id,
            'user' => $this->user->name ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
