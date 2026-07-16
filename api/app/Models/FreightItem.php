<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\FreightItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreightItem extends Model
{
    /** @use HasFactory<FreightItemFactory> */
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'load_id',
        'description',
        'pieces',
        'weight_lbs',
        'freight_class',
    ];

    protected function casts(): array
    {
        return [
            'pieces' => 'integer',
            'weight_lbs' => 'integer',
        ];
    }

    // Named parentLoad() to avoid colliding with Eloquent's Model::load().
    public function parentLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }
}
