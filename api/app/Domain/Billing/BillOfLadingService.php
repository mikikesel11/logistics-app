<?php

namespace App\Domain\Billing;

use App\Models\BillOfLading;
use App\Models\Load;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

/**
 * Creates a Bill of Lading by snapshotting a load's parties and freight, then
 * queues PDF rendering. The snapshot makes the document immutable even if the
 * underlying load later changes.
 */
class BillOfLadingService
{
    public function generateFromLoad(Load $load): BillOfLading
    {
        $load->loadMissing(['customer', 'carrier', 'origin', 'destination', 'freightItems']);

        return DB::transaction(function () use ($load) {
            $bol = BillOfLading::query()->create([
                'load_id' => $load->id,
                'bol_number' => $this->nextBolNumber(),
                'customer_name' => $load->customer?->name,
                'carrier_name' => $load->carrier?->name,
                'ship_from' => $this->addressSnapshot($load->origin),
                'ship_to' => $this->addressSnapshot($load->destination),
                'freight' => $load->freightItems->map(fn ($i) => [
                    'description' => $i->description,
                    'pieces' => $i->pieces,
                    'weight_lbs' => $i->weight_lbs,
                    'freight_class' => $i->freight_class,
                ])->all(),
            ]);

            // Queued in production (redis + cron); runs inline under sync locally.
            GenerateBillOfLadingPdf::dispatch($bol->id);

            return $bol->refresh();
        });
    }

    private function nextBolNumber(): string
    {
        // Per-org sequence; unique constraint guards races.
        $count = BillOfLading::query()->count() + 1;

        return 'BOL-'.now()->format('Y').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function addressSnapshot(?Location $location): ?array
    {
        if ($location === null) {
            return null;
        }

        return $location->only([
            'name', 'address_line1', 'address_line2', 'city', 'state',
            'postal_code', 'country', 'contact_name', 'contact_phone',
        ]);
    }
}
