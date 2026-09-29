<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'user_id' => 1, 'titre' => 'Jean slim Levi\'s 501 bleu indigo – Taille 40',
                'description' => 'Jean Levi\'s en excellent état, porté seulement 3 fois. Couleur bleu indigo intense, coupe slim flatteuse. Aucun défaut visible. Lavage à froid recommandé.',
                'categorie' => 'Jeans & Pantalons', 'marque' => 'Levi\'s', 'taille' => '40', 'genre' => 'Femme',
                'etat' => 'Très bon état', 'type' => 'vente', 'prix' => 28.00,
                'statut' => 'disponible', 'ai_score' => 94, 'ai_prix_min' => 24.00, 'ai_prix_max' => 35.00,
                'ai_classification' => '{"categorie":"Jeans & Pantalons","genre":"Femme"}',
                'note_vendeur' => 4.8, 'vues' => 34, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'user_id' => 2, 'titre' => 'Veste blazer Zara – Beige – Taille M',
                'description' => 'Blazer tendance Zara, coupe oversize, couleur beige sable. Parfait pour un look professionnel ou casual chic. Doublure intérieure légère.',
                'categorie' => 'Vestes & Manteaux', 'marque' => 'Zara', 'taille' => 'M', 'genre' => 'Femme',
                'etat' => 'Neuf avec étiquette', 'type' => 'vente', 'prix' => 45.00,
                'statut' => 'disponible', 'ai_score' => 91, 'ai_prix_min' => 38.00, 'ai_prix_max' => 55.00,
                'ai_classification' => '{"categorie":"Vestes & Manteaux","genre":"Femme"}',
                'note_vendeur' => 4.5, 'vues' => 67, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'user_id' => 3, 'titre' => 'Lot 3 T-shirts Nike Dri-FIT – Taille L',
                'description' => 'Lot de 3 t-shirts sport Nike Dri-FIT, couleurs : gris, blanc et noir. Parfaits pour running ou salle. Très bon état général, légère trace de lavage sur le gris.',
                'categorie' => 'Sportswear', 'marque' => 'Nike', 'taille' => 'L', 'genre' => 'Homme',
                'etat' => 'Bon état', 'type' => 'vente', 'prix' => 22.00,
                'statut' => 'disponible', 'ai_score' => 87, 'ai_prix_min' => 18.00, 'ai_prix_max' => 28.00,
                'ai_classification' => '{"categorie":"Sportswear","genre":"Homme"}',
                'note_vendeur' => 4.2, 'vues' => 45, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'user_id' => 1, 'titre' => 'Robe fleurie H&M – Taille S – Don',
                'description' => 'Belle robe mi-longue fleurie H&M, couleurs vives, parfaite pour l\'été. Je n\'en ai plus l\'utilité, préfère la donner plutôt que la jeter. Très bon état.',
                'categorie' => 'Robes & Jupes', 'marque' => 'H&M', 'taille' => 'S', 'genre' => 'Femme',
                'etat' => 'Très bon état', 'type' => 'don', 'prix' => null,
                'statut' => 'disponible', 'ai_score' => 88, 'ai_prix_min' => 10.00, 'ai_prix_max' => 20.00,
                'ai_classification' => '{"categorie":"Robes & Jupes","genre":"Femme"}',
                'note_vendeur' => 4.8, 'vues' => 23, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'user_id' => 2, 'titre' => 'Manteau laine camel – Taille 42 – Échange',
                'description' => 'Magnifique manteau en laine camel, coupe classique longue. Cherche à échanger contre une veste légère printemps ou un manteau court. Taille 40-42.',
                'categorie' => 'Vestes & Manteaux', 'marque' => null, 'taille' => '42', 'genre' => 'Femme',
                'etat' => 'Bon état', 'type' => 'echange', 'prix' => null,
                'article_echange' => 'Veste légère printemps taille 40-42',
                'statut' => 'disponible', 'ai_score' => 82, 'ai_prix_min' => 35.00, 'ai_prix_max' => 55.00,
                'ai_classification' => '{"categorie":"Vestes & Manteaux","genre":"Femme"}',
                'note_vendeur' => 4.5, 'vues' => 56, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'user_id' => 3, 'titre' => 'Sneakers Adidas Stan Smith – Taille 42',
                'description' => 'Paire de Stan Smith blanches, très peu portées. Semelles comme neuves. Idéales pour compléter n\'importe quel look casual.',
                'categorie' => 'Chaussures', 'marque' => 'Adidas', 'taille' => '42', 'genre' => 'Homme',
                'etat' => 'Très bon état', 'type' => 'vente', 'prix' => 38.00,
                'statut' => 'disponible', 'ai_score' => 93, 'ai_prix_min' => 32.00, 'ai_prix_max' => 48.00,
                'ai_classification' => '{"categorie":"Chaussures","genre":"Homme"}',
                'note_vendeur' => 4.2, 'vues' => 89, 'created_at' => now(), 'updated_at' => now(),
            ],
        ];

        foreach ($articles as $article) {
            DB::table('articles_marketplace')->insertOrIgnore($article);
        }
    }
}
