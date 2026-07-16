<?php

namespace App\Http\Controllers\Api;

use App\Domain\Loads\LoadData;
use App\Domain\Loads\LoadService;
use App\Domain\Loads\LoadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loads\StoreLoadRequest;
use App\Http\Requests\Loads\UpdateLoadRequest;
use App\Http\Requests\Loads\UpdateLoadStatusRequest;
use App\Http\Resources\LoadResource;
use App\Models\Load;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoadController extends Controller
{
    public function __construct(private readonly LoadService $loads) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);

        $page = Load::query()
            ->with(['customer', 'carrier', 'origin', 'destination'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate($perPage);

        return ApiResponse::success(
            LoadResource::collection($page->items()),
            meta: [
                'total' => $page->total(),
                'per_page' => $page->perPage(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function store(StoreLoadRequest $request): JsonResponse
    {
        $load = $this->loads->create(LoadData::fromArray($request->validated()));

        return ApiResponse::success(new LoadResource($load), 201);
    }

    public function show(Load $load): JsonResponse
    {
        $load->load(['customer', 'carrier', 'origin', 'destination', 'freightItems']);

        return ApiResponse::success(new LoadResource($load));
    }

    public function update(UpdateLoadRequest $request, Load $load): JsonResponse
    {
        $updated = $this->loads->update($load, LoadData::fromArray($request->validated()));

        return ApiResponse::success(new LoadResource($updated));
    }

    /** Guarded status transition — invalid moves return 422. */
    public function updateStatus(UpdateLoadStatusRequest $request, Load $load): JsonResponse
    {
        $target = LoadStatus::from($request->validated('status'));
        $updated = $this->loads->transitionTo($load, $target);

        return ApiResponse::success(new LoadResource($updated));
    }

    public function destroy(Load $load): JsonResponse
    {
        $load->delete();

        return ApiResponse::success(['message' => 'Load deleted.']);
    }
}
