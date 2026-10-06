<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transformation extends Model
{
    use HasFactory;

    public const TYPES = ['Sac', 'Coussin', 'Accessoire', 'Décoration', 'Vêtement', 'Autre'];

    public const STATUTS = [
        'idee'     => 'Idée',
        'en_cours' => 'En cours',
        'termine'  => 'Terminé',
    ];

    public const DIFFICULTES = [
        'facile' => 'Facile',
        'moyen'  => 'Moyen',
        'avance' => 'Avancé',
    ];

    protected $fillable = [
        'user_id',
        'depot_id',
        'titre',
        'type_projet',
        'description',
        'difficulte',
        'duree_estimee',
        'materiaux',
        'genere_par_ia',
        'statut',
    ];

    protected $casts = [
        'materiaux'     => 'array',
        'genere_par_ia' => 'boolean',
    ];

    // Relation 1-N : un utilisateur a plusieurs projets d'upcycling
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relation 1-N : un dépôt (vêtement) peut être transformé en plusieurs projets
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? ucfirst((string) $this->statut);
    }

    public function getDifficulteLabelAttribute(): ?string
    {
        return self::DIFFICULTES[$this->difficulte] ?? null;
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'termine'  => 'badge-success',
            'en_cours' => 'badge-warning',
            default    => 'badge-info',
        };
    }
}
