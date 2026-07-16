<?php

namespace App\Domain\Crm\Repositories;

use App\Domain\Crm\Data\CustomerData;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepository
{
    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Customer;

    public function create(CustomerData $data): Customer;

    public function update(Customer $customer, CustomerData $data): Customer;

    public function delete(Customer $customer): void;
}
