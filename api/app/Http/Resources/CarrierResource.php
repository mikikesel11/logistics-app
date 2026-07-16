<?php

namespace App\Http\Resources;

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Carrier
 */
class CarrierResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mc_number' => $this->mc_number,
            'dot_number' => $this->dot_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'insurance_expires_at' => $this->insurance_expires_at?->toDateString(),
            'notes' => $this->notes,
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'contacts_count' => $this->whenCounted('contacts'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
