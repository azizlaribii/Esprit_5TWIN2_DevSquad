@extends('layouts.app')

@section('title', 'Détails Utilisateur — ' . $user->name)

@section('content')
<div style="max-width:860px; margin:0 auto; padding-bottom:3rem">

    <div style="margin-bottom:1.5rem">
        <a href="{{ route('admin.users.index') }}" style="display:inline-flex; align-items:center; gap:.35rem; color:var(--text-secondary); text-decoration:none; font-size:.875rem; margin-bottom:.5rem">
            <span class="material-icons-round" style="font-size:1.1rem">arrow_back</span>
            <span>Retour à la liste des utilisateurs</span>
        </a>
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem">
            <div>
                <h1 class="page-title">{{ $user->name }}</h1>
                <p class="page-subtitle">Détails et gestion du compte utilisateur.</p>
            </div>
            <div style="display:flex; gap:.5rem">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary">
                    <span class="material-icons-round">edit</span>
                    <span>Modifier</span>
                </a>

                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Confirmez-vous la suppression définitive du compte « {{ $user->name }} » ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <span class="material-icons-round">delete</span>
                            <span>Supprimer</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- User Profile Header Card -->
    <div class="card" style="padding:2rem; margin-bottom:1.5rem">
        <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                 style="width:84px; height:84px; border-radius:50%; object-fit:cover; border:3px solid var(--border); box-shadow:var(--shadow-glow)">
            <div style="flex:1">
                <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem">
                    <h2 style="font-size:1.4rem; font-weight:800; color:var(--text-primary); margin:0">{{ $user->name }}</h2>
                    @if($user->role === 'admin')
                        <span class="badge" style="background:#FEF3C7; color:#92400E; border:1px solid #FCD34D">👑 Administrateur</span>
                    @elseif($user->role === 'atelier')
                        <span class="badge" style="background:#E0F2FE; color:#0369A1; border:1px solid #BAE6FD">✂️ Atelier</span>
                    @elseif($user->role === 'association')
                        <span class="badge" style="background:#FCE7F3; color:#BE185D; border:1px solid #FBCFE8">🤝 Association</span>
                    @else
                        <span class="badge badge-success">👤 Particulier</span>
                    @endif
                </div>
                <div style="color:var(--text-secondary); font-size:.9rem; margin-bottom:.5rem">{{ $user->email }}</div>
                <div style="display:flex; gap:1rem; font-size:.8rem; color:var(--text-muted); flex-wrap:wrap">
                    <div>📅 Inscrit le {{ $user->created_at ? $user->created_at->format('d/m/Y à H:i') : '—' }}</div>
                    @if($user->email_verified_at)
                        <div style="color:#059669">✓ E-mail vérifié</div>
                    @else
                        <div style="color:#D97706">⚠ E-mail non vérifié</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Security & 2FA Status -->
    <div class="card" style="padding:1.5rem; margin-bottom:1.5rem">
        <div class="card-header" style="margin-bottom:1rem">
            <div class="card-title" style="display:flex; align-items:center; gap:.5rem">
                <span class="material-icons-round" style="color:var(--primary)">security</span>
                <span>Sécurité & Double Authentification (Fortify 2FA)</span>
            </div>
            @if($user->two_factor_enabled)
                <span class="badge badge-success">Actif & Confirmé</span>
            @else
                <span class="badge" style="background:#F1F5F9; color:var(--text-muted)">Non activé</span>
            @endif
        </div>

        @if($user->two_factor_enabled)
            <div style="display:flex; justify-content:space-between; align-items:center; background:#ECFDF5; border:1px solid #A7F3D0; border-radius:12px; padding:1rem 1.25rem">
                <div style="font-size:.875rem; color:#065F46">
                    <strong>La protection 2FA est active.</strong> L'utilisateur est invité à saisir un code TOTP lors de sa connexion.
                    @if($user->two_factor_confirmed_at)
                        <div style="font-size:.78rem; color:#047857; margin-top:.2rem">Confirmé le {{ $user->two_factor_confirmed_at->format('d/m/Y à H:i') }}</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.users.toggle-2fa', $user) }}"
                      onsubmit="return confirm('Désactiver le 2FA pour « {{ $user->name }} » ? L\'utilisateur pourra se reconnecter sans code TOTP.')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <span class="material-icons-round">no_encryption</span>
                        <span>Réinitialiser le 2FA</span>
                    </button>
                </form>
            </div>
        @else
            <p style="color:var(--text-secondary); font-size:.875rem; margin:0">
                La double authentification n'a pas encore été configurée par cet utilisateur.
            </p>
        @endif
    </div>

    <!-- Activity Counts -->
    <div class="grid-3">
        <div class="card" style="padding:1.25rem; text-align:center">
            <div style="width:40px; height:40px; border-radius:10px; background:#EEF2FF; color:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto .5rem">
                <span class="material-icons-round">inventory_2</span>
            </div>
            <div style="font-size:1.5rem; font-weight:800; color:var(--text-primary)">{{ $counts['depots'] }}</div>
            <div style="font-size:.8rem; color:var(--text-muted)">Dépôts enregistrés</div>
        </div>

        <div class="card" style="padding:1.25rem; text-align:center">
            <div style="width:40px; height:40px; border-radius:10px; background:#ECFDF5; color:#10B981; display:flex; align-items:center; justify-content:center; margin:0 auto .5rem">
                <span class="material-icons-round">storefront</span>
            </div>
            <div style="font-size:1.5rem; font-weight:800; color:#10B981">{{ $counts['articles'] }}</div>
            <div style="font-size:.8rem; color:var(--text-muted)">Articles Marketplace</div>
        </div>

        <div class="card" style="padding:1.25rem; text-align:center">
            <div style="width:40px; height:40px; border-radius:10px; background:#FEF2F2; color:#EF4444; display:flex; align-items:center; justify-content:center; margin:0 auto .5rem">
                <span class="material-icons-round">checkroom</span>
            </div>
            <div style="font-size:1.5rem; font-weight:800; color:#EF4444">{{ $counts['armoires'] }}</div>
            <div style="font-size:.8rem; color:var(--text-muted)">Vêtements Armoire</div>
        </div>
    </div>

</div>
@endsection

