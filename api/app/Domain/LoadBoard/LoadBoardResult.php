<?php

namespace App\Domain\LoadBoard;

/**
 * A normalized available-load row, independent of which provider produced it.
 */
final readonly class LoadBoardResult
{
    public function __construct(
        public string $source,
        public ?string $reference,
        public ?string $origin,
        public ?string $destination,
        public ?string $commodity,
        public ?int $weightLbs,
        public ?int $rateCents,
        public ?string $pickupDate,
        public ?int $internalLoadId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'reference' => $this->reference,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'commodity' => $this->commodity,
            'weight_lbs' => $this->weightLbs,
            'rate_cents' => $this->rateCents,
            'pickup_date' => $this->pickupDate,
            'internal_load_id' => $this->internalLoadId,
        ];
    }
}
