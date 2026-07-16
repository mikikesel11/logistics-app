<?php

it('returns a healthy status in the API envelope', function () {
    $response = $this->getJson('/api/health');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['status' => 'ok'],
            'error' => null,
        ])
        ->assertJsonStructure([
            'success',
            'data' => ['status', 'app', 'time'],
            'error',
        ]);
});
