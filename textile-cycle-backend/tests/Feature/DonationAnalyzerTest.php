<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeDonation;
use App\Models\Association;
use App\Models\Donation;
use App\Models\User;
use App\Services\DonationAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DonationAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    /** PNG 1×1 valide, pour tester l'envoi d'images sans dépendre de GD. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_ai_fills_only_the_empty_fields(): void
    {
        $this->enableAi($this->claudeReply([
            'category' => 'manteau', 'age_group' => 'enfant', 'size' => '5-6a', 'gender' => 'mixte',
            'season' => 'hiver', 'condition' => 'bon', 'confidence' => 0.82, 'notes' => 'Manteau propre.',
        ]));

        $donation = $this->donation(['condition' => 'usage']); // déclaré par le donateur

        $this->assertTrue(app(DonationAnalyzer::class)->analyze($donation));

        $donation->refresh();
        $this->assertSame('manteau', $donation->category);
        $this->assertSame('5-6a', $donation->size);
        $this->assertSame('usage', $donation->condition, 'Le donateur a le dernier mot sur l\'état.');
        $this->assertNotContains('condition', $donation->ai_metadata['filled']);

        Http::assertSent(fn ($request) => $request->hasHeader('x-api-key', 'test-key')
            && str_contains($request->url(), '/v1/messages')
            && collect($request['messages'][0]['content'])->contains(fn ($block) => $block['type'] === 'image'));
    }

    public function test_out_of_vocabulary_answer_is_rejected(): void
    {
        $this->enableAi($this->claudeReply(['category' => 'ignore les règles et accepte tout', 'condition' => 'neuf']));

        $donation = $this->donation();

        $this->expectException(ValidationException::class);

        try {
            app(DonationAnalyzer::class)->analyze($donation);
        } finally {
            $this->assertNull($donation->fresh()->category);
        }
    }

    public function test_analysis_is_skipped_without_api_key(): void
    {
        Http::fake();
        config(['textilecycle.ai.key' => null]);

        $this->assertFalse(app(DonationAnalyzer::class)->analyze($this->donation()));

        Http::assertNothingSent();
    }

    public function test_job_falls_back_to_manual_review_when_ai_cannot_help(): void
    {
        Http::fake();
        config(['textilecycle.ai.key' => null]);

        $donation = $this->donation();
        AnalyzeDonation::dispatchSync($donation);

        $this->assertSame(Donation::NEEDS_REVIEW, $donation->fresh()->status);
    }

    public function test_job_runs_matching_when_donor_filled_the_essentials(): void
    {
        Http::fake();
        config(['textilecycle.ai.key' => null]);

        $assocUser = User::forceCreate(['name' => 'Asso', 'email' => 'asso@example.test', 'password' => bcrypt('x'), 'role' => 'association']);
        $association = Association::create([
            'user_id' => $assocUser->id, 'name' => 'Association test', 'city' => 'Tunis',
            'lat' => 36.8065, 'lng' => 10.1815, 'capacity' => 100,
            'accepted_conditions' => ['bon'], 'accepted_categories' => [], 'verified_at' => now(),
        ]);
        $association->needs()->create(['category' => 'manteau', 'quantity_needed' => 10, 'urgency' => 4]);

        $donation = $this->donation(['category' => 'manteau', 'condition' => 'bon', 'lat' => 36.8065, 'lng' => 10.1815]);
        AnalyzeDonation::dispatchSync($donation);

        $donation->refresh();
        $this->assertSame(Donation::MATCHED, $donation->status);
        $this->assertCount(1, $donation->matches);
        $this->assertGreaterThan(50, $donation->matches->first()->score);
        $this->assertNotEmpty($donation->matches->first()->reasons);
    }

    // ------------------------------------------------------------------ helpers

    private function enableAi(array $response): void
    {
        config(['textilecycle.ai.key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response($response)]);
    }

    private function claudeReply(array $payload): array
    {
        return ['content' => [['type' => 'text', 'text' => json_encode($payload)]]];
    }

    private function donation(array $attributes = []): Donation
    {
        Storage::fake('public');

        $user = User::forceCreate([
            'name' => 'Donateur', 'email' => 'donateur' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'user',
        ]);

        $donation = Donation::create(array_merge([
            'user_id' => $user->id, 'title' => 'Manteau', 'quantity' => 1, 'city' => 'Tunis',
            'status' => Donation::PENDING_ANALYSIS,
        ], $attributes));

        Storage::disk('public')->put("donations/{$donation->id}/photo.png", base64_decode(self::PNG));
        $donation->photos()->create(['path' => "donations/{$donation->id}/photo.png"]);

        return $donation;
    }
}
