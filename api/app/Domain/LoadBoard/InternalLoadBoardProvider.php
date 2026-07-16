<?php

namespace App\Domain\LoadBoard;

use App\Models\Load;
use Illuminate\Support\Collection;

/**
 * Load board backed by the broker's own loads. Org scoping is applied
 * automatically by the Load model's global scope.
 */
class InternalLoadBoardProvider implements LoadBoardProvider
{
    public function name(): string
    {
        return 'internal';
    }

    public function search(LoadBoardSearch $search): Collection
    {
        return Load::query()
            ->with(['origin', 'destination'])
            ->when($search->status, fn ($q, $status) => $q->where('status', $status))
            ->when($search->originState, fn ($q, $state) => $q->whereHas(
                'origin', fn ($o) => $o->where('state', $state)
            ))
            ->when($search->destinationState, fn ($q, $state) => $q->whereHas(
                'destination', fn ($d) => $d->where('state', $state)
            ))
            ->latest()
            ->limit($search->limit)
            ->get()
            ->map(fn (Load $load) => new LoadBoardResult(
                source: $this->name(),
                reference: $load->reference,
                origin: $load->origin?->shortLabel(),
                destination: $load->destination?->shortLabel(),
                commodity: $load->commodity,
                weightLbs: $load->weight_lbs,
                rateCents: $load->customer_rate_cents,
                pickupDate: $load->pickup_date?->toDateString(),
                internalLoadId: $load->id,
            ));
    }
}
