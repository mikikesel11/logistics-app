<?php

namespace App\Domain\Loads;

/**
 * Immutable input for creating/updating a Load (excludes status, which only
 * changes through the guarded transition path).
 */
final readonly class LoadData
{
    /**
     * @param  list<array<string, mixed>>|null  $freightItems
     */
    public function __construct(
        public ?string $reference,
        public ?int $customerId,
        public ?int $carrierId,
        public ?int $originLocationId,
        public ?int $destinationLocationId,
        public ?string $commodity,
        public ?int $weightLbs,
        public ?string $pickupDate,
        public ?string $deliveryDate,
        public int $customerRateCents,
        public int $carrierCostCents,
        public ?string $notes,
        public ?array $freightItems,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reference: $data['reference'] ?? null,
            customerId: $data['customer_id'] ?? null,
            carrierId: $data['carrier_id'] ?? null,
            originLocationId: $data['origin_location_id'] ?? null,
            destinationLocationId: $data['destination_location_id'] ?? null,
            commodity: $data['commodity'] ?? null,
            weightLbs: isset($data['weight_lbs']) ? (int) $data['weight_lbs'] : null,
            pickupDate: $data['pickup_date'] ?? null,
            deliveryDate: $data['delivery_date'] ?? null,
            customerRateCents: (int) ($data['customer_rate_cents'] ?? 0),
            carrierCostCents: (int) ($data['carrier_cost_cents'] ?? 0),
            notes: $data['notes'] ?? null,
            freightItems: $data['freight_items'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'reference' => $this->reference,
            'customer_id' => $this->customerId,
            'carrier_id' => $this->carrierId,
            'origin_location_id' => $this->originLocationId,
            'destination_location_id' => $this->destinationLocationId,
            'commodity' => $this->commodity,
            'weight_lbs' => $this->weightLbs,
            'pickup_date' => $this->pickupDate,
            'delivery_date' => $this->deliveryDate,
            'customer_rate_cents' => $this->customerRateCents,
            'carrier_cost_cents' => $this->carrierCostCents,
            'notes' => $this->notes,
        ];
    }
}
