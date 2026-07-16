<?php

namespace App\Http\Controllers\Api;

use App\Domain\LoadBoard\LoadBoardProvider;
use App\Domain\LoadBoard\LoadBoardSearch;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Searchable load board. Backed by InternalLoadBoardProvider today; the same
 * endpoint serves external providers (DAT, …) once bound.
 */
class LoadBoardController extends Controller
{
    public function __construct(private readonly LoadBoardProvider $provider) {}

    public function index(Request $request): JsonResponse
    {
        $results = $this->provider->search(LoadBoardSearch::fromArray($request->query()));

        return ApiResponse::success(
            $results->map->toArray()->values(),
            meta: ['source' => $this->provider->name(), 'count' => $results->count()],
        );
    }
}
