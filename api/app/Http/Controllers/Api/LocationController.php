<?php

namespace App\Http\Controllers\Api;

use App\Domain\Crm\Data\LocationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreLocationRequest;
use App\Http\Requests\Crm\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Reusable addresses (origin / consignee / facility). A supporting entity, so
 * it talks to Eloquent directly — no repository (see the plan's "repository for
 * core aggregates only" note). Org scoping is applied by the model's global
 * scope, and organization_id auto-fills on create.
 */
class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 50);
        $page = Location::query()->latest()->paginate($perPage);

        return ApiResponse::success(
            LocationResource::collection($page->items()),
            meta: [
                'total' => $page->total(),
                'per_page' => $page->perPage(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = Location::query()->create(
            LocationData::fromArray($request->validated())->toAttributes()
        );

        return ApiResponse::success(new LocationResource($location), 201);
    }

    public function show(Location $location): JsonResponse
    {
        return ApiResponse::success(new LocationResource($location));
    }

    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        $location->update(LocationData::fromArray($request->validated())->toAttributes());

        return ApiResponse::success(new LocationResource($location->fresh()));
    }

    public function destroy(Location $location): JsonResponse
    {
        $location->delete();

        return ApiResponse::success(['message' => 'Location deleted.']);
    }
}
