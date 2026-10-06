<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\HasTextileRole;
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasTextileRole;
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    // Relations existantes
    public function depots()
    {
        return $this->hasMany(Depot::class);
    }
        public function transformations()
    {
        return $this->hasMany(Transformation::class);
    }
    // Relations Marketplace (1-N : un utilisateur a plusieurs articles)
    public function articlesMarketplace()
    {
        return $this->hasMany(ArticleMarketplace::class);
    }

    // N-N : un utilisateur a plusieurs favoris via la table pivot
    public function favorisMarketplace()
    {
        return $this->hasMany(FavoriMarketplace::class);
    }

    // Demandes d'achat faites par cet utilisateur
    public function demandesAchat()
    {
        return $this->hasMany(DemandeMarketplace::class, 'acheteur_id');
    }

    // Évaluations données
    public function evaluationsDonnees()
    {
        return $this->hasMany(EvaluationVendeur::class, 'evaluateur_id');
    }

    // Évaluations reçues (en tant que vendeur)
    public function evaluationsRecues()
    {
        return $this->hasMany(EvaluationVendeur::class, 'vendeur_id');
    }

    // Note moyenne en tant que vendeur
    public function getNoteMoyenneAttribute(): float
    {
        $evals = $this->evaluationsRecues;
        return $evals->count() > 0 ? round($evals->avg('note'), 1) : 4.0;
    }
}
