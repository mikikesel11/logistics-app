<?php

namespace App\Models;

use App\Domain\Loads\LoadStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\LoadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Load extends Model
{
    /** @use HasFactory<LoadFactory> */
    use BelongsToOrganization, HasFactory;

    /**
     * Defaults so a freshly-created model reflects the DB column defaults
     * without needing a refresh (status cast would otherwise be null).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'quoted',
        'customer_rate_cents' => 0,
        'carrier_cost_cents' => 0,
    ];

    protected $fillable = [
        'reference',
        'status',
        'customer_id',
        'carrier_id',
        'origin_location_id',
        'destination_location_id',
        'commodity',
        'weight_lbs',
        'pickup_date',
        'delivery_date',
        'customer_rate_cents',
        'carrier_cost_cents',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoadStatus::class,
            'pickup_date' => 'date',
            'delivery_date' => 'date',
            'customer_rate_cents' => 'integer',
            'carrier_cost_cents' => 'integer',
            'weight_lbs' => 'integer',
        ];
    }

    /** Derived gross margin in cents (never persisted). */
    public function marginCents(): int
    {
        return $this->customer_rate_cents - $this->carrier_cost_cents;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function freightItems(): HasMany
    {
        return $this->hasMany(FreightItem::class);
    }

    public function billsOfLading(): HasMany
    {
        return $this->hasMany(BillOfLading::class);
    }
}
