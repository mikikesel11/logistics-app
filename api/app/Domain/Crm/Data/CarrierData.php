<?php

namespace App\Domain\Crm\Data;

/**
 * Immutable input for creating/updating a Carrier.
 */
final readonly class CarrierData
{
    /**
     * @param  list<array<string, mixed>>|null  $contacts
     */
    public function __construct(
        public string $name,
        public ?string $mcNumber,
        public ?string $dotNumber,
        public ?string $email,
        public ?string $phone,
        public ?string $insuranceExpiresAt,
        public ?string $notes,
        public ?array $contacts,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            mcNumber: $data['mc_number'] ?? null,
            dotNumber: $data['dot_number'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            insuranceExpiresAt: $data['insurance_expires_at'] ?? null,
            notes: $data['notes'] ?? null,
            contacts: $data['contacts'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'mc_number' => $this->mcNumber,
            'dot_number' => $this->dotNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'insurance_expires_at' => $this->insuranceExpiresAt,
            'notes' => $this->notes,
        ];
    }
}
