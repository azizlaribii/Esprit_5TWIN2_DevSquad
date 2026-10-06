<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DonationDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_really_delete_a_donation(): void
    {
        Storage::fake('public');
        $donation = Donation::factory()->withPhotos(2)->create();
        DonationMatch::factory()->create(['donation_id' => $donation->id]);
        Storage::disk('public')->put("donations/{$donation->id}/a.jpg", 'x');

        $this->actingAs($donation->user)
            ->delete(route('donations.destroy', $donation))
            ->assertRedirect(route('dons.index'));

        $this->assertDatabaseMissing('donations', ['id' => $donation->id]);
        $this->assertDatabaseCount('donation_photos', 0);
        $this->assertDatabaseCount('donation_matches', 0);
        Storage::disk('public')->assertMissing("donations/{$donation->id}/a.jpg");
    }

    public function test_accepted_donation_cannot_be_deleted(): void
    {
        $donation = Donation::factory()->create(['status' => Donation::ACCEPTED]);

        $this->actingAs($donation->user)
            ->delete(route('donations.destroy', $donation))
            ->assertForbidden();

        $this->assertDatabaseHas('donations', ['id' => $donation->id]);
    }

    public function test_other_user_cannot_delete(): void
    {
        $donation = Donation::factory()->create();
        $other    = User::forceCreate(['name' => 'Autre', 'email' => 'autre@example.test', 'password' => bcrypt('password'), 'role' => 'user']);

        $this->actingAs($other)->delete(route('donations.destroy', $donation))->assertForbidden();
    }
}
