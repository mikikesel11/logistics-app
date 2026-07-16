<?php

namespace App\Domain\Crm\Repositories;

use App\Domain\Crm\Data\CarrierData;
use App\Models\Carrier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CarrierRepository
{
    /**
     * @return LengthAwarePaginator<int, Carrier>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Carrier;

    public function create(CarrierData $data): Carrier;

    public function update(Carrier $carrier, CarrierData $data): Carrier;

    public function delete(Carrier $carrier): void;
}
