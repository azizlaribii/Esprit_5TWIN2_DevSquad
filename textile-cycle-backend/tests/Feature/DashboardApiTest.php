<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    public function test_dashboard_kpis_endpoint(): void
    {
        $response = $this->getJson('/api/dashboard/kpis');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'depots_total',
                         'reparations_total',
                         'dons_total',
                     ]
                 ]);
    }
}
