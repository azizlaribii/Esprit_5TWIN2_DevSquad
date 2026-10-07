<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'categorie', 'quantite', 'etat', 'statut', 'description', 'photo',
        'ai_type', 'ai_couleur', 'ai_etat', 'ai_matiere', 'ai_confiance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}