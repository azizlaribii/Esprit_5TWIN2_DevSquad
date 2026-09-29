<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleMarketplace extends Model
{
    use HasFactory;

    protected $table = 'articles_marketplace';

    protected $fillable = [
        'user_id',
        'titre',
        'description',
        'categorie',
        'marque',
        'taille',
        'genre',
        'etat',
        'type',
        'prix',
        'article_echange',
        'image_url',
        'statut',
        'ai_score',
        'ai_prix_min',
        'ai_prix_max',
        'ai_classification',
        'note_vendeur',
        'vues',
    ];

    protected $appends = ['is_favori'];

    // Relation: le vendeur (User)
    public function vendeur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation: les favoris
    public function favoris()
    {
        return $this->hasMany(FavoriMarketplace::class, 'article_id');
    }

    // Relation: les demandes
    public function demandes()
    {
        return $this->hasMany(DemandeMarketplace::class, 'article_id');
    }

    // Relation: les évaluations
    public function evaluations()
    {
        return $this->hasMany(EvaluationVendeur::class, 'article_id');
    }

    // Attribut calculé: est-ce un favori de l'utilisateur courant ?
    public function getIsFavoriAttribute(): bool
    {
        if (!auth()->check()) return false;
        return $this->favoris()->where('user_id', auth()->id())->exists();
    }

    /**
     * IA : Calcul du score de compatibilité avec un utilisateur
     * Basé sur : catégories consultées, taille préférée, historique achats
     */
    public function calculateAiScore(User $user): int
    {
        $score = 60; // Base score

        // Bonus taille
        $preferences = $user->preferences ?? [];
        if (!empty($preferences['taille']) && in_array($this->taille, (array)$preferences['taille'])) {
            $score += 20;
        }

        // Bonus catégorie
        if (!empty($preferences['categories']) && in_array($this->categorie, (array)$preferences['categories'])) {
            $score += 15;
        }

        // Bonus état (articles en bon état mieux notés)
        $etatBonus = ['Neuf avec étiquette' => 5, 'Très bon état' => 3, 'Bon état' => 1, 'État correct' => 0];
        $score += $etatBonus[$this->etat] ?? 0;

        return min(100, $score);
    }

    /**
     * IA : Estimation automatique du prix
     * Basée sur : catégorie, marque, état, type
     */
    public static function estimerPrixIA(string $categorie, string $etat, ?string $marque = null): array
    {
        $basePrice = [
            'Vestes & Manteaux' => 45,
            'Robes & Jupes' => 30,
            'Jeans & Pantalons' => 25,
            'Chaussures' => 35,
            'T-Shirts & Tops' => 15,
            'Sportswear' => 20,
            'Accessoires' => 12,
            'Lingerie' => 10,
        ][$categorie] ?? 20;

        $etatMultiplier = [
            'Neuf avec étiquette' => 1.5,
            'Très bon état' => 1.2,
            'Bon état' => 1.0,
            'État correct' => 0.6,
        ][$etat] ?? 1.0;

        // Bonus marque premium
        $premiumBrands = ['Zara', 'H&M', 'Nike', 'Adidas', 'Levi\'s', 'Ralph Lauren', 'Gucci', 'Louis Vuitton'];
        $brandBonus = $marque && in_array($marque, $premiumBrands) ? 1.3 : 1.0;

        $estimated = $basePrice * $etatMultiplier * $brandBonus;

        return [
            'min' => round($estimated * 0.8, 2),
            'max' => round($estimated * 1.2, 2),
        ];
    }

    /**
     * IA : Classification automatique depuis description
     */
    public static function classifierIA(string $titre, string $description): array
    {
        // Moteur de classification basé sur mots-clés
        $text = strtolower($titre . ' ' . $description);

        $categories = [
            'Jeans & Pantalons' => ['jean', 'jeans', 'pantalon', 'slim', 'cargo', 'chino'],
            'T-Shirts & Tops' => ['t-shirt', 'tshirt', 'top', 'tank', 'polo', 'chemise'],
            'Vestes & Manteaux' => ['veste', 'manteau', 'blazer', 'parka', 'trench', 'bomber'],
            'Robes & Jupes' => ['robe', 'jupe', 'dress', 'skirt'],
            'Chaussures' => ['chaussure', 'basket', 'sneaker', 'botte', 'sandale', 'escarpin'],
            'Sportswear' => ['sport', 'jogging', 'legging', 'running'],
        ];

        $genres = [
            'Homme' => ['homme', 'man', 'men', 'boy'],
            'Femme' => ['femme', 'woman', 'women', 'girl', 'fille'],
            'Enfant' => ['enfant', 'enfants', 'bébé', 'baby', 'kid'],
        ];

        $detectedCategory = 'T-Shirts & Tops';
        foreach ($categories as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) { $detectedCategory = $cat; break 2; }
            }
        }

        $detectedGenre = 'Unisexe';
        foreach ($genres as $genre => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) { $detectedGenre = $genre; break 2; }
            }
        }

        return ['categorie' => $detectedCategory, 'genre' => $detectedGenre];
    }

    /**
     * IA : Détection des annonces similaires
     */
    public function getSimilarArticles(int $limit = 4)
    {
        return static::where('id', '!=', $this->id)
            ->where('statut', 'disponible')
            ->where(function($q) {
                $q->where('categorie', $this->categorie)
                  ->orWhere('taille', $this->taille)
                  ->orWhere('genre', $this->genre);
            })
            ->orderByRaw('ABS(prix - ?) ASC', [$this->prix ?? 0])
            ->limit($limit)
            ->get();
    }

    // Scope: articles disponibles
    public function scopeDisponible($query)
    {
        return $query->where('statut', 'disponible');
    }
}
