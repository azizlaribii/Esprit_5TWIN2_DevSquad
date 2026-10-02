<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_kpis_endpoint(): void
    {
        $response = $this->getJson('/api/dashboard/kpis');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'label',
                             'valeur',
                             'variation',
                             'unite',
                             'icone',
                             'couleur',
                         ]
                     ]
                 ]);
    }
}
