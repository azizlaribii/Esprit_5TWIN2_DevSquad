<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'city', 'latitude', 'longitude',
        'phone', 'rating', 'specialties',
    ];

    protected $casts = [
        'specialties' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'rating' => 'float',
    ];

    public function repairRequests(): HasMany
    {
        return $this->hasMany(RepairRequest::class);
    }

    /**
     * Calcule la distance (km) depuis un point donné (formule de Haversine),
     * utile pour trier les ateliers "les plus proches".
     */
    public function scopeNearestFirst($query, float $lat, float $lng)
    {
        $haversine = "(6371 * acos(cos(radians($lat))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians($lng))
                        + sin(radians($lat))
                        * sin(radians(latitude))))";

        return $query->selectRaw("workshops.*, {$haversine} AS distance")
                      ->orderBy('distance');
    }
}
