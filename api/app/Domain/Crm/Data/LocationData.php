<?php

namespace App\Domain\Crm\Data;

/**
 * Immutable input for creating/updating a Location (a reusable address used as a
 * load's origin/destination and snapshotted onto a Bill of Lading). Built from
 * validated request data; never mutated.
 */
final readonly class LocationData
{
    public function __construct(
        public ?string $name,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public string $state,
        public string $postalCode,
        public string $country,
        public ?string $contactName,
        public ?string $contactPhone,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            addressLine1: $data['address_line1'],
            addressLine2: $data['address_line2'] ?? null,
            city: $data['city'],
            state: $data['state'],
            postalCode: $data['postal_code'],
            country: $data['country'] ?? 'US',
            contactName: $data['contact_name'] ?? null,
            contactPhone: $data['contact_phone'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'address_line1' => $this->addressLine1,
            'address_line2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
        ];
    }
}
