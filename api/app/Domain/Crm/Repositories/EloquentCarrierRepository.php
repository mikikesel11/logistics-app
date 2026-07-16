<?php

namespace App\Domain\Crm\Repositories;

use App\Domain\Crm\Data\CarrierData;
use App\Models\Carrier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentCarrierRepository implements CarrierRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Carrier::query()
            ->withCount('contacts')
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): ?Carrier
    {
        return Carrier::query()->with('contacts')->find($id);
    }

    public function create(CarrierData $data): Carrier
    {
        return DB::transaction(function () use ($data) {
            $carrier = Carrier::query()->create($data->toAttributes());
            $this->syncContacts($carrier, $data->contacts);

            return $carrier->load('contacts');
        });
    }

    public function update(Carrier $carrier, CarrierData $data): Carrier
    {
        return DB::transaction(function () use ($carrier, $data) {
            $carrier->update($data->toAttributes());

            if ($data->contacts !== null) {
                $carrier->contacts()->delete();
                $this->syncContacts($carrier, $data->contacts);
            }

            return $carrier->fresh('contacts');
        });
    }

    public function delete(Carrier $carrier): void
    {
        DB::transaction(function () use ($carrier) {
            $carrier->contacts()->delete();
            $carrier->delete();
        });
    }

    /**
     * @param  list<array<string, mixed>>|null  $contacts
     */
    private function syncContacts(Carrier $carrier, ?array $contacts): void
    {
        foreach ($contacts ?? [] as $contact) {
            $carrier->contacts()->create($contact);
        }
    }
}
