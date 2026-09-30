<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle fusionné : colonnes d'origine du projet (nom, adresse, beneficiaires_aides)
 * + colonnes du module « Don intelligent ».
 *
 * Le module lit et écrit `name` ; c'est un alias de la colonne `nom` du projet,
 * pour que les statistiques existantes continuent de fonctionner sans rien changer.
 */
class Association extends Model
{
    use HasFactory;

    protected $fillable = [
        // colonnes d'origine
        'nom',
        'adresse',
        'beneficiaires_aides',
        // module Don intelligent
        'name', // alias de `nom`
        'user_id', 'description', 'city', 'lat', 'lng',
        'accepted_conditions', 'accepted_categories', 'capacity',
        'opening_hours', 'phone', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_conditions' => 'array',
            'accepted_categories' => 'array',
            'verified_at'         => 'datetime',
            'lat'                 => 'float',
            'lng'                 => 'float',
            'capacity'            => 'integer',
        ];
    }

    // ---- alias name <-> nom ----------------------------------------------

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nom'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nom'] = $value;
    }

    // ---- relations --------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function needs(): HasMany
    {
        return $this->hasMany(AssociationNeed::class);
    }

    /** Besoins encore ouverts : quantité restante et non expirés. */
    public function activeNeeds(): HasMany
    {
        return $this->hasMany(AssociationNeed::class)
            ->where('quantity_needed', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', today());
            });
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DonationMatch::class);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
