<?php

namespace App\Domain\Loads;

use App\Models\Load;
use Illuminate\Support\Facades\DB;

/**
 * Load lifecycle operations. Status only moves through guarded transitions.
 */
class LoadService
{
    public function create(LoadData $data): Load
    {
        return DB::transaction(function () use ($data) {
            $load = Load::query()->create($data->toAttributes());
            $this->syncFreightItems($load, $data->freightItems);

            return $load->load(['freightItems', 'customer', 'carrier', 'origin', 'destination']);
        });
    }

    public function update(Load $load, LoadData $data): Load
    {
        return DB::transaction(function () use ($load, $data) {
            $load->update($data->toAttributes());

            if ($data->freightItems !== null) {
                $load->freightItems()->delete();
                $this->syncFreightItems($load, $data->freightItems);
            }

            return $load->fresh(['freightItems', 'customer', 'carrier', 'origin', 'destination']);
        });
    }

    /**
     * @throws InvalidStatusTransition
     */
    public function transitionTo(Load $load, LoadStatus $target): Load
    {
        $current = $load->status;

        if (! $current->canTransitionTo($target)) {
            throw new InvalidStatusTransition($current, $target);
        }

        $load->update(['status' => $target]);

        return $load->fresh();
    }

    /**
     * @param  list<array<string, mixed>>|null  $items
     */
    private function syncFreightItems(Load $load, ?array $items): void
    {
        foreach ($items ?? [] as $item) {
            $load->freightItems()->create($item);
        }
    }
}
