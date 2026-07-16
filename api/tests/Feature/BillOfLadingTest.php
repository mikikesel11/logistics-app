<?php

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Load;
use App\Models\Location;
use Illuminate\Support\Facades\Storage;

function loadWithParties(int $orgId): Load
{
    $customer = Customer::factory()->create(['organization_id' => $orgId]);
    $carrier = Carrier::factory()->create(['organization_id' => $orgId]);
    $origin = Location::factory()->create(['organization_id' => $orgId, 'state' => 'TX']);
    $destination = Location::factory()->create(['organization_id' => $orgId, 'state' => 'CA']);

    $load = Load::factory()->create([
        'organization_id' => $orgId,
        'customer_id' => $customer->id,
        'carrier_id' => $carrier->id,
        'origin_location_id' => $origin->id,
        'destination_location_id' => $destination->id,
    ]);

    $load->freightItems()->create([
        'organization_id' => $orgId,
        'description' => 'Pallets of goods',
        'pieces' => 12,
        'weight_lbs' => 3000,
        'freight_class' => '70',
    ]);

    return $load;
}

it('generates a BOL from a load and snapshots parties + freight', function () {
    $user = actingAsOrgUser();
    $load = loadWithParties($user->organization_id);

    $response = $this->postJson("/api/loads/{$load->id}/bol");

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'load_id' => $load->id,
                'is_ready' => true, // sync queue renders inline
                'freight' => [['description' => 'Pallets of goods', 'pieces' => 12]],
                // Origin/destination addresses are snapshotted onto the BOL.
                'ship_from' => ['state' => 'TX'],
                'ship_to' => ['state' => 'CA'],
            ],
        ]);

    $this->assertDatabaseHas('bills_of_lading', ['load_id' => $load->id]);

    // PDF artifact was written.
    $bol = $load->billsOfLading()->first();
    Storage::disk('local')->assertExists($bol->pdf_path);
});

it('downloads the generated BOL as a PDF', function () {
    $user = actingAsOrgUser();
    $load = loadWithParties($user->organization_id);
    $this->postJson("/api/loads/{$load->id}/bol")->assertCreated();

    $response = $this->get("/api/loads/{$load->id}/bol/download");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('returns 404 downloading a BOL for a load that has none', function () {
    $user = actingAsOrgUser();
    $load = Load::factory()->create(['organization_id' => $user->organization_id]);

    $this->getJson("/api/loads/{$load->id}/bol/download")->assertStatus(404);
});

it('will not generate a BOL for another organization\'s load', function () {
    $foreignLoad = Load::factory()->create(); // other org
    actingAsOrgUser();

    $this->postJson("/api/loads/{$foreignLoad->id}/bol")->assertStatus(404);
});
