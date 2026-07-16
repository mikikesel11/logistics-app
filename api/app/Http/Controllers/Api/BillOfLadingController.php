<?php

namespace App\Http\Controllers\Api;

use App\Domain\Billing\BillOfLadingService;
use App\Http\Controllers\Controller;
use App\Http\Resources\BillOfLadingResource;
use App\Models\Load;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillOfLadingController extends Controller
{
    public function __construct(private readonly BillOfLadingService $service) {}

    /** Generate a Bill of Lading from a load (snapshots parties + freight). */
    public function store(Load $load): JsonResponse
    {
        $bol = $this->service->generateFromLoad($load);

        return ApiResponse::success(new BillOfLadingResource($bol), 201);
    }

    /** Download the most recent BOL PDF for a load. */
    public function download(Load $load): StreamedResponse|JsonResponse
    {
        $bol = $load->billsOfLading()->latest()->first();

        if ($bol === null) {
            return ApiResponse::error('No Bill of Lading has been generated for this load.', 404);
        }

        if (! $bol->isReady() || ! Storage::disk('local')->exists($bol->pdf_path)) {
            // Queued but not yet rendered (async in production).
            return ApiResponse::error('The Bill of Lading PDF is still being generated.', 409);
        }

        return Storage::disk('local')->download($bol->pdf_path, "{$bol->bol_number}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
