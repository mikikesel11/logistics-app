<?php

namespace App\Domain\Billing;

use App\Models\BillOfLading;
use App\Support\Tenancy\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Renders a Bill of Lading to PDF with dompdf (pure PHP — shared-hosting safe)
 * and stores the artifact. Runs on the queue in production; inline under the
 * sync driver locally and in tests.
 */
class GenerateBillOfLadingPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $billOfLadingId) {}

    public function handle(TenantContext $tenant): void
    {
        // Jobs run without an authenticated user; scope to the BOL's org so the
        // global tenant scope can still resolve the record.
        $bol = BillOfLading::withoutGlobalScopes()->findOrFail($this->billOfLadingId);
        $tenant->set($bol->organization_id);

        $pdf = Pdf::loadView('bol.pdf', ['bol' => $bol])->setPaper('letter');

        $path = "bol/{$bol->bol_number}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        $bol->update([
            'pdf_path' => $path,
            'generated_at' => now(),
        ]);
    }
}
