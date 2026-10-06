<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_form_shows_french_errors_per_field(): void
    {
        $donor = User::forceCreate(['name' => 'Donateur', 'email' => 'd@example.test', 'password' => bcrypt('password'), 'role' => 'user']);

        $this->actingAs($donor)
            ->from(route('donations.create'))
            ->followingRedirects()
            ->post(route('donations.store'), ['title' => '', 'quantity' => 0, 'city' => ''])
            ->assertSee('Le champ titre est obligatoire.')
            ->assertSee('Le champ ville est obligatoire.')
            ->assertSee('Le champ quantité doit être au moins égal à 1.')
            ->assertSee('Le champ photos est obligatoire.');
    }

    public function test_need_form_shows_french_errors(): void
    {
        $association = Association::factory()->create();

        $this->actingAs($association->user)
            ->from(route('association.needs.index'))
            ->followingRedirects()
            ->post(route('association.needs.store'), ['urgency' => 9])
            ->assertSee('Le champ catégorie est obligatoire.')
            ->assertSee('Le champ quantité nécessaire est obligatoire.')
            ->assertSee('Le champ urgence doit être compris entre 1 et 5.');
    }
}
