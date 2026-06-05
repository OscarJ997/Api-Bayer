<?php

namespace App\Http\Resources;

use App\Models\ConnectedPortal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ConnectedPortal */
class ConnectedPortalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'country_code' => $this->country_code,
            'name' => $this->name,
            'url' => $this->url,
            'description' => $this->description,
            'category' => $this->category?->value,
            'status' => $this->status?->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'created_date' => $this->created_at?->toIso8601String(),
        ];
    }
}
