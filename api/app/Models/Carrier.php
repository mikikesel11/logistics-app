<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\CarrierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Carrier extends Model
{
    /** @use HasFactory<CarrierFactory> */
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'name',
        'mc_number',
        'dot_number',
        'email',
        'phone',
        'insurance_expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'insurance_expires_at' => 'date',
        ];
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }
}
