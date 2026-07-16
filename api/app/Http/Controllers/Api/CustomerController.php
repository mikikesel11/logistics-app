<?php

namespace App\Http\Controllers\Api;

use App\Domain\Crm\Data\CustomerData;
use App\Domain\Crm\Repositories\CustomerRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreCustomerRequest;
use App\Http\Requests\Crm\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $page = $this->customers->paginate($perPage);

        return ApiResponse::success(
            CustomerResource::collection($page->items()),
            meta: [
                'total' => $page->total(),
                'per_page' => $page->perPage(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create(CustomerData::fromArray($request->validated()));

        return ApiResponse::success(new CustomerResource($customer), 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return ApiResponse::success(new CustomerResource($customer->load('contacts')));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $updated = $this->customers->update($customer, CustomerData::fromArray($request->validated()));

        return ApiResponse::success(new CustomerResource($updated));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customers->delete($customer);

        return ApiResponse::success(['message' => 'Customer deleted.']);
    }
}
