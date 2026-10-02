<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\AssociationNeed;
use App\Models\Donation;
use App\Services\MatchingService;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Tests du scoring, sans base de données : les modèles sont construits en mémoire
 * et les relations/compteurs injectés à la main.
 */
class MatchingServiceTest extends TestCase
{
    private const TUNIS = [36.8065, 10.1815];
    private const LA_MARSA = [36.8782, 10.3247];
    private const SFAX = [34.7406, 10.7603];

    private MatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MatchingService();
    }

    public function test_distance_tunis_la_marsa_is_about_15_km(): void
    {
        $donation = $this->donation();
        $assoc    = $this->association(['lat' => self::LA_MARSA[0], 'lng' => self::LA_MARSA[1]]);

        $this->assertEqualsWithDelta(15.0, $this->service->distanceKm($donation, $assoc), 0.6);
    }

    public function test_unverified_association_is_excluded(): void
    {
        $this->assertNull($this->service->score($this->donation(), $this->association(['verified_at' => null])));
    }

    public function test_condition_not_accepted_is_excluded(): void
    {
        $donation = $this->donation(['condition' => 'usage']);
        $assoc    = $this->association(['accepted_conditions' => ['neuf', 'bon']]);

        $this->assertNull($this->service->score($donation, $assoc));
    }

    public function test_empty_accepted_lists_mean_everything_is_accepted(): void
    {
        $assoc = $this->association(['accepted_conditions' => [], 'accepted_categories' => []]);

        $this->assertNotNull($this->service->score($this->donation(['condition' => 'a_reparer']), $assoc));
    }

    public function test_category_not_accepted_is_excluded(): void
    {
        $assoc = $this->association(['accepted_categories' => ['pull', 'haut']]);

        $this->assertNull($this->service->score($this->donation(['category' => 'manteau']), $assoc));
    }

    public function test_association_too_far_is_excluded(): void
    {
        $assoc = $this->association(['lat' => self::SFAX[0], 'lng' => self::SFAX[1]]);

        $this->assertNull($this->service->score($this->donation(), $assoc));
    }

    public function test_association_without_remaining_capacity_is_excluded(): void
    {
        $assoc = $this->association(['capacity' => 100]);
        $assoc->setAttribute('pending_quantity', 99);

        // 99 en attente + 3 pièces > 100
        $this->assertNull($this->service->score($this->donation(['quantity' => 3]), $assoc));
    }

    public function test_perfect_need_scores_100_and_size_neighbour_scores_95(): void
    {
        $donation = $this->donation(['size' => '5-6a']);

        $perfect  = new AssociationNeed(['category' => 'manteau', 'age_group' => 'enfant', 'size' => '5-6a', 'gender' => 'mixte', 'season' => 'hiver', 'quantity_needed' => 5, 'urgency' => 3]);
        $neighbor = new AssociationNeed(['category' => 'manteau', 'age_group' => 'enfant', 'size' => '3-4a', 'gender' => 'mixte', 'season' => 'hiver', 'quantity_needed' => 5, 'urgency' => 3]);

        $this->assertSame(100.0, $this->service->needFit($donation, $perfect));
        $this->assertSame(95.0, $this->service->needFit($donation, $neighbor));
    }

    public function test_need_with_other_category_does_not_apply(): void
    {
        $need = new AssociationNeed(['category' => 'pull', 'quantity_needed' => 5, 'urgency' => 5]);

        $this->assertSame(0.0, $this->service->needFit($this->donation(['category' => 'manteau']), $need));
    }

    public function test_association_with_matching_need_beats_one_without(): void
    {
        $donation = $this->donation();

        $withNeed    = $this->association([], [$this->need()]);
        $withoutNeed = $this->association();

        $this->assertGreaterThan(
            $this->service->score($donation, $withoutNeed)->score,
            $this->service->score($donation, $withNeed)->score
        );
    }

    public function test_higher_urgency_gives_higher_score(): void
    {
        $donation = $this->donation();

        $urgent = $this->association([], [$this->need(['urgency' => 5])]);
        $calm   = $this->association([], [$this->need(['urgency' => 1])]);

        $this->assertGreaterThan(
            $this->service->score($donation, $calm)->score,
            $this->service->score($donation, $urgent)->score
        );
    }

    public function test_closer_association_gets_higher_score(): void
    {
        $donation = $this->donation();

        $near = $this->association(['lat' => self::TUNIS[0], 'lng' => self::TUNIS[1]], [$this->need()]);
        $far  = $this->association(['lat' => self::LA_MARSA[0], 'lng' => self::LA_MARSA[1]], [$this->need()]);

        $this->assertGreaterThan(
            $this->service->score($donation, $far)->score,
            $this->service->score($donation, $near)->score
        );
    }

    public function test_result_exposes_reasons_and_breakdown(): void
    {
        $result = $this->service->score($this->donation(), $this->association([], [$this->need(['urgency' => 4])]));

        $this->assertNotEmpty($result->reasons);
        $this->assertStringContainsString('urgence 4/5', $result->reasons[0]);
        $this->assertEqualsCanonicalizing(['need', 'urgency', 'proximity', 'condition', 'capacity'], array_keys($result->breakdown));
        $this->assertGreaterThanOrEqual(0, $result->score);
        $this->assertLessThanOrEqual(100, $result->score);
    }

    public function test_rank_sorts_by_score_and_respects_limit(): void
    {
        $best   = $this->association(['name' => 'Meilleure'], [$this->need(['urgency' => 5])]);
        $middle = $this->association(['name' => 'Moyenne'], [$this->need(['urgency' => 2])]);
        $worst  = $this->association(['name' => 'Sans besoin']);
        $out    = $this->association(['name' => 'Non vérifiée', 'verified_at' => null], [$this->need(['urgency' => 5])]);

        $service = new class(collect([$worst, $out, $middle, $best])) extends MatchingService {
            public function __construct(private Collection $fake)
            {
            }

            protected function candidates(): Collection
            {
                return $this->fake;
            }
        };

        $ranked = $service->rank($this->donation(), 2);

        $this->assertCount(2, $ranked);
        $this->assertSame(['Meilleure', 'Moyenne'], $ranked->map(fn ($r) => $r->association->name)->all());
    }

    // ------------------------------------------------------------------ helpers

    private function donation(array $attributes = []): Donation
    {
        return new Donation(array_merge([
            'category'  => 'manteau',
            'age_group' => 'enfant',
            'size'      => '5-6a',
            'gender'    => 'mixte',
            'season'    => 'hiver',
            'condition' => 'bon',
            'quantity'  => 3,
            'lat'       => self::TUNIS[0],
            'lng'       => self::TUNIS[1],
        ], $attributes));
    }

    /** @param list<AssociationNeed> $needs */
    private function association(array $attributes = [], array $needs = []): Association
    {
        $association = new Association(array_merge([
            'name'                => 'Association test',
            'city'                => 'Tunis',
            'lat'                 => self::TUNIS[0],
            'lng'                 => self::TUNIS[1],
            'accepted_conditions' => ['neuf', 'bon'],
            'accepted_categories' => [],
            'capacity'            => 100,
            'verified_at'         => now(),
        ], $attributes));

        $association->setRelation('activeNeeds', collect($needs));

        return $association;
    }

    private function need(array $attributes = []): AssociationNeed
    {
        return new AssociationNeed(array_merge([
            'category'        => 'manteau',
            'age_group'       => 'enfant',
            'size'            => null,
            'gender'          => 'mixte',
            'season'          => 'hiver',
            'quantity_needed' => 10,
            'urgency'         => 3,
        ], $attributes));
    }
}
