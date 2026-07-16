<?php

use App\Models\Carrier;
use App\Models\Organization;

it('creates a carrier with MC/DOT numbers', function () {
    actingAsOrgUser();

    $this->postJson('/api/carriers', [
        'name' => 'Roadrunner LLC',
        'mc_number' => 'MC123456',
        'dot_number' => '9876543',
        'insurance_expires_at' => '2027-01-01',
    ])->assertCreated()
        ->assertJson(['data' => ['name' => 'Roadrunner LLC', 'mc_number' => 'MC123456']]);

    $this->assertDatabaseHas('carriers', ['mc_number' => 'MC123456']);
});

it('rejects a duplicate MC number within the same organization', function () {
    $user = actingAsOrgUser();
    Carrier::factory()->create([
        'organization_id' => $user->organization_id,
        'mc_number' => 'MC999999',
    ]);

    $this->postJson('/api/carriers', ['name' => 'Dup', 'mc_number' => 'MC999999'])
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['mc_number']]]);
});

it('allows the same MC number across different organizations', function () {
    $orgA = Organization::factory()->create();
    Carrier::factory()->create(['organization_id' => $orgA->id, 'mc_number' => 'MC555000']);

    // Different org — same MC number should be allowed.
    actingAsOrgUser();
    $this->postJson('/api/carriers', ['name' => 'Other Org Carrier', 'mc_number' => 'MC555000'])
        ->assertCreated();
});

it('lists and shows carriers', function () {
    $user = actingAsOrgUser();
    $carrier = Carrier::factory()->create(['organization_id' => $user->organization_id]);

    $this->getJson('/api/carriers')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/carriers/{$carrier->id}")->assertOk()
        ->assertJson(['data' => ['id' => $carrier->id]]);
});

it('updates a carrier keeping its own MC number', function () {
    $user = actingAsOrgUser();
    $carrier = Carrier::factory()->create([
        'organization_id' => $user->organization_id,
        'mc_number' => 'MC246810',
    ]);

    $this->putJson("/api/carriers/{$carrier->id}", [
        'name' => 'Renamed Carrier',
        'mc_number' => 'MC246810',
    ])->assertOk()->assertJson(['data' => ['name' => 'Renamed Carrier']]);
});

it('deletes a carrier', function () {
    $user = actingAsOrgUser();
    $carrier = Carrier::factory()->create(['organization_id' => $user->organization_id]);

    $this->deleteJson("/api/carriers/{$carrier->id}")->assertOk();
    $this->assertDatabaseMissing('carriers', ['id' => $carrier->id]);
});
