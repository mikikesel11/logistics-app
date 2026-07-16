<?php

use App\Models\Customer;

it('requires authentication', function () {
    $this->getJson('/api/customers')->assertStatus(401);
});

it('lists customers for the current organization with pagination meta', function () {
    $user = actingAsOrgUser();
    Customer::factory()->count(3)->create(['organization_id' => $user->organization_id]);

    $this->getJson('/api/customers')
        ->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['meta' => ['total', 'per_page', 'current_page', 'last_page']]);
});

it('creates a customer with nested contacts', function () {
    actingAsOrgUser();

    $response = $this->postJson('/api/customers', [
        'name' => 'Acme Shipping',
        'email' => 'ap@acme.test',
        'billing_terms' => 'Net 30',
        'contacts' => [
            ['name' => 'Jane Doe', 'title' => 'AP Manager', 'is_primary' => true],
        ],
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => ['name' => 'Acme Shipping', 'contacts' => [['name' => 'Jane Doe']]],
        ]);

    $this->assertDatabaseHas('customers', ['name' => 'Acme Shipping']);
    $this->assertDatabaseHas('contacts', ['name' => 'Jane Doe', 'is_primary' => true]);
});

it('validates customer input', function () {
    actingAsOrgUser();

    $this->postJson('/api/customers', ['email' => 'nope'])
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['name', 'email']]]);
});

it('shows a customer', function () {
    $user = actingAsOrgUser();
    $customer = Customer::factory()->create(['organization_id' => $user->organization_id]);

    $this->getJson("/api/customers/{$customer->id}")
        ->assertOk()
        ->assertJson(['data' => ['id' => $customer->id]]);
});

it('updates a customer and replaces contacts', function () {
    $user = actingAsOrgUser();
    $customer = Customer::factory()->create(['organization_id' => $user->organization_id]);
    $customer->contacts()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Old Contact',
    ]);

    $this->putJson("/api/customers/{$customer->id}", [
        'name' => 'Renamed Co',
        'contacts' => [['name' => 'New Contact']],
    ])->assertOk()->assertJson(['data' => ['name' => 'Renamed Co']]);

    $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Renamed Co']);
    $this->assertDatabaseMissing('contacts', ['name' => 'Old Contact']);
    $this->assertDatabaseHas('contacts', ['name' => 'New Contact']);
});

it('deletes a customer and its contacts', function () {
    $user = actingAsOrgUser();
    $customer = Customer::factory()->create(['organization_id' => $user->organization_id]);
    $customer->contacts()->create(['organization_id' => $user->organization_id, 'name' => 'C']);

    $this->deleteJson("/api/customers/{$customer->id}")->assertOk();

    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    $this->assertDatabaseMissing('contacts', ['contactable_id' => $customer->id, 'contactable_type' => Customer::class]);
});
