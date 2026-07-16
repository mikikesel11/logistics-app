<?php

namespace App\Http\Resources;

use App\Models\Load;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Load
 */
class LoadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'customer_id' => $this->customer_id,
            'carrier_id' => $this->carrier_id,
            'origin_location_id' => $this->origin_location_id,
            'destination_location_id' => $this->destination_location_id,
            'commodity' => $this->commodity,
            'weight_lbs' => $this->weight_lbs,
            'pickup_date' => $this->pickup_date?->toDateString(),
            'delivery_date' => $this->delivery_date?->toDateString(),
            'customer_rate_cents' => $this->customer_rate_cents,
            'carrier_cost_cents' => $this->carrier_cost_cents,
            'margin_cents' => $this->marginCents(),
            'notes' => $this->notes,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'carrier' => new CarrierResource($this->whenLoaded('carrier')),
            'origin' => new LocationResource($this->whenLoaded('origin')),
            'destination' => new LocationResource($this->whenLoaded('destination')),
            'freight_items' => FreightItemResource::collection($this->whenLoaded('freightItems')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
