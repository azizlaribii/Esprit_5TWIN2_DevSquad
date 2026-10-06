<?php

namespace Database\Factories;

use App\Models\EvaluationVendeur;
use App\Models\ArticleMarketplace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour EvaluationVendeur
 * Relations :
 *   - EvaluationVendeur belongsTo User (évaluateur)
 *   - EvaluationVendeur belongsTo User (vendeur)
 *   - EvaluationVendeur belongsTo ArticleMarketplace
 */
class EvaluationVendeurFactory extends Factory
{
    protected $model = EvaluationVendeur::class;

    public function definition(): array
    {
        return [
            // Relation : l'évaluateur (acheteur)
            'evaluateur_id' => User::factory()->roleUser(),
            // Relation : le vendeur évalué
            'vendeur_id'    => User::factory()->roleUser(),
            // Relation : l'article concerné
            'article_id'    => ArticleMarketplace::factory(),
            'note'          => $this->faker->numberBetween(1, 5),
            'commentaire'   => $this->faker->sentence(10),
        ];
    }
}
