<?php

namespace App\Domain\Crm\Data;

/**
 * Immutable input for creating/updating a Customer. Built from validated
 * request data; never mutated.
 */
final readonly class CustomerData
{
    /**
     * @param  list<array<string, mixed>>  $contacts
     */
    public function __construct(
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $billingTerms,
        public ?float $creditLimit,
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
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            billingTerms: $data['billing_terms'] ?? null,
            creditLimit: isset($data['credit_limit']) ? (float) $data['credit_limit'] : null,
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
            'email' => $this->email,
            'phone' => $this->phone,
            'billing_terms' => $this->billingTerms,
            'credit_limit' => $this->creditLimit,
            'notes' => $this->notes,
        ];
    }
}
