<?php

namespace App\Domain\Crm\Repositories;

use App\Domain\Crm\Data\CustomerData;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentCustomerRepository implements CustomerRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->withCount('contacts')
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): ?Customer
    {
        return Customer::query()->with('contacts')->find($id);
    }

    public function create(CustomerData $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::query()->create($data->toAttributes());
            $this->syncContacts($customer, $data->contacts);

            return $customer->load('contacts');
        });
    }

    public function update(Customer $customer, CustomerData $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->update($data->toAttributes());

            // Only touch contacts when the caller provided them.
            if ($data->contacts !== null) {
                $customer->contacts()->delete();
                $this->syncContacts($customer, $data->contacts);
            }

            return $customer->fresh('contacts');
        });
    }

    public function delete(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $customer->contacts()->delete();
            $customer->delete();
        });
    }

    /**
     * @param  list<array<string, mixed>>|null  $contacts
     */
    private function syncContacts(Customer $customer, ?array $contacts): void
    {
        foreach ($contacts ?? [] as $contact) {
            // organization_id auto-fills from the tenant context on create.
            $customer->contacts()->create($contact);
        }
    }
}
