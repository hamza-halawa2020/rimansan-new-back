<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddSideBarBannerResource extends JsonResource
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
            'image' => $this->image,
            'link' => $this->link,
            'status' => $this->status,
            'admin_id' => $this->admin_id,
            'admin' => $this->admin->name,
            'created_at' => $this->created_at
        ];
    }
}
