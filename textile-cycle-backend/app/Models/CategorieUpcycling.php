<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieUpcycling extends Model
{
    use HasFactory;

    protected $table = 'categories_upcycling';

    protected $fillable = ['nom', 'description', 'icone', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    // Relation 1-N : une catégorie contient plusieurs projets de transformation
    public function transformations(): HasMany
    {
        return $this->hasMany(Transformation::class, 'categorie_id');
    }

    public function scopeActives(Builder $query): Builder
    {
        return $query->where('actif', true);
    }
}
