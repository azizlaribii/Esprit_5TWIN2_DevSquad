<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    use HasFactory;

    public const STATUTS = [
        'en_attente' => 'En attente',
        'valide'     => 'Validé',
        'refuse'     => 'Refusé',
        'traite'     => 'Traité',
    ];

    protected $fillable = [
        'user_id', 'categorie', 'quantite', 'etat', 'statut', 'description', 'photo',
        'ai_type', 'ai_couleur', 'ai_etat', 'ai_matiere', 'ai_confiance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function valorisations()
    {
        return $this->hasMany(Valorisation::class);
    }

    /** Propriétaire du dépôt ou administrateur. */
    public function estGerablePar(?User $user): bool
    {
        return $user !== null && ($user->id === $this->user_id || $user->role === 'admin');
    }
}