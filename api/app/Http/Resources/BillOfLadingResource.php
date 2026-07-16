<?php

namespace App\Http\Resources;

use App\Models\BillOfLading;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BillOfLading
 */
class BillOfLadingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'load_id' => $this->load_id,
            'bol_number' => $this->bol_number,
            'customer_name' => $this->customer_name,
            'carrier_name' => $this->carrier_name,
            'ship_from' => $this->ship_from,
            'ship_to' => $this->ship_to,
            'freight' => $this->freight,
            'special_instructions' => $this->special_instructions,
            'is_ready' => $this->isReady(),
            'generated_at' => $this->generated_at,
            'created_at' => $this->created_at,
        ];
    }
}
