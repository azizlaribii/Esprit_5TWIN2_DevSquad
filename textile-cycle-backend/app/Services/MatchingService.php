<?php

namespace App\Services;

use App\Models\Association;
use App\Models\AssociationNeed;
use App\Models\Donation;
use App\Models\DonationMatch;
use App\Support\Textile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Matching déterministe entre un don et les associations.
 *
 * 1. Filtres éliminatoires : association vérifiée, état et catégorie acceptés,
 *    capacité restante, distance maximale, association n'ayant pas déjà refusé ce don.
 * 2. Score pondéré sur 100 (poids dans config/textilecycle.php).
 *
 * L'IA n'intervient pas ici : le score est reproductible et testable.
 */
class MatchingService
{
    /** Points du sous-score « besoin » (total 100). */
    private const NEED_POINTS = [
        'category' => 45,
        'age'      => 25,
        'size'     => 10,
        'gender'   => 10,
        'season'   => 10,
    ];

    /**
     * Calcule les meilleures associations, les enregistre et met à jour le statut du don.
     *
     * @return Collection<int, DonationMatch>
     */
    public function run(Donation $donation): Collection
    {
        $results = $this->rank($donation, (int) config('textilecycle.suggestions', 3));
        $matches = $this->persist($donation, $results);

        // Ne pas écraser un don déjà demandé ou accepté.
        if (in_array($donation->status, [
            Donation::PENDING_ANALYSIS, Donation::NEEDS_REVIEW, Donation::MATCHED, Donation::NO_MATCH,
        ], true)) {
            $donation->update([
                'status' => $matches->isEmpty() ? Donation::NO_MATCH : Donation::MATCHED,
            ]);
        }

        return $matches;
    }

    /**
     * @return Collection<int, MatchResult>
     */
    public function rank(Donation $donation, int $limit = 3): Collection
    {
        $excluded = $donation->exists
            ? $donation->matches()->where('status', DonationMatch::REJECTED)->pluck('association_id')->all()
            : [];

        return $this->candidates()
            ->reject(fn (Association $a) => in_array($a->id, $excluded, true))
            ->map(fn (Association $a) => $this->score($donation, $a))
            ->filter()
            ->sortByDesc(fn (MatchResult $r) => $r->score)
            ->take($limit)
            ->values();
    }

    /**
     * Score d'une association pour un don, ou null si elle est éliminée.
     * Ne fait aucune requête si les relations/compteurs sont déjà chargés.
     */
    public function score(Donation $donation, Association $association): ?MatchResult
    {
        if (! $this->isEligible($donation, $association)) {
            return null;
        }

        $maxKm    = max(1.0, (float) config('textilecycle.max_distance_km', 50));
        $distance = $this->distanceKm($donation, $association);

        if ($distance !== null && $distance > $maxKm) {
            return null;
        }

        [$need, $needFit] = $this->bestNeed($donation, $association);

        $breakdown = [
            'need'      => $needFit,
            'urgency'   => $need ? (($need->urgency - 1) / 4) * 100 : 0.0,
            'proximity' => $distance === null ? 50.0 : max(0.0, 100 * (1 - $distance / $maxKm)),
            'condition' => (float) (Textile::CONDITION_VALUE[$donation->condition] ?? 50),
            'capacity'  => $this->capacityScore($association),
        ];

        $weights = (array) config('textilecycle.weights', []);
        $total   = array_sum($weights) ?: 100;
        $score   = 0.0;

        foreach ($breakdown as $criterion => $value) {
            $score += $value * (($weights[$criterion] ?? 0) / $total);
        }

        return new MatchResult(
            association: $association,
            need: $need,
            score: round($score, 1),
            breakdown: array_map(fn ($v) => round($v, 1), $breakdown),
            reasons: $this->reasons($donation, $association, $need, $distance),
            distanceKm: $distance === null ? null : round($distance, 1),
        );
    }

    /** Filtres éliminatoires. */
    public function isEligible(Donation $donation, Association $association): bool
    {
        if (! $association->isVerified()) {
            return false;
        }

        $conditions = $association->accepted_conditions ?? [];
        if ($conditions !== [] && ! in_array($donation->condition, $conditions, true)) {
            return false;
        }

        $categories = $association->accepted_categories ?? [];
        if ($categories !== [] && ! in_array($donation->category, $categories, true)) {
            return false;
        }

        $pending = (int) ($association->pending_quantity ?? 0);
        if ($pending + max(1, (int) $donation->quantity) > max(1, (int) $association->capacity)) {
            return false;
        }

        return true;
    }

    /**
     * Meilleur besoin actif de l'association pour ce don.
     *
     * @return array{0: ?AssociationNeed, 1: float}
     */
    public function bestNeed(Donation $donation, Association $association): array
    {
        $best    = null;
        $bestFit = 0.0;

        foreach ($association->activeNeeds as $need) {
            $fit = $this->needFit($donation, $need);
            if ($fit > $bestFit) {
                $best    = $need;
                $bestFit = $fit;
            }
        }

        return [$best, $bestFit];
    }

    /**
     * Adéquation don ↔ besoin, de 0 à 100. Une catégorie différente = 0 (le besoin ne s'applique pas).
     */
    public function needFit(Donation $donation, AssociationNeed $need): float
    {
        if ($need->category !== $donation->category) {
            return 0.0;
        }

        $p = self::NEED_POINTS;
        $points = $p['category'];

        // Tranche d'âge : besoin sans préférence = OK ; don sans info = moitié des points.
        if ($need->age_group === null) {
            $points += $p['age'];
        } elseif ($donation->age_group === null) {
            $points += $p['age'] / 2;
        } elseif ($need->age_group === $donation->age_group) {
            $points += $p['age'];
        }

        // Taille : identique = tout, voisine = moitié.
        if ($need->size === null) {
            $points += $p['size'];
        } elseif ($donation->size === null) {
            $points += $p['size'] / 2;
        } elseif (strcasecmp($need->size, $donation->size) === 0) {
            $points += $p['size'];
        } else {
            $a = Textile::sizeIndex($need->size);
            $b = Textile::sizeIndex($donation->size);
            if ($a !== null && $b !== null && abs($a - $b) === 1) {
                $points += $p['size'] / 2;
            }
        }

        // Genre : « mixte » côté besoin ou côté don convient à tout.
        if ($this->isWildcard($need->gender, 'mixte')
            || $this->isWildcard($donation->gender, 'mixte')
            || $need->gender === $donation->gender) {
            $points += $p['gender'];
        }

        // Saison : « toutes » convient à tout ; la mi-saison est un compromis.
        if ($this->isWildcard($need->season, 'toutes')
            || $this->isWildcard($donation->season, 'toutes')
            || $need->season === $donation->season) {
            $points += $p['season'];
        } elseif ($need->season === 'mi_saison' || $donation->season === 'mi_saison') {
            $points += $p['season'] / 2;
        }

        return (float) $points;
    }

    /** Distance à vol d'oiseau (haversine) en km, ou null si une position manque. */
    public function distanceKm(Donation $donation, Association $association): ?float
    {
        if ($donation->lat === null || $donation->lng === null
            || $association->lat === null || $association->lng === null) {
            return null;
        }

        $earth = 6371.0;
        $dLat  = deg2rad($association->lat - $donation->lat);
        $dLng  = deg2rad($association->lng - $donation->lng);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($donation->lat)) * cos(deg2rad($association->lat)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Capacité restante (60 %) et taux d'acceptation passé (40 %).
     * Avec moins de 3 réponses historiques, le taux est neutre (50).
     */
    public function capacityScore(Association $association): float
    {
        $capacity = max(1, (int) $association->capacity);
        $pending  = (int) ($association->pending_quantity ?? 0);
        $free     = max(0.0, 1 - $pending / $capacity) * 100;

        $accepted  = (int) ($association->accepted_count ?? 0);
        $rejected  = (int) ($association->rejected_count ?? 0);
        $responses = $accepted + $rejected;
        $rate      = $responses >= 3 ? ($accepted / $responses) * 100 : 50.0;

        return 0.6 * $free + 0.4 * $rate;
    }

    /** Associations vérifiées, avec besoins actifs et compteurs d'historique. */
    protected function candidates(): Collection
    {
        return Association::query()
            ->verified()
            ->with('activeNeeds')
            ->withSum(['matches as pending_quantity' => fn ($q) => $q->where('status', DonationMatch::ACCEPTED)], 'quantity')
            ->withCount([
                'matches as accepted_count' => fn ($q) => $q->whereIn('status', [DonationMatch::ACCEPTED, DonationMatch::COMPLETED]),
                'matches as rejected_count' => fn ($q) => $q->where('status', DonationMatch::REJECTED),
            ])
            ->get();
    }

    /**
     * Enregistre les suggestions. Les demandes déjà envoyées, acceptées ou refusées
     * gardent leur statut ; seules les suggestions périmées sont supprimées.
     *
     * @param  Collection<int, MatchResult>  $results
     * @return Collection<int, DonationMatch>
     */
    public function persist(Donation $donation, Collection $results): Collection
    {
        return DB::transaction(function () use ($donation, $results) {
            $donation->matches()
                ->where('status', DonationMatch::SUGGESTED)
                ->whereNotIn('association_id', $results->map(fn (MatchResult $r) => $r->association->id))
                ->delete();

            $results->each(function (MatchResult $r) use ($donation) {
                $donation->matches()->updateOrCreate(
                    ['association_id' => $r->association->id],
                    [
                        'association_need_id' => $r->need?->id,
                        'score'               => $r->score,
                        'reasons'             => $r->reasons,
                        'quantity'            => $donation->quantity,
                    ]
                );
            });

            return $donation->matches()
                ->where('status', DonationMatch::SUGGESTED)
                ->with('association')
                ->orderByDesc('score')
                ->get();
        });
    }

    /** @return list<string> */
    protected function reasons(Donation $donation, Association $association, ?AssociationNeed $need, ?float $distance): array
    {
        $reasons = [];

        if ($need) {
            $target = Textile::label('categories', $need->category);
            if ($need->age_group) {
                $target .= ' · ' . Textile::label('age_groups', $need->age_group);
            }
            if ($need->size) {
                $target .= ' · taille ' . $need->size;
            }
            $reasons[] = sprintf('Recherche activement : %s (urgence %d/5)', $target, $need->urgency);
        } else {
            $reasons[] = 'Accepte cette catégorie de vêtements (pas de besoin déclaré pour le moment)';
        }

        if ($distance !== null) {
            $reasons[] = $distance < 1
                ? 'À moins d\'1 km'
                : sprintf('À %s km', number_format($distance, 1, ',', ' '));
        }

        $reasons[] = sprintf('Accepte l\'état « %s »', Textile::label('conditions', $donation->condition));

        $accepted  = (int) ($association->accepted_count ?? 0);
        $rejected  = (int) ($association->rejected_count ?? 0);
        $responses = $accepted + $rejected;
        if ($responses >= 3 && $accepted / $responses >= 0.8) {
            $reasons[] = sprintf('Association fiable : %d %% des demandes acceptées', round($accepted / $responses * 100));
        }

        return $reasons;
    }

    private function isWildcard(?string $value, string $wildcard): bool
    {
        return $value === null || $value === '' || $value === $wildcard;
    }
}
