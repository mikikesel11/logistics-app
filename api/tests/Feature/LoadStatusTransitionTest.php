<?php

use App\Models\Load;

it('allows a legal forward transition', function () {
    $user = actingAsOrgUser();
    $load = Load::factory()->create([
        'organization_id' => $user->organization_id,
        'status' => 'quoted',
    ]);

    $this->patchJson("/api/loads/{$load->id}/status", ['status' => 'booked'])
        ->assertOk()
        ->assertJson(['data' => ['status' => 'booked']]);

    expect($load->fresh()->status->value)->toBe('booked');
});

it('rejects an illegal transition with 422', function () {
    $user = actingAsOrgUser();
    $load = Load::factory()->create([
        'organization_id' => $user->organization_id,
        'status' => 'quoted',
    ]);

    // quoted cannot jump straight to delivered
    $this->patchJson("/api/loads/{$load->id}/status", ['status' => 'delivered'])
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($load->fresh()->status->value)->toBe('quoted');
});

it('validates the status value', function () {
    $user = actingAsOrgUser();
    $load = Load::factory()->create(['organization_id' => $user->organization_id]);

    $this->patchJson("/api/loads/{$load->id}/status", ['status' => 'bogus'])
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['status']]]);
});

it('walks a load through the full lifecycle', function () {
    $user = actingAsOrgUser();
    $load = Load::factory()->create([
        'organization_id' => $user->organization_id,
        'status' => 'quoted',
    ]);

    foreach (['booked', 'dispatched', 'in_transit', 'delivered', 'invoiced'] as $next) {
        $this->patchJson("/api/loads/{$load->id}/status", ['status' => $next])->assertOk();
    }

    expect($load->fresh()->status->value)->toBe('invoiced');
});
