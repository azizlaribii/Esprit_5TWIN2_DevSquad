<?php

namespace App\Models;

use App\Support\Textile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donation extends Model
{
    use HasFactory;

    public const PENDING_ANALYSIS = 'pending_analysis'; // en cours d'analyse IA
    public const NEEDS_REVIEW     = 'needs_review';     // le donateur doit compléter les champs
    public const MATCHED          = 'matched';          // suggestions prêtes
    public const NO_MATCH         = 'no_match';         // aucune association compatible
    public const REQUESTED        = 'requested';        // le donateur a choisi, en attente de réponse
    public const ACCEPTED         = 'accepted';         // l'association a accepté
    public const COMPLETED        = 'completed';        // don remis
    public const CANCELLED        = 'cancelled';

    public const STATUS_LABELS = [
        self::PENDING_ANALYSIS => 'Analyse en cours',
        self::NEEDS_REVIEW     => 'À compléter',
        self::MATCHED          => 'Suggestions prêtes',
        self::NO_MATCH         => 'Aucune association compatible',
        self::REQUESTED        => 'En attente de réponse',
        self::ACCEPTED         => 'Accepté',
        self::COMPLETED        => 'Remis',
        self::CANCELLED        => 'Annulé',
    ];

    protected $fillable = [
        'user_id', 'title', 'description', 'category', 'age_group', 'size', 'gender',
        'season', 'condition', 'quantity', 'city', 'lat', 'lng', 'status',
        'ai_metadata', 'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_metadata' => 'array',
            'analyzed_at' => 'datetime',
            'lat'         => 'float',
            'lng'         => 'float',
            'quantity'    => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DonationPhoto::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DonationMatch::class);
    }

    /** Le matching a besoin au minimum d'une catégorie et d'un état. */
    public function isReadyForMatching(): bool
    {
        return filled($this->category) && filled($this->condition);
    }

    /** Le donateur peut encore modifier le don tant qu'aucune association ne l'a accepté. */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::NEEDS_REVIEW, self::MATCHED, self::NO_MATCH, self::PENDING_ANALYSIS,
        ], true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function categoryLabel(): string
    {
        return Textile::label('categories', $this->category);
    }
}
