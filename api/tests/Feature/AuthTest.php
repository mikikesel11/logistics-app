<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

function makeUser(): User
{
    $organization = Organization::factory()->create();

    return User::factory()->create([
        'organization_id' => $organization->id,
        'password' => Hash::make('password'),
    ]);
}

it('logs in with valid credentials and returns a token', function () {
    $user = makeUser();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['user' => ['email' => $user->email]],
            'error' => null,
        ])
        ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'email', 'organization_id']]]);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
});

it('rejects invalid credentials with a 401 envelope', function () {
    $user = makeUser();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])
        ->assertStatus(401)
        ->assertJson([
            'success' => false,
            'data' => null,
            'error' => ['message' => 'The provided credentials are incorrect.'],
        ]);
});

it('validates login input', function () {
    $this->postJson('/api/login', ['email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJson(['success' => false])
        ->assertJsonStructure(['error' => ['message', 'details' => ['email', 'password']]]);
});

it('returns the authenticated user from /api/me', function () {
    $user = makeUser();

    Sanctum::actingAs($user);

    $this->getJson('/api/me')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['id' => $user->id, 'email' => $user->email],
        ]);
});

it('blocks /api/me without authentication', function () {
    $this->getJson('/api/me')
        ->assertStatus(401)
        ->assertJson(['success' => false, 'error' => ['message' => 'Unauthenticated.']]);
});

it('logs out by revoking the current token', function () {
    $user = makeUser();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)->postJson('/api/logout')
        ->assertOk()
        ->assertJson(['success' => true, 'data' => ['message' => 'Logged out.']]);

    expect($user->fresh()->tokens()->count())->toBe(0);
});
