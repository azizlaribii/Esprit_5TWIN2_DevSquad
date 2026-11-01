<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_kpis_endpoint(): void
    {
        // L'API est protégée par auth:sanctum : on s'authentifie avant d'appeler
        Sanctum::actingAs(User::factory()->create());

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
