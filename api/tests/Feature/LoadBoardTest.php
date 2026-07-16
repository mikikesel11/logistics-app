<?php

use App\Models\Load;
use App\Models\Location;

it('returns internal loads from the load board', function () {
    $user = actingAsOrgUser();
    Load::factory()->count(2)->create(['organization_id' => $user->organization_id]);

    $this->getJson('/api/load-board')
        ->assertOk()
        ->assertJson(['success' => true, 'meta' => ['source' => 'internal', 'count' => 2]])
        ->assertJsonCount(2, 'data');
});

it('filters the load board by origin state', function () {
    $user = actingAsOrgUser();
    $tx = Location::factory()->create(['organization_id' => $user->organization_id, 'state' => 'TX']);
    $ca = Location::factory()->create(['organization_id' => $user->organization_id, 'state' => 'CA']);
    Load::factory()->create(['organization_id' => $user->organization_id, 'origin_location_id' => $tx->id]);
    Load::factory()->create(['organization_id' => $user->organization_id, 'origin_location_id' => $ca->id]);

    $this->getJson('/api/load-board?origin_state=TX')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJson(['data' => [['origin' => $tx->shortLabel()]]]);
});

it('does not expose another organization\'s loads on the board', function () {
    Load::factory()->count(3)->create(); // other orgs
    $user = actingAsOrgUser();
    Load::factory()->create(['organization_id' => $user->organization_id]);

    $this->getJson('/api/load-board')->assertOk()->assertJsonCount(1, 'data');
});
