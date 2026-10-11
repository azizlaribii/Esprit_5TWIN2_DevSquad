<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VetementArmoire extends Model
{
    use HasFactory;

    protected $table = 'vetements_armoire';

    protected $fillable = [
        'user_id',
        'titre',
        'image_path',
        'dossier',
        'categorie',
        'marque',
        'taille',
        'genre',
        'etat',
        'description',
    ];

    protected $appends = ['display_image_url', 'dossier_icon'];

    /**
     * Icône associée au dossier / section de vêtement
     */
    public function getDossierIconAttribute(): string
    {
        $d = mb_strtolower($this->dossier ?? '');
        if (str_contains($d, 't-shirt') || str_contains($d, 'top') || str_contains($d, 'chemise') || str_contains($d, 'pull')) return '👕';
        if (str_contains($d, 'pantalon') || str_contains($d, 'jean')) return '👖';
        if (str_contains($d, 'chaussure') || str_contains($d, 'basket')) return '👟';
        if (str_contains($d, 'chaussette')) return '🧦';
        if (str_contains($d, 'veste') || str_contains($d, 'manteau')) return '🧥';
        return '📁';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accesseur pour afficher l'URL propre de l'image
     */
    public function getDisplayImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return asset('images/default-clothing.jpg');
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        if (Str::startsWith($this->image_path, 'storage/')) {
            return asset($this->image_path);
        }

        return asset('storage/' . $this->image_path);
    }
}
