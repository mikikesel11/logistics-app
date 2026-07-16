<?php

namespace App\Http\Controllers\Api;

use App\Domain\Crm\Data\CarrierData;
use App\Domain\Crm\Repositories\CarrierRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreCarrierRequest;
use App\Http\Requests\Crm\UpdateCarrierRequest;
use App\Http\Resources\CarrierResource;
use App\Models\Carrier;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function __construct(private readonly CarrierRepository $carriers) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $page = $this->carriers->paginate($perPage);

        return ApiResponse::success(
            CarrierResource::collection($page->items()),
            meta: [
                'total' => $page->total(),
                'per_page' => $page->perPage(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function store(StoreCarrierRequest $request): JsonResponse
    {
        $carrier = $this->carriers->create(CarrierData::fromArray($request->validated()));

        return ApiResponse::success(new CarrierResource($carrier), 201);
    }

    public function show(Carrier $carrier): JsonResponse
    {
        return ApiResponse::success(new CarrierResource($carrier->load('contacts')));
    }

    public function update(UpdateCarrierRequest $request, Carrier $carrier): JsonResponse
    {
        $updated = $this->carriers->update($carrier, CarrierData::fromArray($request->validated()));

        return ApiResponse::success(new CarrierResource($updated));
    }

    public function destroy(Carrier $carrier): JsonResponse
    {
        $this->carriers->delete($carrier);

        return ApiResponse::success(['message' => 'Carrier deleted.']);
    }
}
