@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div style="max-width:860px; margin:0 auto; display:flex; flex-direction:column; gap:1.75rem">

    <!-- En-tête profil -->
    <div class="card" style="display:flex; align-items:center; gap:1.5rem; padding:1.75rem">
        <img src="{{ auth()->user()->avatar_url }}" alt="Avatar"
             style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid var(--border)">
        <div style="flex:1">
            <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-bottom:.25rem">{{ auth()->user()->name }}</h1>
            <div style="display:flex; align-items:center; gap:.75rem">
                <span class="badge badge-primary">{{ ucfirst(auth()->user()->role) }}</span>
                <span style="font-size:.85rem; color:var(--text-secondary)">{{ auth()->user()->email }}</span>
                @if(auth()->user()->hasVerifiedEmail())
                    <span style="display:inline-flex; align-items:center; gap:.25rem; font-size:.78rem; color:#10B981; font-weight:600">
                        <span class="material-icons-round" style="font-size:1rem">verified</span> E-mail vérifié
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <span class="material-icons-round">check_circle</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Informations personnelles -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">👤 Informations personnelles</h2>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="name">Nom complet</label>
                    <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required class="form-control">
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required class="form-control">
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Photo de profil</label>
                <div style="display:flex; align-items:center; gap:1rem">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Aperçu" style="width:44px; height:44px; border-radius:50%; object-fit:cover; border:1px solid var(--border)">
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="form-control" style="padding:.5rem">
                </div>
                @error('avatar') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex; justify-content:flex-end">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons-round">save</span>
                    <span>Enregistrer les modifications</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Sécurité et 2FA -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🔐 Sécurité & Authentification à deux facteurs</h2>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem">
            <div>
                <div style="font-weight:600; color:var(--text-primary); margin-bottom:.25rem">Double authentification (2FA)</div>
                <div style="font-size:.85rem; color:var(--text-secondary)">
                    Statut actuel :
                    @if(auth()->user()->two_factor_enabled)
                        <strong style="color:#10B981">✅ Activée</strong>
                    @else
                        <span style="color:var(--text-muted)">Non configurée</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('2fa.show') }}" class="btn btn-secondary">
                <span class="material-icons-round">security</span>
                <span>{{ auth()->user()->two_factor_enabled ? 'Gérer le 2FA' : 'Activer le 2FA' }}</span>
            </a>
        </div>
    </div>

    <!-- Changement de mot de passe -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🔑 Changer le mot de passe</h2>
        </div>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="current_password">Mot de passe actuel</label>
                <input type="password" name="current_password" id="current_password" required class="form-control">
                @error('current_password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="new_password">Nouveau mot de passe</label>
                    <input type="password" name="password" id="new_password" required minlength="8" class="form-control" placeholder="Min. 8 caractères">
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="form-control">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons-round">lock_reset</span>
                    <span>Mettre à jour le mot de passe</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Zone de suppression -->
    <div class="card" style="border-color:#FECACA; background:#FFFBFB">
        <div class="card-header">
            <h2 class="card-title" style="color:var(--accent-red)">⚠️ Zone de danger</h2>
        </div>
        <p style="color:var(--text-secondary); font-size:.875rem; margin-bottom:1.25rem">
            La suppression de votre compte effacera l'ensemble de vos données, annonces et historique de façon définitive.
        </p>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteAccountModal').style.display='flex'">
            <span class="material-icons-round">delete_forever</span>
            <span>Supprimer mon compte définitivement</span>
        </button>
    </div>

</div>

<!-- Modal confirmation suppression -->
<div id="deleteAccountModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:1rem" onclick="if(event.target===this)this.style.display='none'">
    <div class="card" style="max-width:440px; width:100%; padding:2rem; box-shadow:0 20px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:1.2rem; font-weight:700; color:var(--text-primary); margin-bottom:.5rem">Confirmer la suppression</h3>
        <p style="color:var(--text-secondary); font-size:.875rem; margin-bottom:1.25rem">
            Cette action est irréversible. Veuillez saisir votre mot de passe pour valider la suppression :
        </p>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')
            <div class="form-group">
                <input type="password" name="password" required class="form-control" placeholder="Votre mot de passe">
            </div>
            <div style="display:flex; gap:.75rem; justify-content:flex-end">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('deleteAccountModal').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>
@endsection
