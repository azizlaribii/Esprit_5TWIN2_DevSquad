<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeDonation;
use App\Models\Association;
use App\Models\Donation;
use App\Models\DonationMatch;
use App\Models\User;
use App\Notifications\MatchAccepted;
use App\Notifications\NewDonationMatch;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Parcours complet : suggestion → choix du donateur → acceptation → remise.
 * Suppose le branchement décrit dans le README (alias « role », routes, trait sur User).
 */
class DonationFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $donor;
    private User $assocUser;
    private Association $association;
    private Donation $donation;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Queue::fake();
        config(['textilecycle.ai.key' => null]);

        $this->donor     = $this->user('user', 'donateur@example.test');
        $this->assocUser = $this->user('association', 'asso@example.test');

        $this->association = Association::create([
            'user_id' => $this->assocUser->id, 'name' => 'Association test', 'city' => 'Tunis',
            'lat' => 36.8065, 'lng' => 10.1815, 'capacity' => 100,
            'accepted_conditions' => ['neuf', 'bon'], 'accepted_categories' => [], 'verified_at' => now(),
        ]);
        $this->association->needs()->create(['category' => 'manteau', 'quantity_needed' => 10, 'urgency' => 5]);

        $this->donation = Donation::create([
            'user_id' => $this->donor->id, 'title' => 'Manteau d\'hiver', 'category' => 'manteau',
            'condition' => 'bon', 'quantity' => 3, 'city' => 'Tunis', 'lat' => 36.8065, 'lng' => 10.1815,
            'status' => Donation::PENDING_ANALYSIS,
        ]);

        app(MatchingService::class)->run($this->donation);
    }

    public function test_full_journey(): void
    {
        $match = $this->donation->matches()->first();
        $this->assertSame(Donation::MATCHED, $this->donation->fresh()->status);

        // 1. Le donateur choisit l'association.
        $this->actingAs($this->donor)
            ->post(route('matches.choose', [$this->donation, $match]))
            ->assertRedirect(route('donations.show', $this->donation));

        $this->assertSame(Donation::REQUESTED, $this->donation->fresh()->status);
        Notification::assertSentTo($this->assocUser, NewDonationMatch::class);

        // 2. L'association accepte et fixe un rendez-vous.
        $this->actingAs($this->assocUser)
            ->post(route('association.requests.accept', $match), [
                'meeting_type' => 'depot',
                'meeting_at'   => now()->addDays(2)->format('Y-m-d\TH:i'),
                'meeting_note' => 'Porte B',
            ])
            ->assertRedirect(route('association.dashboard'));

        $this->assertSame(Donation::ACCEPTED, $this->donation->fresh()->status);
        $this->assertSame(DonationMatch::ACCEPTED, $match->fresh()->status);
        Notification::assertSentTo($this->donor, MatchAccepted::class);

        // 3. Remise : le besoin diminue de la quantité reçue.
        $this->actingAs($this->assocUser)
            ->post(route('association.requests.complete', $match))
            ->assertRedirect(route('association.dashboard'));

        $this->assertSame(Donation::COMPLETED, $this->donation->fresh()->status);
        $this->assertSame(7, $this->association->needs()->first()->quantity_needed);
    }

    public function test_rejection_excludes_the_association_and_relaunches_the_search(): void
    {
        $match = $this->donation->matches()->first();

        $this->actingAs($this->donor)->post(route('matches.choose', [$this->donation, $match]));
        $this->actingAs($this->assocUser)->post(route('association.requests.reject', $match));

        $this->assertSame(DonationMatch::REJECTED, $match->fresh()->status);
        $this->assertSame(Donation::PENDING_ANALYSIS, $this->donation->fresh()->status);
        Queue::assertPushed(AnalyzeDonation::class);

        // Le job relancé ne re-propose pas l'association qui a décliné.
        app(MatchingService::class)->run($this->donation->fresh());
        $this->assertSame(Donation::NO_MATCH, $this->donation->fresh()->status);
    }

    public function test_strangers_cannot_act_on_someone_elses_match(): void
    {
        $match    = $this->donation->matches()->first();
        $intruder = $this->user('user', 'intrus@example.test');
        $otherAssoc = $this->user('association', 'autre@example.test');

        $this->actingAs($intruder)->post(route('matches.choose', [$this->donation, $match]))->assertForbidden();
        $this->actingAs($intruder)->get(route('donations.show', $this->donation))->assertForbidden();

        $this->actingAs($this->donor)->post(route('matches.choose', [$this->donation, $match]));
        $this->actingAs($otherAssoc)->post(route('association.requests.accept', $match), [
            'meeting_type' => 'depot', 'meeting_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertForbidden();
    }

    public function test_only_admin_area_is_restricted_by_role(): void
    {
        $this->actingAs($this->donor)->get(route('admin.associations.index'))->assertForbidden();
        $this->actingAs($this->donor)->get(route('association.dashboard'))->assertForbidden();
        $this->actingAs($this->assocUser)->get(route('donations.index'))->assertForbidden();
    }

    public function test_pages_render(): void
    {
        $match = $this->donation->matches()->first();
        $admin = $this->user('admin', 'admin@example.test');

        $this->actingAs($this->donor)->get(route('donations.index'))->assertOk();
        $this->actingAs($this->donor)->get(route('donations.create'))->assertOk();
        $this->actingAs($this->donor)->get(route('donations.show', $this->donation))->assertOk()->assertSee('Associations recommandées');
        $this->actingAs($this->donor)->get(route('donations.edit', $this->donation))->assertOk();

        $this->actingAs($this->donor)->post(route('matches.choose', [$this->donation, $match]));

        $this->actingAs($this->assocUser)->get(route('association.dashboard'))->assertOk()->assertSee('Demandes à traiter');
        $this->actingAs($this->assocUser)->get(route('association.profile.edit'))->assertOk();
        $this->actingAs($this->assocUser)->get(route('association.needs.index'))->assertOk();
        $this->actingAs($this->assocUser)->get(route('association.requests.show', $match))->assertOk()->assertSee('Répondre à la demande');

        // Anonymat du donateur avant acceptation.
        $this->actingAs($this->assocUser)->get(route('association.requests.show', $match))
            ->assertDontSee('donateur@example.test');

        $this->actingAs($admin)->get(route('admin.associations.index'))->assertOk()->assertSee('Association test');
    }

    private function user(string $role, string $email): User
    {
        return User::forceCreate([
            'name' => ucfirst($role), 'email' => $email, 'password' => bcrypt('password'), 'role' => $role,
        ]);
    }
}
