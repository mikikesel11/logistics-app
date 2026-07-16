<?php

namespace App\Http\Requests\Loads;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class StoreLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $orgId = $this->user()->organization_id;

        return [
            'reference' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', $this->existsInOrg('customers', $orgId)],
            'carrier_id' => ['nullable', $this->existsInOrg('carriers', $orgId)],
            'origin_location_id' => ['nullable', $this->existsInOrg('locations', $orgId)],
            'destination_location_id' => ['nullable', $this->existsInOrg('locations', $orgId)],
            'commodity' => ['nullable', 'string', 'max:255'],
            'weight_lbs' => ['nullable', 'integer', 'min:0'],
            'pickup_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:pickup_date'],
            'customer_rate_cents' => ['nullable', 'integer', 'min:0'],
            'carrier_cost_cents' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'freight_items' => ['sometimes', 'array'],
            'freight_items.*.description' => ['required', 'string', 'max:255'],
            'freight_items.*.pieces' => ['nullable', 'integer', 'min:1'],
            'freight_items.*.weight_lbs' => ['nullable', 'integer', 'min:0'],
            'freight_items.*.freight_class' => ['nullable', 'string', 'max:20'],
        ];
    }

    /** Ensure a referenced record belongs to the caller's organization. */
    private function existsInOrg(string $table, int $orgId): Exists
    {
        return Rule::exists($table, 'id')->where('organization_id', $orgId);
    }
}
