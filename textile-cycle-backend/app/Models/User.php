<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Concerns\HasTextileRole;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasTextileRole, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at'        => 'datetime',
        'two_factor_confirmed_at'  => 'datetime',
        'preferences'              => 'array',
    ];

    public function getTwoFactorEnabledAttribute(): bool
    {
        return $this->hasEnabledTwoFactorAuthentication();
    }

    // ─── Relations existantes ────────────────────────────────────────────────

    public function depots()
    {
        return $this->hasMany(Depot::class);
    }

    public function transformations()
    {
        return $this->hasMany(Transformation::class);
    }

    public function articlesMarketplace()
    {
        return $this->hasMany(ArticleMarketplace::class);
    }

    public function vetementsArmoire()
    {
        return $this->hasMany(VetementArmoire::class);
    }

    public function favorisMarketplace()
    {
        return $this->hasMany(FavoriMarketplace::class);
    }

    public function demandesAchat()
    {
        return $this->hasMany(DemandeMarketplace::class, 'acheteur_id');
    }

    public function evaluationsDonnees()
    {
        return $this->hasMany(EvaluationVendeur::class, 'evaluateur_id');
    }

    public function evaluationsRecues()
    {
        return $this->hasMany(EvaluationVendeur::class, 'vendeur_id');
    }

    public function getNoteMoyenneAttribute(): float
    {
        $evals = $this->evaluationsRecues;
        return $evals->count() > 0 ? round($evals->avg('note'), 1) : 4.0;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=10b981&color=fff&size=128";
    }
}
