<?php

namespace App\Domain\LoadBoard;

use Illuminate\Support\Collection;

/**
 * A source of available loads. The MVP ships one implementation backed by the
 * broker's own loads (InternalLoadBoardProvider). External, partner-gated
 * providers (DAT, Truckstop, 123Loadboard) implement this same contract later
 * and are swapped in via the container — no calling code changes.
 *
 * @see docs/domain-model.md for the extension contract.
 */
interface LoadBoardProvider
{
    /**
     * @return Collection<int, LoadBoardResult>
     */
    public function search(LoadBoardSearch $search): Collection;

    /** Stable identifier for the source, e.g. "internal", "dat". */
    public function name(): string;
}
