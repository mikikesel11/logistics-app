<?php

namespace App\Http\Resources;

use App\Models\FreightItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FreightItem
 */
class FreightItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'pieces' => $this->pieces,
            'weight_lbs' => $this->weight_lbs,
            'freight_class' => $this->freight_class,
        ];
    }
}
