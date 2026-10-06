<?php

namespace Database\Factories;

use App\Models\ArticleMarketplace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour l'entité ArticleMarketplace
 * Relation : ArticleMarketplace belongsTo User (vendeur)
 */
class ArticleMarketplaceFactory extends Factory
{
    protected $model = ArticleMarketplace::class;

    public function definition(): array
    {
        $categories = [
            'Jeans & Pantalons', 'T-Shirts & Tops', 'Vestes & Manteaux',
            'Robes & Jupes', 'Chaussures', 'Sportswear', 'Accessoires',
        ];

        $etats = ['Neuf avec étiquette', 'Très bon état', 'Bon état', 'État correct'];
        $genres = ['Homme', 'Femme', 'Enfant', 'Unisexe'];
        $tailles = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '38', '40', '42', '44'];
        $marques = ['Zara', 'H&M', 'Nike', 'Adidas', "Levi's", 'Uniqlo', 'Mango', 'Pull&Bear'];
        $types = ['vente', 'echange'];

        $categorie = $this->faker->randomElement($categories);
        $etat      = $this->faker->randomElement($etats);

        return [
            // Relation 1-N : un article appartient à un vendeur (User)
            'user_id'           => User::factory()->roleUser(),
            'titre'             => $this->faker->words(4, true) . ' - ' . $categorie,
            'description'       => $this->faker->paragraph(3),
            'categorie'         => $categorie,
            'marque'            => $this->faker->randomElement($marques),
            'taille'            => $this->faker->randomElement($tailles),
            'genre'             => $this->faker->randomElement($genres),
            'etat'              => $etat,
            'type'              => $this->faker->randomElement($types),
            'prix'              => $this->faker->randomFloat(2, 5, 150),
            'article_echange'   => null,
            'image_url'         => null,
            'statut'            => 'disponible',
            'ai_score'          => $this->faker->numberBetween(50, 100),
            'vues'              => $this->faker->numberBetween(0, 500),
        ];
    }

    /** État : article de type vente */
    public function typeVente(): static
    {
        return $this->state(fn () => [
            'type'  => 'vente',
            'prix'  => $this->faker->randomFloat(2, 5, 200),
        ]);
    }

    /** État : article de type échange */
    public function typeEchange(): static
    {
        return $this->state(fn () => [
            'type'            => 'echange',
            'prix'            => null,
            'article_echange' => 'Recherche vêtement équivalent en bon état',
        ]);
    }

    /** État : article déjà vendu/réservé */
    public function vendu(): static
    {
        return $this->state(fn () => ['statut' => 'vendu']);
    }
}
