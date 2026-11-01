@extends('layouts.app')

@section('title', 'Créer un Utilisateur')

@section('content')
<div style="max-width:760px; margin:0 auto; padding-bottom:3rem">

    <div style="margin-bottom:1.5rem">
        <a href="{{ route('admin.users.index') }}" style="display:inline-flex; align-items:center; gap:.35rem; color:var(--text-secondary); text-decoration:none; font-size:.875rem; margin-bottom:.5rem">
            <span class="material-icons-round" style="font-size:1.1rem">arrow_back</span>
            <span>Retour à la liste des utilisateurs</span>
        </a>
        <h1 class="page-title">Créer un nouvel utilisateur</h1>
        <p class="page-subtitle">Ajoutez manuellement un administrateur, atelier, association ou particulier.</p>
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
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="name">Nom complet ou Raison sociale <span style="color:var(--accent-red)">*</span></label>
                    <input type="text" name="name" id="name" required class="form-control"
                           placeholder="Ex: Sophie Martin" value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Adresse E-mail <span style="color:var(--accent-red)">*</span></label>
                    <input type="email" name="email" id="email" required class="form-control"
                           placeholder="sophie@exemple.com" value="{{ old('email') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Rôle sur la plateforme <span style="color:var(--accent-red)">*</span></label>
                <select name="role" id="role" required class="form-control">
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>👑 Administrateur</option>
                    <option value="atelier" {{ old('role') === 'atelier' ? 'selected' : '' }}>✂️ Atelier</option>
                    <option value="association" {{ old('role') === 'association' ? 'selected' : '' }}>🤝 Association</option>
                    <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>👤 Particulier</option>
                </select>
                <small style="color:var(--text-muted); font-size:.8rem; margin-top:.35rem; display:block">
                    Le rôle détermine les permissions d'accès et les fonctionnalités disponibles.
                </small>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe <span style="color:var(--accent-red)">*</span></label>
                    <input type="password" name="password" id="password" required class="form-control"
                           placeholder="Min. 8 caractères">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmer le mot de passe <span style="color:var(--accent-red)">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="form-control"
                           placeholder="Répétez le mot de passe">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:.75rem; margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid var(--border)">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <span>Annuler</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons-round">check_circle</span>
                    <span>Créer l'utilisateur</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
