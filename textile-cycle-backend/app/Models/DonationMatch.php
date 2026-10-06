<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationMatch extends Model
{
    use HasFactory;

    public const SUGGESTED = 'suggested';
    public const REQUESTED = 'requested';
    public const ACCEPTED  = 'accepted';
    public const REJECTED  = 'rejected';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled'; // don annulé par le donateur : libère la capacité, sans pénaliser l'association

    public const STATUS_LABELS = [
        self::SUGGESTED => 'Suggérée',
        self::REQUESTED => 'Demande envoyée',
        self::ACCEPTED  => 'Acceptée',
        self::REJECTED  => 'Refusée',
        self::COMPLETED => 'Remis',
        self::CANCELLED => 'Annulée',
    ];

    protected $fillable = [
        'donation_id', 'association_id', 'association_need_id', 'score', 'reasons',
        'explanation', 'quantity', 'status', 'meeting_type', 'meeting_at',
        'meeting_note', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'reasons'      => 'array',
            'score'        => 'float',
            'meeting_at'   => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    public function need(): BelongsTo
    {
        return $this->belongsTo(AssociationNeed::class, 'association_need_id');
    }

    /** Texte IA si disponible, sinon les raisons calculées par l'algorithme. */
    public function displayExplanation(): string
    {
        return $this->explanation ?: implode(' · ', $this->reasons ?? []);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
