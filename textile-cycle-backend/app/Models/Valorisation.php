<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valorisation extends Model
{
    use HasFactory;

    public const FILIERES = [
        'revente'    => 'Revente',
        'reparation' => 'Réparation',
        'don'        => 'Don',
        'recyclage'  => 'Recyclage',
    ];

    public const STATUTS = [
        'propose'  => 'Proposée',
        'validee'  => 'Validée',
        'refusee'  => 'Refusée',
        'realisee' => 'Réalisée',
    ];

    protected $fillable = [
        'depot_id', 'filiere', 'statut', 'estimation_prix', 'commentaire', 'source',
        'motif_refus', 'traite_par', 'traite_le',
    ];

    protected $casts = [
        'traite_le' => 'datetime',
    ];

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    /** Administrateur qui a validé ou refusé la valorisation. */
    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function estGerablePar(?User $user): bool
    {
        return $this->depot->estGerablePar($user);
    }
}