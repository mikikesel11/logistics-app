<?php

namespace App\Domain\LoadBoard;

/**
 * Provider-agnostic search criteria for a load board. External providers
 * (DAT, Truckstop, …) map these onto their own query params.
 */
final readonly class LoadBoardSearch
{
    public function __construct(
        public ?string $originState = null,
        public ?string $destinationState = null,
        public ?string $status = null,
        public int $limit = 25,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     */
    public static function fromArray(array $query): self
    {
        return new self(
            originState: $query['origin_state'] ?? null,
            destinationState: $query['destination_state'] ?? null,
            status: $query['status'] ?? null,
            limit: (int) ($query['limit'] ?? 25),
        );
    }
}
