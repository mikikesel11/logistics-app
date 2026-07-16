<?php

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Load;
use App\Models\Location;

it('creates a load with freight items and derives margin', function () {
    $user = actingAsOrgUser();
    $customer = Customer::factory()->create(['organization_id' => $user->organization_id]);
    $origin = Location::factory()->create(['organization_id' => $user->organization_id]);

    $response = $this->postJson('/api/loads', [
        'reference' => 'L-1001',
        'customer_id' => $customer->id,
        'origin_location_id' => $origin->id,
        'commodity' => 'Palletized freight',
        'customer_rate_cents' => 200000,
        'carrier_cost_cents' => 160000,
        'freight_items' => [
            ['description' => 'Pallets', 'pieces' => 10, 'freight_class' => '70'],
        ],
    ]);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'reference' => 'L-1001',
                'status' => 'quoted',
                'margin_cents' => 40000,
                'freight_items' => [['description' => 'Pallets']],
            ],
        ]);

    $this->assertDatabaseHas('loads', ['reference' => 'L-1001', 'status' => 'quoted']);
    $this->assertDatabaseHas('freight_items', ['description' => 'Pallets', 'pieces' => 10]);
});

it('rejects a customer from another organization', function () {
    $foreignCustomer = Customer::factory()->create(); // different org
    actingAsOrgUser();

    $this->postJson('/api/loads', [
        'customer_id' => $foreignCustomer->id,
        'customer_rate_cents' => 1000,
    ])->assertStatus(422)->assertJsonStructure(['error' => ['details' => ['customer_id']]]);
});

it('lists and filters loads by status', function () {
    $user = actingAsOrgUser();
    Load::factory()->create(['organization_id' => $user->organization_id, 'status' => 'quoted']);
    Load::factory()->create(['organization_id' => $user->organization_id, 'status' => 'booked']);

    $this->getJson('/api/loads')->assertOk()->assertJsonCount(2, 'data');
    $this->getJson('/api/loads?status=booked')->assertOk()->assertJsonCount(1, 'data');
});

it('assigns a carrier via update', function () {
    $user = actingAsOrgUser();
    $load = Load::factory()->create(['organization_id' => $user->organization_id]);
    $carrier = Carrier::factory()->create(['organization_id' => $user->organization_id]);

    $this->putJson("/api/loads/{$load->id}", ['carrier_id' => $carrier->id])
        ->assertOk()
        ->assertJson(['data' => ['carrier_id' => $carrier->id]]);
});
