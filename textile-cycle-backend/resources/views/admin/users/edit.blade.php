@extends('layouts.app')

@section('title', 'Modifier l\'Utilisateur — ' . $user->name)

@section('content')
<div style="max-width:760px; margin:0 auto; padding-bottom:3rem">

    <div style="margin-bottom:1.5rem">
        <a href="{{ route('admin.users.show', $user) }}" style="display:inline-flex; align-items:center; gap:.35rem; color:var(--text-secondary); text-decoration:none; font-size:.875rem; margin-bottom:.5rem">
            <span class="material-icons-round" style="font-size:1.1rem">arrow_back</span>
            <span>Retour au profil</span>
        </a>
        <h1 class="page-title">Modifier l'utilisateur</h1>
        <p class="page-subtitle">Modifiez les informations personnelles, le rôle ou le mot de passe de {{ $user->name }}.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom:1.5rem">
            <span class="material-icons-round">error_outline</span>
            <div>
                <strong>Erreurs de saisie :</strong>
                <ul style="margin:.35rem 0 0 1.25rem; font-size:.85rem">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card" style="padding:2rem">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="name">Nom complet ou Raison sociale <span style="color:var(--accent-red)">*</span></label>
                    <input type="text" name="name" id="name" required class="form-control"
                           value="{{ old('name', $user->name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Adresse E-mail <span style="color:var(--accent-red)">*</span></label>
                    <input type="email" name="email" id="email" required class="form-control"
                           value="{{ old('email', $user->email) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Rôle sur la plateforme <span style="color:var(--accent-red)">*</span></label>
                <select name="role" id="role" required class="form-control">
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>👑 Administrateur</option>
                    <option value="atelier" {{ old('role', $user->role) === 'atelier' ? 'selected' : '' }}>✂️ Atelier</option>
                    <option value="association" {{ old('role', $user->role) === 'association' ? 'selected' : '' }}>🤝 Association</option>
                    <option value="user" {{ in_array(old('role', $user->role), ['user', 'particulier', 'vendeur', 'acheteur']) ? 'selected' : '' }}>👤 Particulier</option>
                </select>
            </div>

            <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:12px; padding:1.25rem; margin-top:1.5rem; margin-bottom:1.5rem">
                <h3 style="font-size:.95rem; font-weight:700; color:var(--text-primary); margin-bottom:.35rem">
                    🔑 Réinitialisation du mot de passe (optionnel)
                </h3>
                <p style="color:var(--text-muted); font-size:.8rem; margin-bottom:1rem">
                    Laissez ces champs vides si vous ne souhaitez pas modifier le mot de passe actuel.
                </p>

                <div class="grid-2">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="password">Nouveau mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="Min. 8 caractères">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                               placeholder="Répétez le mot de passe">
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:.75rem; padding-top:1.25rem; border-top:1px solid var(--border)">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                    <span>Annuler</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons-round">save</span>
                    <span>Enregistrer les modifications</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
