<?php

namespace App\Models\Concerns;

use App\Models\Association;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * À ajouter au modèle App\Models\User :
 *
 *     use App\Models\Concerns\HasTextileRole;
 *     class User extends Authenticatable { use HasTextileRole; ... }
 *
 * (les 4 rôles : admin, atelier, association, user/particulier).
 */
trait HasTextileRole
{
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAtelier(): bool
    {
        return $this->role === 'atelier';
    }

    public function isAssociation(): bool
    {
        return $this->role === 'association';
    }

    /** Le « particulier » (donateur/client) correspond au rôle « user » du projet. */
    public function isParticulier(): bool
    {
        return $this->role === 'user' || $this->role === 'particulier';
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function association(): HasOne
    {
        return $this->hasOne(Association::class);
    }
}
