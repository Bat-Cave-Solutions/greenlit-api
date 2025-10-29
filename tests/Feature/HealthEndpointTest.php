<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['status', 'app', 'time', 'commit'])
            ->assertJson(['status' => 'ok']);
    }
}
