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
    ];

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function estGerablePar(?User $user): bool
    {
        return $this->depot->estGerablePar($user);
    }
}