<?php

namespace Database\Factories;

use App\Models\ArticleMarketplace;
use App\Models\DemandeMarketplace;
use App\Models\User;
use Database\Factories\AssociationFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour les demandes d'achat/échange du Marketplace.
 *
 * États :
 *  - enAttente()   → statut = 'en_attente'
 *  - acceptee()    → statut = 'acceptee'
 *  - refusee()     → statut = 'refusee'
 *  - finalisee()   → statut = 'finalisee'
 *
 * @extends Factory<DemandeMarketplace>
 */
class DemandeMarketplaceFactory extends Factory
{
    protected $model = DemandeMarketplace::class;

    private const MESSAGES = [
        'Bonjour, cet article m\'intéresse beaucoup, est-il encore disponible ?',
        'Je suis très intéressé(e). Peut-on organiser un rendez-vous ?',
        "Bonjour, l'article correspond exactement à ce que je cherche !",
        'Est-il possible d\'avoir plus de photos avant de confirmer ?',
        'Je suis prêt(e) à acheter dès aujourd\'hui si la transaction peut se faire rapidement.',
        'Pouvez-vous me donner plus d\'informations sur l\'état de l\'article ?',
        null,  // Certaines demandes n'ont pas de message
    ];

    public function definition(): array
    {
        return [
            // Par défaut, crée un article et un acheteur séparés
            'article_id'  => fn () => ArticleMarketplace::factory()->create()->id,
            'acheteur_id' => fn () => AssociationFactory::createUser('acheteur'),
            'statut'      => 'en_attente',
            'message'     => fake()->randomElement(self::MESSAGES),
        ];
    }


    // ---------------------------------------------------------------
    //  États : statut de la demande
    // ---------------------------------------------------------------

    public function enAttente(): static
    {
        return $this->state(['statut' => 'en_attente']);
    }

    public function acceptee(): static
    {
        return $this->state(['statut' => 'acceptee']);
    }

    public function refusee(): static
    {
        return $this->state(['statut' => 'refusee']);
    }

    public function finalisee(): static
    {
        return $this->state(['statut' => 'finalisee']);
    }

    // ---------------------------------------------------------------
    //  Helpers de relation
    // ---------------------------------------------------------------

    /** Associe la demande à un article existant. */
    public function pourArticle(ArticleMarketplace $article): static
    {
        return $this->state(['article_id' => $article->id]);
    }

    /** Associe la demande à un acheteur existant. */
    public function parAcheteur(User $acheteur): static
    {
        return $this->state(['acheteur_id' => $acheteur->id]);
    }
}
