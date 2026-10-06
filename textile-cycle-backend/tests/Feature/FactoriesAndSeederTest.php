<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Donation;
use App\Models\DonationMatch;
use App\Models\User;
use Database\Seeders\DonIntelligentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactoriesAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_association_factory_builds_relations(): void
    {
        $association = Association::factory()->hasNeeds(2)->create();

        $this->assertSame('association', $association->user->role);
        $this->assertCount(2, $association->needs);
        $this->assertTrue($association->isVerified());
        $this->assertNotEmpty($association->name);
    }

    public function test_donation_factory_builds_photos_and_match(): void
    {
        $donation = Donation::factory()->withPhotos(2)->create();
        $match    = DonationMatch::factory()->requested()->create([
            'donation_id' => $donation->id,
        ]);

        $this->assertSame('user', $donation->user->role);
        $this->assertCount(2, $donation->photos);
        $this->assertSame(DonationMatch::REQUESTED, $match->status);
        $this->assertNotNull($match->association->user_id);
    }

    public function test_unverified_and_review_states(): void
    {
        $this->assertFalse(Association::factory()->unverified()->create()->isVerified());

        $donation = Donation::factory()->needsReview()->create();
        $this->assertSame(Donation::NEEDS_REVIEW, $donation->status);
        $this->assertNull($donation->category);
    }

    public function test_seeder_is_idempotent(): void
    {
        Storage::fake('public');
        config(['textilecycle.ai.key' => null]);

        $this->seed(DonIntelligentSeeder::class);
        $this->seed(DonIntelligentSeeder::class);

        $this->assertSame(5, Association::count());
        $this->assertSame(4, Association::whereNotNull('verified_at')->count());
        $this->assertSame(5, Association::whereNotNull('user_id')->count());

        $amel = User::where('email', 'donateur1@example.test')->firstOrFail();
        $this->assertSame(3, $amel->donations()->count());
        $this->assertSame(1, User::where('email', 'donateur2@example.test')->firstOrFail()->donations()->count());
        $this->assertGreaterThan(0, DonationMatch::count());
    }
}
