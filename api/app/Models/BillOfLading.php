<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\BillOfLadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillOfLading extends Model
{
    /** @use HasFactory<BillOfLadingFactory> */
    use BelongsToOrganization, HasFactory;

    protected $table = 'bills_of_lading';

    protected $fillable = [
        'load_id',
        'bol_number',
        'customer_name',
        'carrier_name',
        'ship_from',
        'ship_to',
        'freight',
        'special_instructions',
        'pdf_path',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'ship_from' => 'array',
            'ship_to' => 'array',
            'freight' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function isReady(): bool
    {
        return $this->pdf_path !== null;
    }

    public function parentLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }
}
