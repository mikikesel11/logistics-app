<?php

use App\Models\Location;
use App\Models\Organization;

it('requires authentication', function () {
    $this->getJson('/api/locations')->assertStatus(401);
});

it('lists locations for the current organization with pagination meta', function () {
    $user = actingAsOrgUser();
    Location::factory()->count(3)->create(['organization_id' => $user->organization_id]);

    $this->getJson('/api/locations')
        ->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['meta' => ['total', 'per_page', 'current_page', 'last_page']]);
});

it('creates a location', function () {
    actingAsOrgUser();

    $this->postJson('/api/locations', [
        'name' => 'Acme DC',
        'address_line1' => '100 Dock St',
        'city' => 'Columbus',
        'state' => 'OH',
        'postal_code' => '43004',
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => ['name' => 'Acme DC', 'city' => 'Columbus', 'state' => 'OH', 'country' => 'US'],
        ]);

    $this->assertDatabaseHas('locations', ['name' => 'Acme DC', 'address_line1' => '100 Dock St']);
});

it('validates required address fields', function () {
    actingAsOrgUser();

    $this->postJson('/api/locations', ['name' => 'Missing address'])
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['address_line1', 'city', 'state', 'postal_code']]]);
});

it('shows a location', function () {
    $user = actingAsOrgUser();
    $location = Location::factory()->create(['organization_id' => $user->organization_id]);

    $this->getJson("/api/locations/{$location->id}")
        ->assertOk()
        ->assertJson(['data' => ['id' => $location->id]]);
});

it('updates a location', function () {
    $user = actingAsOrgUser();
    $location = Location::factory()->create(['organization_id' => $user->organization_id]);

    $this->putJson("/api/locations/{$location->id}", [
        'name' => 'Renamed Facility',
        'address_line1' => '200 New Rd',
        'city' => 'Reno',
        'state' => 'NV',
        'postal_code' => '89501',
    ])->assertOk()->assertJson(['data' => ['name' => 'Renamed Facility', 'city' => 'Reno']]);

    $this->assertDatabaseHas('locations', ['id' => $location->id, 'city' => 'Reno']);
});

it('deletes a location', function () {
    $user = actingAsOrgUser();
    $location = Location::factory()->create(['organization_id' => $user->organization_id]);

    $this->deleteJson("/api/locations/{$location->id}")->assertOk();

    $this->assertDatabaseMissing('locations', ['id' => $location->id]);
});

it('does not expose locations from another organization', function () {
    actingAsOrgUser();
    $other = Organization::factory()->create();
    $foreign = Location::factory()->create(['organization_id' => $other->id]);

    // Not listed…
    $this->getJson('/api/locations')->assertOk()->assertJsonCount(0, 'data');
    // …and not reachable by id (global scope hides it → 404).
    $this->getJson("/api/locations/{$foreign->id}")->assertStatus(404);
});
