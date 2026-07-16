<?php

namespace App\Support\Tenancy;

use Illuminate\Support\Facades\Auth;

/**
 * Resolves the "current organization" for tenant scoping.
 *
 * Priority:
 *   1. An explicit override (set for queued jobs / console commands that run
 *      outside an authenticated request).
 *   2. The authenticated user's organization.
 *
 * When neither is present (e.g. seeding, login lookups) the context is empty
 * and the global scope does not filter — this is intentional so bootstrapping
 * and cross-org console tasks can operate. The security boundary is the
 * authenticated API request, where a user is always present.
 *
 * Registered as a singleton so an override persists for the request/job.
 */
class TenantContext
{
    private ?int $organizationId = null;

    private bool $overridden = false;

    public function set(?int $organizationId): void
    {
        $this->organizationId = $organizationId;
        $this->overridden = true;
    }

    public function clear(): void
    {
        $this->organizationId = null;
        $this->overridden = false;
    }

    public function id(): ?int
    {
        if ($this->overridden) {
            return $this->organizationId;
        }

        return Auth::user()?->organization_id;
    }

    public function has(): bool
    {
        return $this->id() !== null;
    }
}
