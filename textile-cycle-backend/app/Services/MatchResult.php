<?php

namespace App\Services;

use App\Models\Association;
use App\Models\AssociationNeed;

/**
 * Résultat du scoring d'une association pour un don.
 */
final class MatchResult
{
    /**
     * @param  array<string,float>  $breakdown  sous-scores 0-100 par critère
     * @param  list<string>         $reasons    explications lisibles, générées par l'algorithme
     */
    public function __construct(
        public readonly Association $association,
        public readonly ?AssociationNeed $need,
        public readonly float $score,
        public readonly array $breakdown,
        public readonly array $reasons,
        public readonly ?float $distanceKm,
    ) {
    }
}
