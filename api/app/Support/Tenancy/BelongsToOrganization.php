<?php

namespace App\Support\Tenancy;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to any tenant-scoped model. Adds the organization global scope and
 * auto-fills organization_id from the current tenant on create.
 *
 * Requires the table to have an `organization_id` column.
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope);

        static::creating(function ($model) {
            if (empty($model->organization_id)) {
                $context = app(TenantContext::class);

                if ($context->has()) {
                    $model->organization_id = $context->id();
                }
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
