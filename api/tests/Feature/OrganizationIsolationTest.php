<?php

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Organization;

it('does not list another organization\'s customers', function () {
    $otherOrg = Organization::factory()->create();
    Customer::factory()->count(2)->create(['organization_id' => $otherOrg->id]);

    $user = actingAsOrgUser();
    Customer::factory()->create(['organization_id' => $user->organization_id]);

    $this->getJson('/api/customers')
        ->assertOk()
        ->assertJsonCount(1, 'data'); // only own org's customer
});

it('returns 404 when accessing another organization\'s customer', function () {
    $otherOrg = Organization::factory()->create();
    $foreign = Customer::factory()->create(['organization_id' => $otherOrg->id]);

    actingAsOrgUser();

    $this->getJson("/api/customers/{$foreign->id}")->assertStatus(404);
});

it('cannot update or delete another organization\'s carrier', function () {
    $otherOrg = Organization::factory()->create();
    $foreign = Carrier::factory()->create(['organization_id' => $otherOrg->id]);

    actingAsOrgUser();

    $this->putJson("/api/carriers/{$foreign->id}", ['name' => 'Hijack'])->assertStatus(404);
    $this->deleteJson("/api/carriers/{$foreign->id}")->assertStatus(404);

    $this->assertDatabaseHas('carriers', ['id' => $foreign->id, 'organization_id' => $otherOrg->id]);
});
